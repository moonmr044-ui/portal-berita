<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\BreakingNews;
use App\Models\User;

/**
 * Otorisasi Breaking News ticker. Khusus pemred.
 */
final class BreakingNewsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::BREAKING_NEWS_MANAGE->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::BREAKING_NEWS_MANAGE->value);
    }

    public function update(User $user, BreakingNews $breakingNews): bool
    {
        return $user->can(PermissionName::BREAKING_NEWS_MANAGE->value);
    }

    public function delete(User $user, BreakingNews $breakingNews): bool
    {
        return $user->can(PermissionName::BREAKING_NEWS_MANAGE->value);
    }
}
