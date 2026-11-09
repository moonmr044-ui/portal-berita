<?php

declare(strict_types=1);

use App\Enums\PostStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `posts`: naskah berita dengan FSM status, mekanisme klaim editor,
 * optimistic locking (lock_version), dan soft delete.
 *
 * Index wajib (spesifikasi §6):
 *   (status, published_at)
 *   (category_id, status, published_at)
 *   (author_id, status)
 *   (status, claimed_by, claim_expires_at)
 *   slug UNIQUE
 *   FULLTEXT (title, excerpt, body)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('author_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('editor_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Mekanisme klaim editor
            $table->foreignId('claimed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('claim_expires_at')->nullable();

            $table->foreignId('featured_media_id')
                ->nullable()
                ->constrained('media')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();

            // FSM: status + published_at (dilarang boolean is_published)
            $table->string('status', 20)->default(PostStatus::DRAFT->value);
            $table->timestamp('published_at')->nullable();

            $table->text('editorial_notes')->nullable();
            $table->text('takedown_reason')->nullable();

            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedInteger('lock_version')->default(1);

            $table->string('seo_title', 200)->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->string('source_name', 150)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index wajib
            $table->index(['status', 'published_at'], 'posts_status_published_at_index');
            $table->index(['category_id', 'status', 'published_at'], 'posts_category_status_published_index');
            $table->index(['author_id', 'status'], 'posts_author_status_index');
            $table->index(['status', 'claimed_by', 'claim_expires_at'], 'posts_claim_index');
            $table->index('published_at', 'posts_published_at_index');
            $table->index('views_count', 'posts_views_count_index');
            $table->index('source_url', 'posts_source_url_index');
        });

        // FULLTEXT untuk pencarian default (SearchService).
        // Ditambahkan lewat raw statement karena Blueprint tidak menyediakan
        // API fulltext yang portabel untuk MySQL.
        Schema::getConnection()->statement(
            'ALTER TABLE posts ADD FULLTEXT posts_fulltext_index (title, excerpt, body)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
