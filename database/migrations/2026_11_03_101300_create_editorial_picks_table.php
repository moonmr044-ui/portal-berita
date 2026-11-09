<?php

declare(strict_types=1);

use App\Enums\EditorialPickSlot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `editorial_picks`: kurasi slot hero homepage oleh Pemred.
 *
 * Kolom `slot` UNIQUE sehingga setiap slot hanya punya satu artikel.
 * Bila post_id NULL atau artikelnya tidak PUBLISHED, homepage otomatis
 * fallback ke artikel PUBLISHED terbaru tanpa duplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editorial_picks', function (Blueprint $table): void {
            $table->id();
            $table->string('slot', 20)->unique();
            $table->foreignId('post_id')
                ->nullable()
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();

            $table->index('post_id', 'editorial_picks_post_id_index');
        });

        // Slot default agar halaman kurasi selalu memiliki baris lengkap.
        $now = now();

        foreach (EditorialPickSlot::cases() as $slot) {
            Schema::getConnection()->table('editorial_picks')->insertOrIgnore([
                'slot' => $slot->value,
                'post_id' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('editorial_picks');
    }
};
