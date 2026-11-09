<?php

declare(strict_types=1);

use App\Enums\ReactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `reactions`: reaksi emotikon pembaca pada artikel.
 * Unique (user_id, post_id) — satu pembaca satu reaksi per artikel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('type', 20)->default(ReactionType::LIKE->value);
            $table->timestamps();

            $table->unique(['user_id', 'post_id'], 'reactions_user_post_unique');
            $table->index(['post_id', 'type'], 'reactions_post_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
