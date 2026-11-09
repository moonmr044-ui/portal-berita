<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\CommentStatus;
use App\Enums\ReactionType;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostInteractionController extends Controller
{
    public function toggleLike(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Silakan masuk terlebih dahulu.'], 401);
            }

            return redirect()->route('login');
        }

        $existing = Reaction::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Reaction::query()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'type' => ReactionType::LIKE->value,
            ]);
            $liked = true;
        }

        $likesCount = Reaction::query()->where('post_id', $post->id)->count();

        if ($request->expectsJson()) {
            return response()->json([
                'liked' => $liked,
                'likes_count' => $likesCount,
                'message' => $liked ? 'Anda menyukai berita ini.' : 'Tanda suka dibatalkan.',
            ]);
        }

        return back()->with('status', $liked ? 'Anda menyukai berita ini.' : 'Tanda suka dibatalkan.');
    }

    public function storeComment(Request $request, Post $post): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:1500'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ], [
            'body.required' => 'Isi komentar tidak boleh kosong.',
            'body.min' => 'Komentar minimal 2 karakter.',
            'body.max' => 'Komentar maksimal 1.500 karakter.',
        ]);

        $cleanBody = trim(strip_tags($validated['body']));
        if ($cleanBody === '') {
            return back()->with('error', 'Isi komentar tidak valid.');
        }

        $comment = new Comment([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $cleanBody,
            'status' => CommentStatus::APPROVED,
        ]);

        $comment->ip_hash = hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));
        $comment->save();

        return redirect(url()->previous().'#komentar-pembaca')
            ->with('status', 'Komentar Anda berhasil diterbitkan.');
    }

    public function destroyComment(Request $request, Comment $comment): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $canDelete = $comment->user_id === $user->id
            || $user->is_crew
            || $user->hasAnyRole(['super-admin', 'pemred', 'desk-editor']);

        if (! $canDelete) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus komentar ini.');
        }

        $comment->delete();

        return redirect(url()->previous().'#komentar-pembaca')
            ->with('status', 'Komentar berhasil dihapus.');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user->name = $validated['name'];

        if ($request->hasFile('avatar')) {
            if (! empty($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return back()->with('status', 'Profil dan foto akun Anda berhasil diperbarui.');
    }
}
