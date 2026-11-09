<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Status penanganan permohonan Hak Jawab (UU Pers & Pedoman Media Siber).
 */
enum RightOfReplyStatus: string
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';

    /**
     * Label Bahasa Indonesia untuk panel Pemred.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Penanganan',
            self::REVIEWED => 'Sudah Ditelaah',
            self::PUBLISHED => 'Dipublikasikan',
            self::REJECTED => 'Ditolak',
        };
    }

    /**
     * Kelas Tailwind untuk badge status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::REVIEWED => 'bg-sky-100 text-sky-800 ring-sky-200',
            self::PUBLISHED => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            self::REJECTED => 'bg-rose-100 text-rose-800 ring-rose-200',
        };
    }

    /**
     * Permohonan yang sudah selesai tidak boleh diproses ulang tanpa alasan baru.
     */
    public function isClosed(): bool
    {
        return in_array($this, [self::PUBLISHED, self::REJECTED], true);
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
}
