<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table): void {
            $table->id();
            $table->string('question', 300);
            $table->text('description')->nullable();
            $table->string('edition_label', 100)->default('Edisi Pekan Ini');
            $table->string('deadline_label', 100)->default('Akhir pekan');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'created_at']);
        });

        Schema::create('poll_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_id')
                ->constrained('polls')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('label', 250);
            $table->unsignedInteger('votes_count')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('poll_votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_id')
                ->constrained('polls')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('poll_option_id')
                ->constrained('poll_options')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('voter_token', 120);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['poll_id', 'voter_token'], 'poll_votes_unique_voter');
        });

        Schema::create('epapers', function (Blueprint $table): void {
            $table->id();
            $table->string('edition_number', 60);
            $table->date('edition_date');
            $table->string('title', 250);
            $table->text('description')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->unsignedInteger('downloads_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'edition_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epapers');
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
    }
};
