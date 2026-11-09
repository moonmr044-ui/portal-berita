<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `bookmarks`: artikel simpanan pembaca.
 * Unique (user_id, post_id) — satu pembaca satu bookmark per artikel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'post_id'], 'bookmarks_user_post_unique');
            $table->index(['user_id', 'created_at'], 'bookmarks_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
