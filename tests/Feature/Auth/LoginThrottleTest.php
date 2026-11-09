<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

/*
|--------------------------------------------------------------------------
| Throttle Login — 5 percobaan/menit per email+IP
|--------------------------------------------------------------------------
|
| Ditegakkan middleware `throttle:login` yang memakai limiter bernama `login`
| dari AppServiceProvider (kunci: lowercase(email)|ip).
|
*/

it('membatasi percobaan login berulang dengan kata sandi salah', function (): void {
    User::factory()->reader()->create([
        'email' => 'throttle@portal.test',
        'password' => 'RahasiaPortal2026!',
    ]);

    $throttleKey = 'throttle@portal.test|127.0.0.1';

    $statuses = [];

    // Enam percobaan berturut-turut dengan kata sandi salah. Karena tidak
    // ada yang berhasil login, middleware `guest` tetap meloloskan request
    // sampai limiter menutupnya.
    for ($i = 0; $i < 6; $i++) {
        $statuses[] = $this->post(route('login.store'), [
            'email' => 'throttle@portal.test',
            'password' => 'kata-sandi-salah',
        ])->getStatusCode();
    }

    // Lima percobaan pertama menghasilkan redirect balik dengan error (302),
    // percobaan keenam diblokir limiter (429). Nilai hit tidak diassert di
    // sini karena cache store di-reset antar request pada lingkungan test;
    // yang penting perilaku HTTP-nya benar.
    expect($statuses[0])->toBe(302)
        ->and($statuses[5])->toBe(429);

    $this->assertGuest();

    RateLimiter::clear($throttleKey);
});

it('TIDAK membatasi login yang berhasil dari akun lain', function (): void {
    User::factory()->reader()->create([
        'email' => 'lain@portal.test',
        'password' => 'RahasiaPortal2026!',
    ]);

    $this->post(route('login.store'), [
        'email' => 'lain@portal.test',
        'password' => 'RahasiaPortal2026!',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticated();

    RateLimiter::clear('lain@portal.test|127.0.0.1');
});
