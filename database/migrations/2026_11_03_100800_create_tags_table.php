<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `tags` dan pivot `post_tag` untuk penandaan artikel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 100)->unique();
            $table->timestamps();

            $table->index('name', 'tags_name_index');
        });

        Schema::create('post_tag', function (Blueprint $table): void {
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('tag_id')
                ->constrained('tags')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['post_id', 'tag_id']);
            $table->index(['tag_id', 'post_id'], 'post_tag_tag_post_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tags');
    }
};
