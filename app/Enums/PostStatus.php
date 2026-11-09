<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Status artikel — Finite State Machine.
 *
 * Dilarang menggunakan boolean `is_published`. Status artikel direpresentasikan
 * oleh kolom `status` (Enum ini) + `published_at` (nullable, UTC, indexed).
 *
 * Matriks transisi di method `allowedTransitions()` adalah SUMBER KEBENARAN
 * TUNGGAL. PostWorkflowService hanya boleh mengeksekusi transisi yang
 * didaftarkan di sini; transisi di luar matriks wajib melempar
 * InvalidTransitionException.
 */
enum PostStatus: string
{
    case DRAFT = 'draft';
    case UNDER_REVIEW = 'under_review';
    case REJECTED = 'rejected';
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';
    case TAKEDOWN = 'takedown';

    /**
     * Label Bahasa Indonesia untuk panel newsroom.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::UNDER_REVIEW => 'Menunggu Review',
            self::REJECTED => 'Ditolak',
            self::SCHEDULED => 'Terjadwal',
            self::PUBLISHED => 'Terbit',
            self::TAKEDOWN => 'Diturunkan',
        };
    }

    /**
     * Kelas Tailwind untuk badge status di panel kru.
     * Nilai literal (bukan dinamis) agar tidak terhapus saat purge Vite.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-100 text-slate-700 ring-slate-200',
            self::UNDER_REVIEW => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::REJECTED => 'bg-rose-100 text-rose-800 ring-rose-200',
            self::SCHEDULED => 'bg-sky-100 text-sky-800 ring-sky-200',
            self::PUBLISHED => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            self::TAKEDOWN => 'bg-zinc-800 text-zinc-100 ring-zinc-700',
        };
    }

    /**
     * Status yang terlihat oleh publik pada frontend.
     */
    public function isPubliclyVisible(): bool
    {
        return $this === self::PUBLISHED;
    }

    /**
     * Status yang mengunci slug artikel (tidak boleh berubah bebas).
     */
    public function locksSlug(): bool
    {
        return in_array($this, [self::PUBLISHED, self::TAKEDOWN], true);
    }

    /**
     * Status yang mengizinkan reporter pemilik menyunting naskahnya.
     */
    public function isEditableByReporter(): bool
    {
        return in_array($this, [self::DRAFT, self::REJECTED], true);
    }

    /**
     * Status yang menandakan artikel sedang dipegang editor untuk direview.
     */
    public function isInEditorialQueue(): bool
    {
        return $this === self::UNDER_REVIEW;
    }

    /**
     * Status terjadwal yang menunggu scheduler menerbitkan.
     */
    public function awaitsScheduler(): bool
    {
        return $this === self::SCHEDULED;
    }

    /**
     * Daftar transisi yang diizinkan dari status ini.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::UNDER_REVIEW],
            self::UNDER_REVIEW => [self::DRAFT, self::REJECTED, self::SCHEDULED, self::PUBLISHED],
            self::REJECTED => [self::UNDER_REVIEW],
            self::SCHEDULED => [self::PUBLISHED, self::UNDER_REVIEW],
            self::PUBLISHED => [self::TAKEDOWN],
            self::TAKEDOWN => [self::PUBLISHED],
        };
    }

    /**
     * Apakah transisi dari status ini menuju $to diizinkan matriks.
     */
    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
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
     * Status yang boleh tampil di feed publik dan sitemap.
     *
     * @return list<string>
     */
    public static function publiclyVisibleValues(): array
    {
        return [self::PUBLISHED->value];
    }
}
