<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\User;

/**
 * Otorisasi pengelolaan akun kru & pembaca.
 *
 * Super-admin mengelola kru. Setiap pengguna boleh mengelola akunnya
 * sendiri (profil, penghapusan akun pembaca).
 */
final class UserPolicy
{
    /**
     * Melihat daftar kru (panel newsroom).
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CREW_MANAGE->value);
    }

    /**
     * Melihat profil kru lain.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can(PermissionName::CREW_MANAGE->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CREW_MANAGE->value);
    }

    /**
     * Mengubah akun. Pengguna boleh mengubah akunnya sendiri; super-admin
     * boleh mengubah akun kru mana pun.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->getKey() === $model->getKey()) {
            return true;
        }

        return $user->can(PermissionName::CREW_MANAGE->value);
    }

    /**
     * Menonaktifkan/menghapus akun kru. Super-admin tidak dapat
     * menghapus akunnya sendiri agar tidak terjadi lockout.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->getKey() === $model->getKey()) {
            return false;
        }

        return $user->can(PermissionName::CREW_MANAGE->value);
    }

    /**
     * Menetapkan peran kepada pengguna.
     */
    public function assignRole(User $user, User $model): bool
    {
        return $user->can(PermissionName::CREW_MANAGE->value)
            && $user->getKey() !== $model->getKey();
    }

    /**
     * Menghapus akun pembaca (anonimisasi komentar lama).
     * Menghapus akun sendiri dalam konteks pembaca.
     */
    public function deleteOwnAccount(User $user, User $model): bool
    {
        return $user->getKey() === $model->getKey();
    }
}
