<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `post_status_histories`: jejak audit setiap transisi FSM artikel.
 * Ditulis di dalam transaksi yang sama dengan perubahan status posts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['post_id', 'created_at'], 'post_status_histories_post_created_index');
            $table->index('to_status', 'post_status_histories_to_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_status_histories');
    }
};
