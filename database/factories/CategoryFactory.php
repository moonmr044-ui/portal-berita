<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'News', 'Tekno', 'Bisnis', 'Bola', 'Hiburan', 'Otomotif',
            'Sains', 'Kesehatan', 'Politik', 'Ekonomi', 'Gaya Hidup', 'Travel',
        ]).' '.fake()->unique()->numerify('##');

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'badge_color' => fake()->randomElement([
                '#1D4ED8', '#B91C1C', '#047857', '#7C3AED', '#B45309', '#0F766E',
            ]),
            'description' => null,
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
            'redirect_to_category_id' => null,
        ];
    }

    /**
     * Kategori nonaktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Kategori nonaktif yang mengalihkan ke kategori lain dengan 301.
     */
    public function redirectingTo(Category $target): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
            'redirect_to_category_id' => $target->id,
        ]);
    }
}
