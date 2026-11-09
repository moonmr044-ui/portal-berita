<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\EditorialPick;
use App\Models\User;

/**
 * Otorisasi kurasi Editorial Picks (slot hero homepage). Khusus pemred.
 */
final class EditorialPickPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::EDITORIAL_PICKS_MANAGE->value);
    }

    public function update(User $user, EditorialPick $pick): bool
    {
        return $user->can(PermissionName::EDITORIAL_PICKS_MANAGE->value);
    }

    /**
     * Mengosongkan slot.
     */
    public function clear(User $user, EditorialPick $pick): bool
    {
        return $user->can(PermissionName::EDITORIAL_PICKS_MANAGE->value);
    }
}
