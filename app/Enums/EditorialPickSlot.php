<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Slot kurasi Editorial Picks di homepage.
 *
 * Terdiri dari 1 Headline Utama + 4 Editorial Pick (grid 2x2).
 * Kolom `slot` pada tabel `editorial_picks` bersifat UNIQUE.
 */
enum EditorialPickSlot: string
{
    case HEADLINE = 'headline';
    case PICK_1 = 'pick_1';
    case PICK_2 = 'pick_2';
    case PICK_3 = 'pick_3';
    case PICK_4 = 'pick_4';

    /**
     * Label Bahasa Indonesia untuk panel kurasi.
     */
    public function label(): string
    {
        return match ($this) {
            self::HEADLINE => 'Headline Utama',
            self::PICK_1 => 'Editorial Pick 1',
            self::PICK_2 => 'Editorial Pick 2',
            self::PICK_3 => 'Editorial Pick 3',
            self::PICK_4 => 'Editorial Pick 4',
        };
    }

    /**
     * Slot Headline dirender dengan rasio 16:9 di hero section.
     */
    public function isHeadline(): bool
    {
        return $this === self::HEADLINE;
    }

    /**
     * Urutan render pada grid hero (headline selalu paling awal).
     */
    public function sortOrder(): int
    {
        return match ($this) {
            self::HEADLINE => 1,
            self::PICK_1 => 2,
            self::PICK_2 => 3,
            self::PICK_3 => 4,
            self::PICK_4 => 5,
        };
    }

    /**
     * Slot editorial pick (grid 2x2), tidak termasuk headline.
     *
     * @return list<self>
     */
    public static function picks(): array
    {
        return [self::PICK_1, self::PICK_2, self::PICK_3, self::PICK_4];
    }

    /**
     * Semua nilai slot sebagai array string.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $slot): string => $slot->value,
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

        foreach (self::cases() as $slot) {
            $options[$slot->value] = $slot->label();
        }

        return $options;
    }
}
