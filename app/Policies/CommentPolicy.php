<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\CommentStatus;
use App\Enums\PermissionName;
use App\Models\Comment;
use App\Models\User;

/**
 * Otorisasi komentar pembaca.
 *
 * Pembaca wajib sudah memverifikasi email sebelum boleh berkomentar.
 * Moderasi hanya untuk kru dengan permission `comment.moderate`.
 */
final class CommentPolicy
{
    /**
     * Membuat komentar: wajib email terverifikasi.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::COMMENT_CREATE->value)
            && $user->hasVerifiedEmailOrFail();
    }

    /**
     * Menyunting komentar sendiri, hanya saat masih menunggu moderasi.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->getKey()
            && $comment->status === CommentStatus::PENDING;
    }

    /**
     * Menghapus komentar sendiri (atau moderator).
     */
    public function delete(User $user, Comment $comment): bool
    {
        if ($user->can(PermissionName::COMMENT_MODERATE->value)) {
            return true;
        }

        return $comment->user_id === $user->getKey();
    }

    /**
     * Moderasi komentar.
     */
    public function moderate(User $user): bool
    {
        return $user->can(PermissionName::COMMENT_MODERATE->value);
    }

    /**
     * Melaporkan komentar orang lain (bukan komentar sendiri).
     */
    public function report(User $user, Comment $comment): bool
    {
        return $comment->user_id !== $user->getKey()
            && $user->hasVerifiedEmailOrFail();
    }
}
