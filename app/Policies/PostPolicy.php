<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;

/**
 * Otorisasi artikel.
 *
 * Seluruh keputusan berbasis permission + atribut baris, BUKAN pengecekan
 * nama role di controller. Aturan keras yang ditegakkan di sini:
 *
 * 1. Reporter hanya dapat menyunting naskah miliknya saat DRAFT/REJECTED
 *    dan TIDAK PERNAH dapat memicu status SCHEDULED atau PUBLISHED.
 * 2. Desk Editor hanya memproses naskah pada kategori yang ditugaskan
 *    padanya (pivot `category_user`).
 * 3. Desk Editor dilarang menyetujui naskah buatannya sendiri.
 * 4. Klaim editor: hanya pemegang klaim (atau pemred dengan force-claim)
 *    yang boleh menyetujui/menolak/menjadwalkan naskah.
 */
final class PostPolicy
{
    /**
     * Melihat naskah di panel newsroom.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::POST_VIEW_ANY->value);
    }

    /**
     * Melihat detail naskah: reporter hanya naskahnya sendiri.
     */
    public function view(User $user, Post $post): bool
    {
        if ($user->can(PermissionName::POST_VIEW_ANY->value)
            && $user->can(PermissionName::POST_REVIEW->value)) {
            // Editor/pemred melihat sesuai cakupan kategori.
            return $this->withinAssignedScope($user, $post);
        }

        if ($user->can(PermissionName::POST_VIEW_ANY->value)) {
            return true;
        }

        return $this->isOwnerAuthor($user, $post);
    }

    /**
     * Membuat naskah baru. Reporter & editor boleh.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::POST_CREATE->value)
            || $user->can(PermissionName::POST_EDIT_IN_DESK->value);
    }

    /**
     * Menyunting isi naskah.
     *
     * Reporter: hanya naskah sendiri, hanya saat DRAFT/REJECTED.
     * Desk editor: hanya kategori yang ditugaskan.
     */
    public function update(User $user, Post $post): bool
    {
        if ($this->isOwnerAuthor($user, $post)) {
            return $post->status->isEditableByReporter()
                && $user->can(PermissionName::POST_EDIT_OWN->value);
        }

        if ($user->can(PermissionName::POST_EDIT_IN_DESK->value)) {
            return $this->withinAssignedScope($user, $post);
        }

        // Pemred dapat menyunting naskah apa pun yang belum terbit.
        return $user->can(PermissionName::HOMEPAGE_CURATE->value)
            && $post->status !== PostStatus::PUBLISHED;
    }

    /**
     * Menghapus naskah.
     */
    public function delete(User $user, Post $post): bool
    {
        if ($this->isOwnerAuthor($user, $post)) {
            return $post->status === PostStatus::DRAFT
                && $user->can(PermissionName::POST_EDIT_OWN->value);
        }

        return $user->can(PermissionName::POST_TAKEDOWN->value);
    }

    /**
     * Mengajukan naskah untuk direview (DRAFT/REJECTED -> UNDER_REVIEW).
     */
    public function submit(User $user, Post $post): bool
    {
        return $this->isOwnerAuthor($user, $post)
            && $user->can(PermissionName::POST_SUBMIT->value)
            && $post->status->canTransitionTo(PostStatus::UNDER_REVIEW);
    }

    /**
     * Menarik kembali naskah dari antrean review (UNDER_REVIEW -> DRAFT).
     * Hanya bila belum diklaim editor.
     */
    public function withdraw(User $user, Post $post): bool
    {
        return $this->isOwnerAuthor($user, $post)
            && $user->can(PermissionName::POST_WITHDRAW->value)
            && $post->status === PostStatus::UNDER_REVIEW
            && $post->claimed_by === null;
    }

    /**
     * Mengklaim naskah untuk direview.
     */
    public function claim(User $user, Post $post): bool
    {
        if (! $user->can(PermissionName::POST_CLAIM->value)) {
            return false;
        }

        if (! $this->withinAssignedScope($user, $post)) {
            return false;
        }

        // Hanya naskah yang menunggu review dan belum dipegang siapa pun
        // (atau klaimnya sudah kedaluwarsa).
        return $post->status === PostStatus::UNDER_REVIEW
            && (! $post->hasActiveClaim() || $post->hasExpiredClaim());
    }

    /**
     * Mengambil alih klaim milik editor lain (khusus pemred).
     */
    public function forceClaim(User $user, Post $post): bool
    {
        return $user->can(PermissionName::POST_FORCE_CLAIM->value)
            && $post->status === PostStatus::UNDER_REVIEW;
    }

    /**
     * Melepas klaim sendiri.
     */
    public function releaseClaim(User $user, Post $post): bool
    {
        return $post->claimed_by === $user->getKey()
            && $post->status === PostStatus::UNDER_REVIEW;
    }

    /**
     * Menyetujui & menerbitkan naskah (UNDER_REVIEW -> PUBLISHED/SCHEDULED).
     *
     * Reporter tidak pernah lolos di sini karena permission `post.approve`
     * dan `post.schedule` tidak dimilikinya.
     */
    public function approve(User $user, Post $post): bool
    {
        if (! $user->can(PermissionName::POST_APPROVE->value)) {
            return false;
        }

        if (! $this->withinAssignedScope($user, $post)) {
            return false;
        }

        // Dilarang menyetujui naskah buatan sendiri.
        if ($this->isOwnerAuthor($user, $post)) {
            return false;
        }

        if ($post->status !== PostStatus::UNDER_REVIEW) {
            return false;
        }

        // Harus pemegang klaim aktif, atau pemred yang mengambil alih.
        return $post->claimed_by === $user->getKey()
            || $user->can(PermissionName::POST_FORCE_CLAIM->value);
    }

    /**
     * Menjadwalkan naskah (UNDER_REVIEW -> SCHEDULED).
     */
    public function schedule(User $user, Post $post): bool
    {
        if (! $user->can(PermissionName::POST_SCHEDULE->value)) {
            return false;
        }

        return $this->approve($user, $post);
    }

    /**
     * Menolak naskah (UNDER_REVIEW -> REJECTED).
     */
    public function reject(User $user, Post $post): bool
    {
        if (! $user->can(PermissionName::POST_REJECT->value)) {
            return false;
        }

        if (! $this->withinAssignedScope($user, $post)) {
            return false;
        }

        if ($post->status !== PostStatus::UNDER_REVIEW) {
            return false;
        }

        return $post->claimed_by === $user->getKey()
            || $user->can(PermissionName::POST_FORCE_CLAIM->value);
    }

    /**
     * Menurunkan artikel terbit (PUBLISHED -> TAKEDOWN). Khusus pemred.
     */
    public function takedown(User $user, Post $post): bool
    {
        return $user->can(PermissionName::POST_TAKEDOWN->value)
            && $post->status->canTransitionTo(PostStatus::TAKEDOWN);
    }

    /**
     * Memulihkan artikel takedown (TAKEDOWN -> PUBLISHED). Khusus pemred.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->can(PermissionName::POST_RESTORE->value)
            && $post->status->canTransitionTo(PostStatus::PUBLISHED);
    }

    /**
     * Menerbitkan Ralat resmi untuk artikel terbit.
     */
    public function publishCorrection(User $user, Post $post): bool
    {
        return $user->can(PermissionName::CORRECTION_PUBLISH->value)
            && in_array($post->status, [PostStatus::PUBLISHED, PostStatus::TAKEDOWN], true);
    }

    /**
     * Edit minor artikel terbit (tanpa mengubah status).
     */
    public function minorEdit(User $user, Post $post): bool
    {
        return $user->can(PermissionName::POST_MINOR_EDIT->value)
            && $this->withinAssignedScope($user, $post)
            && in_array($post->status, [PostStatus::PUBLISHED, PostStatus::TAKEDOWN], true);
    }

    /**
     * Apakah pengguna adalah penulis naskah.
     */
    private function isOwnerAuthor(User $user, Post $post): bool
    {
        return $post->author_id === $user->getKey();
    }

    /**
     * Desk editor dibatasi pada kategori yang ditugaskan padanya.
     * Pemred & super-admin tidak dibatasi.
     */
    private function withinAssignedScope(User $user, Post $post): bool
    {
        if ($user->can(PermissionName::HOMEPAGE_CURATE->value)) {
            return true;
        }

        if ($user->hasRole(\App\Enums\RoleName::SUPER_ADMIN->value)) {
            return true;
        }

        return $user->assignedCategories()
            ->whereKey($post->category_id)
            ->exists();
    }
}
