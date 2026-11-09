<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `post_corrections`: Ralat resmi untuk artikel PUBLISHED.
 *
 * Koreksi faktual WAJIB melalui fitur ini; konten asli tidak dihapus agar
 * jejak histori tetap utuh. Dirender sebagai kotak ralat di halaman artikel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_corrections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->text('summary');
            $table->timestamp('published_at')->useCurrent();
            $table->timestamps();

            $table->index(['post_id', 'published_at'], 'post_corrections_post_published_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_corrections');
    }
};
