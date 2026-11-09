<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `breaking_news`: item ticker berita terkini di Top Utility Bar.
 * Hanya item aktif dalam rentang starts_at..ends_at yang dirender.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breaking_news', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 200);
            $table->string('url', 500)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at'], 'breaking_news_active_window_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breaking_news');
    }
};
