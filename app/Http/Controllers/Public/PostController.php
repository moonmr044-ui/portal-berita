<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\SlugRedirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show(Request $request, string $categorySlug, string $postSlug): View|RedirectResponse
    {
        // 1. Cek tabel slug_redirects (jika slug lama pernah diubah)
        $redirect = SlugRedirect::where('old_slug', $postSlug)->first();
        if ($redirect) {
            $targetPost = Post::find($redirect->post_id);
            if ($targetPost && $targetPost->category) {
                return redirect()->route('posts.show', [
                    'categorySlug' => $targetPost->category->slug,
                    'postSlug' => $targetPost->slug,
                ], 301);
            }
        }

        // 2. Ambil artikel
        $post = Post::where('slug', $postSlug)
            ->withDetailRelations()
            ->firstOrFail();

        // 3. Tangani Takedown (410 Gone) atau Bukan Published (404)
        if ($post->status === PostStatus::TAKEDOWN) {
            abort(410, 'Artikel ini telah diturunkan sesuai kebijakan redaksi.');
        }

        if ($post->status !== PostStatus::PUBLISHED) {
            // Hanya kru yang boleh preview jika belum publish
            if (! auth()->check() || ! auth()->user()->is_crew) {
                abort(404);
            }
        }

        // 4. Verifikasi Canonical Kategori (Redirect 301 jika slug kategori salah)
        if ($post->category->slug !== $categorySlug) {
            return redirect()->route('posts.show', [
                'categorySlug' => $post->category->slug,
                'postSlug' => $post->slug,
            ], 301);
        }

        // 5. Update view counter
        $post->increment('views_count');

        // 6. Artikel Terkait (dalam kategori yang sama)
        $relatedPosts = Post::published()
            ->latestPublished()
            ->withListRelations()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->take(4)
            ->get();

        // 7. Berita Terpopuler untuk Sidebar
        $popularPosts = Post::published()
            ->withListRelations()
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        // 8. Berita Terkini Real-Time untuk Sidebar
        $latestUpdates = Post::published()
            ->latestPublished()
            ->withListRelations()
            ->where('id', '!=', $post->id)
            ->take(6)
            ->get();

        // 9. Data Interaksi Pembaca (Like & Komentar Asli dari Database)
        $likesCount = $post->reactions()->count();
        $hasLiked = auth()->check()
            && $post->reactions()->where('user_id', auth()->id())->exists();

        $comments = $post->comments()
            ->approved()
            ->with('user')
            ->latest('created_at')
            ->get();

        return view('public.posts.show', compact(
            'post',
            'relatedPosts',
            'popularPosts',
            'latestUpdates',
            'likesCount',
            'hasLiked',
            'comments'
        ));
    }
}
