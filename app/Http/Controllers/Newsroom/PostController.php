<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsroom;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $statusFilter = $request->input('status', 'all');
        $search = trim((string) $request->input('q', ''));

        $query = Post::query()->withListRelations()->latest();

        // Scope wewenang role:
        // Reporter hanya melihat naskah buatannya sendiri
        if ($user->hasRole('reporter')) {
            $query->where('author_id', $user->id);
        }

        if ($statusFilter !== 'all' && in_array($statusFilter, PostStatus::values(), true)) {
            $query->where('status', $statusFilter);
        }

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        $posts = $query->paginate(15)->withQueryString();

        // Hitungan per status
        $counts = [
            'all' => $user->hasRole('reporter') ? Post::where('author_id', $user->id)->count() : Post::count(),
            'published' => Post::when($user->hasRole('reporter'), fn ($q) => $q->where('author_id', $user->id))->where('status', PostStatus::PUBLISHED->value)->count(),
            'takedown' => Post::when($user->hasRole('reporter'), fn ($q) => $q->where('author_id', $user->id))->where('status', PostStatus::TAKEDOWN->value)->count(),
        ];

        return view('newsroom.posts.index', compact('posts', 'statusFilter', 'counts', 'search', 'user'));
    }

    public function create(): View
    {
        $categories = Category::active()->ordered()->get();
        $tags = Tag::ordered()->get();

        return view('newsroom.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'image_caption' => ['nullable', 'string', 'max:255'],
            'image_credit' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string'],
            'action' => ['required', 'in:draft,submit_review,publish'],
        ]);

        // 1. Buat media jika ada gambar (upload file lokal atau link URL)
        $featuredMediaId = null;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $optimized = app(\App\Services\ImageOptimizer::class)->optimizeAndStore($file, 'posts', 'public');
            $media = Media::create([
                'uploader_id' => $user->id,
                'disk' => $optimized['disk'],
                'path' => $optimized['path'],
                'mime_type' => $optimized['mime_type'],
                'size_bytes' => $optimized['size_bytes'],
                'caption' => $validated['image_caption'] ?? null,
                'credit' => $validated['image_credit'] ?? 'Dok. Redaksi',
                'alt_text' => $validated['title'],
            ]);
            $featuredMediaId = $media->id;
        } elseif (! empty($validated['image_url'])) {
            $media = Media::create([
                'uploader_id' => $user->id,
                'disk' => 'public',
                'path' => $validated['image_url'],
                'caption' => $validated['image_caption'] ?? null,
                'credit' => $validated['image_credit'] ?? 'Dok. Redaksi',
                'alt_text' => $validated['title'],
            ]);
            $featuredMediaId = $media->id;
        }

        // 2. Tentukan status awal berdasarkan aksi & wewenang
        $status = PostStatus::DRAFT;
        $publishedAt = null;
        $editorId = null;

        if ($validated['action'] === 'publish' && $user->hasAnyRole(['super-admin', 'pemred', 'desk-editor'])) {
            $status = PostStatus::PUBLISHED;
            $publishedAt = now();
            $editorId = $user->id;
        } elseif ($validated['action'] === 'submit_review') {
            $status = PostStatus::UNDER_REVIEW;
        }

        $baseSlug = Str::slug($validated['title']);
        $uniqueSlug = Post::where('slug', $baseSlug)->exists()
            ? $baseSlug.'-'.Str::random(5)
            : $baseSlug;

        $post = Post::create([
            'category_id' => (int) $validated['category_id'],
            'author_id' => $user->id,
            'author_name' => ! empty($validated['author_name']) ? trim($validated['author_name']) : null,
            'editor_id' => $editorId,
            'featured_media_id' => $featuredMediaId,
            'title' => $validated['title'],
            'slug' => $uniqueSlug,
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'status' => $status,
            'published_at' => $publishedAt,
            'seo_title' => $validated['title'],
            'seo_description' => $validated['excerpt'],
        ]);

        // 3. Pasang Tags
        $this->syncTagsString($post, $validated['tags'] ?? '');

        $msg = match ($status) {
            PostStatus::PUBLISHED => 'Artikel berhasil diterbitkan ke publik!',
            PostStatus::UNDER_REVIEW => 'Naskah berhasil dikirim ke meja redaksi untuk ditinjau editor.',
            default => 'Naskah berhasil disimpan sebagai draf.',
        };

        return redirect()->route('newsroom.posts.index')->with('status', $msg);
    }

    public function edit(Post $post): View
    {
        $categories = Category::active()->ordered()->get();
        $tags = Tag::ordered()->get();
        $currentTags = $post->tags->pluck('name')->implode(', ');

        return view('newsroom.posts.edit', compact('post', 'categories', 'tags', 'currentTags'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'image_caption' => ['nullable', 'string', 'max:255'],
            'image_credit' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string'],
            'editorial_notes' => ['nullable', 'string'],
            'action' => ['required', 'in:save,submit_review,publish,reject,takedown'],
        ]);

        // Update Media jika disediakan (upload file lokal atau link URL)
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $optimized = app(\App\Services\ImageOptimizer::class)->optimizeAndStore($file, 'posts', 'public');
            if ($post->featuredMedia) {
                $post->featuredMedia->update([
                    'disk' => $optimized['disk'],
                    'path' => $optimized['path'],
                    'mime_type' => $optimized['mime_type'],
                    'size_bytes' => $optimized['size_bytes'],
                    'caption' => $validated['image_caption'] ?? $post->featuredMedia->caption,
                    'credit' => $validated['image_credit'] ?? $post->featuredMedia->credit,
                ]);
            } else {
                $media = Media::create([
                    'uploader_id' => $user->id,
                    'disk' => $optimized['disk'],
                    'path' => $optimized['path'],
                    'mime_type' => $optimized['mime_type'],
                    'size_bytes' => $optimized['size_bytes'],
                    'caption' => $validated['image_caption'] ?? null,
                    'credit' => $validated['image_credit'] ?? 'Dok. Redaksi',
                    'alt_text' => $validated['title'],
                ]);
                $post->featured_media_id = $media->id;
            }
        } elseif (! empty($validated['image_url'])) {
            if ($post->featuredMedia) {
                $post->featuredMedia->update([
                    'path' => $validated['image_url'],
                    'caption' => $validated['image_caption'] ?? $post->featuredMedia->caption,
                    'credit' => $validated['image_credit'] ?? $post->featuredMedia->credit,
                ]);
            } else {
                $media = Media::create([
                    'uploader_id' => $user->id,
                    'disk' => 'public',
                    'path' => $validated['image_url'],
                    'caption' => $validated['image_caption'] ?? null,
                    'credit' => $validated['image_credit'] ?? 'Dok. Redaksi',
                    'alt_text' => $validated['title'],
                ]);
                $post->featured_media_id = $media->id;
            }
        }

        // Tentukan transisi status FSM
        $action = $validated['action'];
        if ($action === 'submit_review') {
            $post->status = PostStatus::UNDER_REVIEW;
        } elseif ($action === 'publish' && $user->hasAnyRole(['super-admin', 'pemred', 'desk-editor'])) {
            $post->status = PostStatus::PUBLISHED;
            $post->editor_id = $user->id;
            $post->published_at = $post->published_at ?? now();
        } elseif ($action === 'reject' && $user->hasAnyRole(['super-admin', 'pemred', 'desk-editor'])) {
            $post->status = PostStatus::REJECTED;
            $post->editorial_notes = $validated['editorial_notes'] ?? 'Harap periksa kembali akurasi data & gaya penulisan.';
        } elseif ($action === 'takedown' && $user->hasAnyRole(['super-admin', 'pemred'])) {
            $post->status = PostStatus::TAKEDOWN;
            $post->takedown_reason = $validated['editorial_notes'] ?? 'Diturunkan atas keputusan redaksi.';
        }

        $post->title = $validated['title'];
        $post->author_name = ! empty($validated['author_name']) ? trim($validated['author_name']) : null;
        $post->category_id = (int) $validated['category_id'];
        $post->excerpt = $validated['excerpt'];
        $post->body = $validated['body'];
        $post->save();

        $this->syncTagsString($post, $validated['tags'] ?? '');

        return redirect()->route('newsroom.posts.index')
            ->with('status', 'Perubahan naskah artikel berhasil disimpan.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('newsroom.posts.index')
            ->with('status', 'Artikel berhasil dihapus.');
    }

    private function syncTagsString(Post $post, string $tagsString): void
    {
        $tagNames = array_filter(array_map('trim', explode(',', $tagsString)));
        $tagIds = [];

        foreach ($tagNames as $name) {
            if ($name === '') {
                continue;
            }
            $slug = Str::slug($name);
            if ($slug === '') {
                continue;
            }
            $tag = Tag::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
            $tagIds[] = $tag->id;
        }

        $post->tags()->sync($tagIds);
    }
}
