<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\RightOfReplyRequest;
use App\Models\User;

/**
 * Otorisasi Hak Jawab.
 *
 * Form publik tidak butuh autentikasi, tetapi PENANGANAN permohonan
 * hanya untuk kru dengan permission `rightofreply.handle` (pemred).
 */
final class RightOfReplyPolicy
{
    /**
     * Siapa pun boleh mengirim permohonan (route publik).
     */
    public function create(?User $user = null): bool
    {
        return true;
    }

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::RIGHT_OF_REPLY_HANDLE->value);
    }

    public function view(User $user, RightOfReplyRequest $request): bool
    {
        return $user->can(PermissionName::RIGHT_OF_REPLY_HANDLE->value);
    }

    /**
     * Menangani permohonan (ubah status + catatan penyelesaian).
     */
    public function handle(User $user, RightOfReplyRequest $request): bool
    {
        return $user->can(PermissionName::RIGHT_OF_REPLY_HANDLE->value)
            && ! $request->status->isClosed();
    }

    /**
     * Memublikasikan tanggapan hak jawab di halaman artikel.
     */
    public function publish(User $user, RightOfReplyRequest $request): bool
    {
        return $user->can(PermissionName::RIGHT_OF_REPLY_HANDLE->value)
            && ! $request->status->isClosed();
    }

    public function delete(User $user, RightOfReplyRequest $request): bool
    {
        return $user->can(PermissionName::RIGHT_OF_REPLY_HANDLE->value);
    }
}
