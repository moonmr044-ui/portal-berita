<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `media`: metadata berkas gambar hasil pipeline WebP.
 *
 * `variants` menyimpan path 3 ukuran (400w/800w/1200w) sebagai JSON untuk
 * atribut `srcset`. Nama file memakai hash unik; kolom `disk` membuat
 * penyimpanan terabstraksi sehingga siap dipindah ke S3/MinIO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('uploader_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('disk', 50)->default('public');
            $table->string('path', 255);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('mime_type', 100)->default('image/webp');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('alt_text', 255)->nullable();
            $table->string('caption', 500)->nullable();
            $table->string('credit', 255)->nullable();
            $table->json('variants')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->timestamps();

            $table->index('uploader_id', 'media_uploader_id_index');
            $table->index('checksum', 'media_checksum_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
