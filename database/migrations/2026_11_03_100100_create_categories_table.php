<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `categories`: rubrik berita dengan dukungan hierarki (parent_id)
 * dan pengalihan kategori nonaktif (redirect_to_category_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->char('badge_color', 7)->default('#1D4ED8');
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('redirect_to_category_id')
                ->nullable()
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'categories_is_active_sort_order_index');
            $table->index('parent_id', 'categories_parent_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
