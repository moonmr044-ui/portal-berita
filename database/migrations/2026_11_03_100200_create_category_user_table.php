<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot `category_user`: penugasan rubrik kepada desk-editor.
 *
 * Desk Editor hanya boleh memproses naskah pada kategori yang ditugaskan
 * padanya. Penugasan ini menjadi dasar pembatasan di Policy dan query scope.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_user', function (Blueprint $table): void {
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();

            $table->primary(['category_id', 'user_id']);
            $table->index('user_id', 'category_user_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_user');
    }
};
