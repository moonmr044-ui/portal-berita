<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Status moderasi komentar pembaca.
 *
 * Komentar berstatus PENDING belum tampil di halaman publik.
 */
enum CommentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case SPAM = 'spam';
    case REJECTED = 'rejected';

    /**
     * Label Bahasa Indonesia untuk panel moderasi.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Moderasi',
            self::APPROVED => 'Disetujui',
            self::SPAM => 'Spam',
            self::REJECTED => 'Ditolak',
        };
    }

    /**
     * Kelas Tailwind untuk badge moderasi.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::APPROVED => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            self::SPAM => 'bg-rose-100 text-rose-800 ring-rose-200',
            self::REJECTED => 'bg-zinc-200 text-zinc-700 ring-zinc-300',
        };
    }

    /**
     * Hanya komentar APPROVED yang tampil di halaman publik.
     */
    public function isPubliclyVisible(): bool
    {
        return $this === self::APPROVED;
    }

    /**
     * Status yang masih dapat diubah moderator.
     */
    public function isModeratable(): bool
    {
        return $this !== self::SPAM;
    }

    /**
     * Semua nilai status sebagai array string.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            self::cases(),
        );
    }

    /**
     * Peta nilai => label untuk pilihan form.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }
}
