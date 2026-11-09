<?php

declare(strict_types=1);

use App\Enums\RightOfReplyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `right_of_reply_requests`: antrean Hak Jawab untuk Pemred.
 *
 * Form publik `/hak-jawab` dilindungi rate limit + honeypot. Kebijakan
 * penanganan merujuk UU Pers & Pedoman Media Siber (docs/KEBIJAKAN_REDAKSI.md).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('right_of_reply_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->nullable()
                ->constrained('posts')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('requester_name', 150);
            $table->string('requester_email', 200);
            $table->string('requester_phone', 30)->nullable();
            $table->text('statement');
            $table->string('status', 20)->default(RightOfReplyStatus::PENDING->value);
            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'ror_status_created_index');
            $table->index('post_id', 'ror_post_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('right_of_reply_requests');
    }
};
