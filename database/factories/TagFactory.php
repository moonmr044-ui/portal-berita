<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Isu '.fake()->unique()->numerify('####').' '.fake()->randomElement([
            'Pemilu', 'Ekonomi Digital', 'Timnas Indonesia', 'Energi Terbarukan',
            'Kecerdasan Buatan', 'Transportasi Publik', 'Kesehatan Mental',
            'Startup Lokal', 'Perubahan Iklim', 'Pendidikan Vokasi',
            'Harga Pangan', 'Infrastruktur Jalan', 'Pariwisata Bali',
            'Kendaraan Listrik', 'Film Indonesia',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
