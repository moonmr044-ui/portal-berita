<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Jenis reaksi emotikon pada artikel.
 *
 * Satu pembaca hanya boleh memberi satu reaksi per artikel
 * (unique constraint pada `(user_id, post_id)`).
 */
enum ReactionType: string
{
    case LIKE = 'like';
    case SURPRISED = 'surprised';
    case SAD = 'sad';
    case ANGRY = 'angry';

    /**
     * Label Bahasa Indonesia untuk tampilan publik.
     */
    public function label(): string
    {
        return match ($this) {
            self::LIKE => 'Suka',
            self::SURPRISED => 'Terkejut',
            self::SAD => 'Sedih',
            self::ANGRY => 'Marah',
        };
    }

    /**
     * Karakter emotikon yang dirender sebagai tombol reaksi.
     */
    public function emoji(): string
    {
        return match ($this) {
            self::LIKE => '👍',
            self::SURPRISED => '😮',
            self::SAD => '😢',
            self::ANGRY => '😠',
        };
    }

    /**
     * Semua nilai reaksi sebagai array string.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $type): string => $type->value,
            self::cases(),
        );
    }

    /**
     * Peta nilai => label untuk membangun pilihan form/select.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }
}
