<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `post_revisions`: snapshot naskah setiap kali disimpan.
 * Retensi maksimal 50 revisi terbaru per artikel (ditegakkan di model/job).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('title', 180)->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->unsignedInteger('lock_version')->default(1);
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['post_id', 'created_at'], 'post_revisions_post_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_revisions');
    }
};
