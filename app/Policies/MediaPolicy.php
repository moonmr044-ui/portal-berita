<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\PostStatus;
use App\Models\Media;
use App\Models\User;

/**
 * Otorisasi media/gambar.
 */
final class MediaPolicy
{
    /**
     * Unggah media: reporter & editor.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::MEDIA_UPLOAD->value);
    }

    /**
     * Melihat media: pengunggah atau kru dengan akses artikel.
     */
    public function view(User $user, Media $media): bool
    {
        return $media->uploader_id === $user->getKey()
            || $user->can(PermissionName::POST_VIEW_ANY->value);
    }

    /**
     * Mengubah metadata media (alt, takarir, kredit).
     */
    public function update(User $user, Media $media): bool
    {
        return $media->uploader_id === $user->getKey()
            || $user->can(PermissionName::POST_MINOR_EDIT->value);
    }

    /**
     * Menghapus media. Media yang masih dipakai artikel terbit tidak boleh
     * dihapus agar gambar tidak hilang dari halaman publik.
     */
    public function delete(User $user, Media $media): bool
    {
        $canDelete = $media->uploader_id === $user->getKey()
            || $user->can(PermissionName::POST_TAKEDOWN->value);

        if (! $canDelete) {
            return false;
        }

        return ! $media->posts()
            ->whereIn('status', [PostStatus::PUBLISHED->value, PostStatus::SCHEDULED->value])
            ->exists();
    }
}
