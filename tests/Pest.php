<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Http\Controllers\Newsroom\TwoFactorController as TwoFactorSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Setiap closure test terikat pada satu kelas TestCase. Secara default
| menggunakan PHPUnit\Framework\TestCase; di sini diarahkan ke
| Tests\TestCase milik aplikasi untuk direktori Feature dan Unit.
|
| RefreshDatabase aktif agar setiap test berjalan pada database bersih.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Helper Test
|--------------------------------------------------------------------------
*/

/**
 * Buat akun kru dengan peran tertentu (2FA sudah terkonfirmasi).
 */
function crewWith(RoleName $role): User
{
    return User::factory()->crew()->withRole($role)->create();
}

/**
 * Sesi kru yang sudah lolos tantangan 2FA dan belum idle.
 *
 * @return array<string, int>
 */
function crewSession(): array
{
    return [
        'email_otp_verified' => true,
        'crew_last_activity' => now()->getTimestamp(),
        TwoFactorSession::SESSION_PASSED_KEY => now()->getTimestamp(),
        'crew_last_activity_at' => now()->getTimestamp(),
    ];
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Tambahan expectation kustom untuk proyek portal berita.
|
*/

expect()->extend('toBeWithinRange', function (int $min, int $max) {
    return $this->toBeGreaterThanOrEqual($min)->toBeLessThanOrEqual($max);
});

expect()->extend('toBeUtc', function () {
    return expect($this->value->getTimezone()->getName())->toBe('UTC');
});
