<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Category;
use App\Models\User;

/**
 * Otorisasi kategori (rubrik).
 *
 * Pengelolaan kategori global hanya untuk super-admin. Desk editor hanya
 * boleh melihat kategori yang ditugaskan padanya.
 */
final class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CATEGORY_MANAGE->value)
            || $user->can(PermissionName::POST_VIEW_ANY->value);
    }

    public function view(User $user, Category $category): bool
    {
        if ($user->can(PermissionName::CATEGORY_MANAGE->value)) {
            return true;
        }

        return $user->assignedCategories()->whereKey($category->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CATEGORY_MANAGE->value);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can(PermissionName::CATEGORY_MANAGE->value);
    }

    /**
     * Menghapus kategori. Ditolak bila masih dipakai artikel.
     */
    public function delete(User $user, Category $category): bool
    {
        if (! $user->can(PermissionName::CATEGORY_MANAGE->value)) {
            return false;
        }

        return ! $category->posts()->exists() && ! $category->children()->exists();
    }

    /**
     * Mengalihkan kategori nonaktif ke kategori lain.
     */
    public function redirect(User $user, Category $category): bool
    {
        return $user->can(PermissionName::CATEGORY_MANAGE->value);
    }
}
