<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom khusus kru & pembaca ke tabel `users` bawaan Laravel.
 *
 * - is_crew: akun kru (super-admin/pemred/desk-editor/reporter) wajib 2FA.
 * - Kolom 2FA bersifat nullable; hanya diisi setelah enrolment TOTP selesai.
 * - last_login_at dipakai untuk audit & kebijakan sesi kru 2 jam idle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_crew')->default(false)->after('password');
            $table->text('two_factor_secret')->nullable()->after('is_crew');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_confirmed_at');
            $table->boolean('is_active')->default(true)->after('last_login_at');
            $table->softDeletes();

            $table->index(['is_crew', 'is_active'], 'users_is_crew_is_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_is_crew_is_active_index');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'is_crew',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'last_login_at',
                'is_active',
            ]);
        });
    }
};
