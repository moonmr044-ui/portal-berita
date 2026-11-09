<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hash = Str::random(24);
        $width = fake()->randomElement([1200, 1600]);
        $height = fake()->randomElement([675, 900, 1067]);

        return [
            'uploader_id' => User::factory(),
            'disk' => 'public',
            'path' => sprintf('media/%s/%s.webp', now()->format('Y/m'), $hash),
            'width' => $width,
            'height' => $height,
            'mime_type' => 'image/webp',
            'size_bytes' => fake()->numberBetween(80_000, 480_000),
            'alt_text' => fake()->sentence(6),
            'caption' => fake()->sentence(10),
            'credit' => 'Dok. '.fake()->company(),
            'checksum' => hash('sha256', $hash),
            'variants' => [
                '400w' => sprintf('media/%s/%s-400w.webp', now()->format('Y/m'), $hash),
                '800w' => sprintf('media/%s/%s-800w.webp', now()->format('Y/m'), $hash),
                '1200w' => sprintf('media/%s/%s-1200w.webp', now()->format('Y/m'), $hash),
            ],
        ];
    }

    /**
     * Media tanpa varian srcset (mis. hasil impor lama).
     */
    public function withoutVariants(): static
    {
        return $this->state(fn (array $attributes): array => [
            'variants' => null,
        ]);
    }
}
