<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `slug_redirects`: memetakan slug lama artikel ke artikel terkini.
 *
 * Setelah artikel PUBLISHED, slug dikunci. Bila Pemred mengubah slug secara
 * paksa, slug lama dicatat di sini dan URL lama diarahkan 301 ke slug baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slug_redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('old_slug', 200)->unique();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('post_id', 'slug_redirects_post_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slug_redirects');
    }
};
