<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Nama peran RBAC 5-Tier.
 *
 * Dilarang menggunakan magic string untuk nama peran; seluruh pengecekan
 * wewenang memakai Enum ini melalui Policy/Gate berbasis permission.
 */
enum RoleName: string
{
    case SUPER_ADMIN = 'super-admin';
    case PEMRED = 'pemred';
    case DESK_EDITOR = 'desk-editor';
    case REPORTER = 'reporter';
    case READER = 'reader';

    /**
     * Daftar nilai peran sebagai array string.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $role): string => $role->value,
            self::cases(),
        );
    }

    /**
     * Label Bahasa Indonesia untuk tampilan panel kru.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::PEMRED => 'Pemimpin Redaksi',
            self::DESK_EDITOR => 'Editor Desk',
            self::REPORTER => 'Reporter',
            self::READER => 'Pembaca',
        };
    }

    /**
     * Peran yang wajib mengaktifkan 2FA TOTP dan memakai kebijakan sesi kru.
     */
    public function isCrew(): bool
    {
        return $this !== self::READER;
    }
}
