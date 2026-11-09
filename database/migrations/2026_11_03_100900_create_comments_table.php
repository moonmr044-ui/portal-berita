<?php

declare(strict_types=1);

use App\Enums\CommentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `comments`: komentar pembaca dengan moderasi dan dukungan balasan
 * bersarang (parent_id). IP komentator hanya disimpan sebagai hash satu arah
 * (hash_hmac SHA-256 dengan APP_KEY) demi kepatuhan UU PDP.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->text('body');
            $table->string('status', 20)->default(CommentStatus::PENDING->value);
            $table->char('ip_hash', 64)->nullable();
            $table->unsignedInteger('reported_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['post_id', 'status', 'created_at'], 'comments_post_status_created_index');
            $table->index(['user_id', 'created_at'], 'comments_user_created_index');
            $table->index('parent_id', 'comments_parent_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
