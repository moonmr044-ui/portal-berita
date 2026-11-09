<?php

declare(strict_types=1);

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Http\Middleware\ExpireIdleCrewSession;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
|
| `crewWith()` dan `crewSession()` tersedia global dari tests/Pest.php.
|
*/

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
});

/**
 * Akses rute newsroom dengan sesi yang sudah lolos 2FA.
 */
function asCrewOnboarded(User $user): Illuminate\Testing\TestResponse
{
    return test()->actingAs($user)
        ->withSession(crewSession())
        ->get(route('newsroom.dashboard'));
}

/*
|--------------------------------------------------------------------------
| Rute & Halaman Autentikasi
|--------------------------------------------------------------------------
*/

it('menampilkan halaman login', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk')
        ->assertSee('Alamat Email');
});

it('menampilkan halaman pendaftaran pembaca', function (): void {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Daftar Akun Pembaca');
});

it('mendaftarkan rute autentikasi yang diwajibkan', function (string $name): void {
    expect(Route::has($name))->toBeTrue();
})->with([
    'login',
    'login.store',
    'register',
    'register.store',
    'logout',
    'verification.notice',
    'verification.verify',
    'verification.send',
    'newsroom.dashboard',
    'otp.challenge',
    'otp.verify',
    'otp.resend',
]);

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

it('mengautentikasi pembaca dan mengarahkannya ke halaman depan', function (): void {
    $reader = User::factory()->reader()->create([
        'email' => 'pembaca@portal.test',
        'password' => 'RahasiaPortal2026!',
    ]);

    $this->post(route('login.store'), [
        'email' => 'pembaca@portal.test',
        'password' => 'RahasiaPortal2026!',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($reader);
});

it('mengarahkan kru dengan role sensitif ke halaman tantangan OTP email setelah login', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin-sensitif@portal.test',
        'password' => 'RahasiaPortal2026!',
        'is_crew' => true,
        'is_active' => true,
    ]);
    $admin->assignRole(RoleName::SUPER_ADMIN->value);

    $this->post(route('login.store'), [
        'email' => 'admin-sensitif@portal.test',
        'password' => 'RahasiaPortal2026!',
    ])->assertRedirect(route('otp.challenge'));
});

it('mengarahkan kru DENGAN 2FA ke dasbor newsroom setelah login', function (): void {
    User::factory()->crew()->create([
        'email' => 'kru-siap@portal.test',
        'password' => 'RahasiaPortal2026!',
    ]);

    $this->post(route('login.store'), [
        'email' => 'kru-siap@portal.test',
        'password' => 'RahasiaPortal2026!',
    ])->assertRedirect(route('newsroom.dashboard'));
});

it('mencatat waktu login terakhir', function (): void {
    $reader = User::factory()->reader()->create([
        'email' => 'catat@portal.test',
        'password' => 'RahasiaPortal2026!',
        'last_login_at' => null,
    ]);

    $this->post(route('login.store'), [
        'email' => 'catat@portal.test',
        'password' => 'RahasiaPortal2026!',
    ]);

    expect($reader->fresh()->last_login_at)->not->toBeNull();
});

it('menolak kredensial yang salah', function (): void {
    User::factory()->reader()->create([
        'email' => 'salah@portal.test',
        'password' => 'RahasiaPortal2026!',
    ]);

    $this->post(route('login.store'), [
        'email' => 'salah@portal.test',
        'password' => 'kata-sandi-salah',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('menolak akun yang dinonaktifkan', function (): void {
    User::factory()->reader()->inactive()->create([
        'email' => 'nonaktif@portal.test',
        'password' => 'RahasiaPortal2026!',
    ]);

    $this->post(route('login.store'), [
        'email' => 'nonaktif@portal.test',
        'password' => 'RahasiaPortal2026!',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('melakukan logout dan menghapus sesi', function (): void {
    $reader = User::factory()->reader()->create();

    $this->actingAs($reader)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

/*
|--------------------------------------------------------------------------
| Pendaftaran Pembaca
|--------------------------------------------------------------------------
*/

it('membuat akun pembaca dengan peran reader dan tanpa 2FA', function (): void {
    $this->post(route('register.store'), [
        'name' => 'Pembaca Baru',
        'email' => 'baru@portal.test',
        'password' => 'RahasiaPortal2026!',
        'password_confirmation' => 'RahasiaPortal2026!',
        'terms' => '1',
    ])->assertRedirect(route('home'));

    $user = User::where('email', 'baru@portal.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_crew)->toBeFalse()
        ->and($user->hasRole(RoleName::READER->value))->toBeTrue()
        ->and($user->hasTwoFactorEnabled())->toBeFalse()
        ->and($user->email_verified_at)->toBeNull();

    $this->assertAuthenticatedAs($user);
});

it('TIDAK mengizinkan pendaftaran menentukan peran kru', function (): void {
    $this->post(route('register.store'), [
        'name' => 'Penyusup',
        'email' => 'penyusup@portal.test',
        'password' => 'RahasiaPortal2026!',
        'password_confirmation' => 'RahasiaPortal2026!',
        'terms' => '1',
        'is_crew' => true,
        'roles' => [RoleName::SUPER_ADMIN->value],
    ]);

    $user = User::where('email', 'penyusup@portal.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_crew)->toBeFalse()
        ->and($user->hasRole(RoleName::SUPER_ADMIN->value))->toBeFalse()
        ->and($user->getRoleNames()->all())->toBe([RoleName::READER->value]);
});

it('menolak kata sandi yang terlalu pendek saat pendaftaran', function (): void {
    $this->post(route('register.store'), [
        'name' => 'Lemah',
        'email' => 'lemah@portal.test',
        'password' => 'pendek1',
        'password_confirmation' => 'pendek1',
        'terms' => '1',
    ])->assertSessionHasErrors('password');

    expect(User::where('email', 'lemah@portal.test')->exists())->toBeFalse();
});

it('mewajibkan persetujuan syarat ketentuan', function (): void {
    $this->post(route('register.store'), [
        'name' => 'Tanpa Syarat',
        'email' => 'tanpa-syarat@portal.test',
        'password' => 'RahasiaPortal2026!',
        'password_confirmation' => 'RahasiaPortal2026!',
    ])->assertSessionHasErrors('terms');
});

it('menolak email yang sudah terdaftar', function (): void {
    User::factory()->reader()->create(['email' => 'ganda@portal.test']);

    $this->post(route('register.store'), [
        'name' => 'Ganda',
        'email' => 'ganda@portal.test',
        'password' => 'RahasiaPortal2026!',
        'password_confirmation' => 'RahasiaPortal2026!',
        'terms' => '1',
    ])->assertSessionHasErrors('email');
});

/*
|--------------------------------------------------------------------------
| Verifikasi Email Pembaca
|--------------------------------------------------------------------------
*/

it('menampilkan halaman pemberitahuan verifikasi email', function (): void {
    $reader = User::factory()->unverifiedReader()->create();

    $this->actingAs($reader)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('Verifikasi Email Anda');
});

it('memverifikasi email lewat tautan bertanda tangan', function (): void {
    $reader = User::factory()->unverifiedReader()->create();

    $url = Illuminate\Support\Facades\URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $reader->getKey(), 'hash' => sha1($reader->email)],
    );

    $this->actingAs($reader)->get($url)->assertRedirect(route('home'));

    expect($reader->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('menolak tautan verifikasi tanpa tanda tangan valid', function (): void {
    $reader = User::factory()->unverifiedReader()->create();

    $this->actingAs($reader)
        ->get(route('verification.verify', ['id' => $reader->getKey(), 'hash' => sha1($reader->email)]))
        ->assertForbidden();

    expect($reader->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('mengirim ulang tautan verifikasi', function (): void {
    Illuminate\Support\Facades\Notification::fake();

    $reader = User::factory()->unverifiedReader()->create();

    $this->actingAs($reader)
        ->post(route('verification.send'))
        ->assertRedirect();

    Illuminate\Support\Facades\Notification::assertSentTo(
        $reader,
        Illuminate\Auth\Notifications\VerifyEmail::class,
    );
});

/*
|--------------------------------------------------------------------------
| Email OTP — Tantangan & Penegakan Akses Sensitif Redaksi
|--------------------------------------------------------------------------
*/

it('menampilkan halaman tantangan OTP email untuk role sensitif', function (): void {
    $admin = User::factory()->create([
        'is_crew' => true,
        'is_active' => true,
    ]);
    $admin->assignRole(RoleName::SUPER_ADMIN->value);

    $this->actingAs($admin)
        ->get(route('otp.challenge'))
        ->assertOk()
        ->assertSee('Verifikasi Kode OTP Email');
});

it('meloloskan tantangan OTP email dengan kode yang benar', function (): void {
    $admin = User::factory()->create([
        'is_crew' => true,
        'is_active' => true,
    ]);
    $admin->assignRole(RoleName::SUPER_ADMIN->value);

    $this->actingAs($admin)->get(route('otp.challenge'));
    $otp = session('email_otp');

    $this->actingAs($admin)
        ->post(route('otp.verify'), ['otp' => $otp])
        ->assertRedirect(route('newsroom.dashboard'));
});

it('menolak kode tantangan OTP yang salah', function (): void {
    $admin = User::factory()->create([
        'is_crew' => true,
        'is_active' => true,
    ]);
    $admin->assignRole(RoleName::SUPER_ADMIN->value);

    $this->actingAs($admin)->get(route('otp.challenge'));

    $this->actingAs($admin)
        ->post(route('otp.verify'), ['otp' => '000000'])
        ->assertSessionHasErrors('otp');
});

it('TIDAK mengizinkan kru sensitif tanpa verifikasi OTP masuk dasbor', function (): void {
    $admin = User::factory()->create([
        'is_crew' => true,
        'is_active' => true,
    ]);
    $admin->assignRole(RoleName::SUPER_ADMIN->value);

    $this->actingAs($admin)
        ->get(route('newsroom.dashboard'))
        ->assertRedirect(route('otp.challenge'));
});

it('mengizinkan reporter langsung membuka dasbor tanpa OTP', function (): void {
    $reporter = User::factory()->create([
        'is_crew' => true,
        'is_active' => true,
    ]);
    $reporter->assignRole(RoleName::REPORTER->value);

    $this->actingAs($reporter)
        ->withSession(['crew_last_activity' => now()->timestamp])
        ->get(route('newsroom.dashboard'))
        ->assertOk()
        ->assertSee('Dasbor Redaksi');
});

it('mengizinkan kru yang sudah lolos tantangan membuka dasbor', function (): void {
    $crew = User::factory()->crew()->withRole(RoleName::REPORTER)->create();

    asCrewOnboarded($crew)
        ->assertOk()
        ->assertSee('Dasbor Redaksi');
});

it('TIDAK mengizinkan akun pembaca membuka panel newsroom', function (): void {
    $reader = User::factory()->reader()->create();

    $this->actingAs($reader)
        ->withSession([App\Http\Controllers\Newsroom\TwoFactorController::SESSION_PASSED_KEY => now()->getTimestamp()])
        ->get(route('newsroom.dashboard'))
        ->assertForbidden();
});

it('TIDAK mengizinkan akun kru nonaktif membuka panel newsroom', function (): void {
    $crew = User::factory()->crew()->inactive()->create();

    $this->actingAs($crew)
        ->withSession([App\Http\Controllers\Newsroom\TwoFactorController::SESSION_PASSED_KEY => now()->getTimestamp()])
        ->get(route('newsroom.dashboard'))
        ->assertForbidden();
});

it('mengalihkan tamu ke halaman login', function (): void {
    $this->get(route('newsroom.dashboard'))->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| Sesi Kru Kedaluwarsa Setelah 2 Jam Idle
|--------------------------------------------------------------------------
*/

it('mengakhiri sesi kru yang idle lebih dari 2 jam', function (): void {
    $crew = User::factory()->crew()->create();

    // array_merge dipakai (bukan operator +) karena operator + TIDAK menimpa
    // key yang sudah ada, sehingga nilai idle dari crewSession() akan menang.
    $this->actingAs($crew)
        ->withSession(array_merge(crewSession(), [
            'crew_last_activity_at' => now()->subMinutes(ExpireIdleCrewSession::IDLE_TIMEOUT_MINUTES + 1)->getTimestamp(),
        ]))
        ->get(route('newsroom.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('memperbarui penanda aktivitas kru pada setiap permintaan', function (): void {
    $crew = User::factory()->crew()->withRole(RoleName::REPORTER)->create();

    $this->actingAs($crew)
        ->withSession(array_merge(crewSession(), [
            'crew_last_activity_at' => now()->subMinutes(30)->getTimestamp(),
        ]))
        ->get(route('newsroom.dashboard'))
        ->assertOk();

    $updated = session('crew_last_activity_at');

    expect($updated)->toBeInt()
        ->and(now()->getTimestamp() - $updated)->toBeLessThan(5);
});

it('TIDAK menerapkan batas idle 2 jam pada akun pembaca', function (): void {
    $reader = User::factory()->reader()->create();

    $this->actingAs($reader)->withSession([
        'crew_last_activity_at' => now()->subDays(3)->getTimestamp(),
    ])->get(route('home'))->assertOk();

    $this->assertAuthenticatedAs($reader);
});

/*
|--------------------------------------------------------------------------
| Security Headers
|--------------------------------------------------------------------------
*/

it('memasang security headers pada respons publik', function (): void {
    $response = $this->get(route('home'));

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    expect($response->headers->get('Permissions-Policy'))->toContain('camera=()')
        ->and($response->headers->get('Content-Security-Policy'))->toContain("default-src 'self'")
        ->and($response->headers->get('Content-Security-Policy'))->toContain("object-src 'none'");
});

it('memasang security headers pada halaman login', function (): void {
    $this->get(route('login'))->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('menambahkan X-Robots-Tag noindex pada respons 410 Gone', function (): void {
    Route::get('/uji-410', static function (): Illuminate\Http\Response {
        return response('Artikel telah diturunkan.', Illuminate\Http\Response::HTTP_GONE);
    });

    $response = $this->get('/uji-410');

    $response->assertStatus(410);
    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

/*
|--------------------------------------------------------------------------
| Policy — Aturan Otorisasi Keras
|--------------------------------------------------------------------------
*/

it('TIDAK mengizinkan reporter melakukan approve atau schedule', function (): void {
    $reporter = crewWith(RoleName::REPORTER);

    expect($reporter->can(PermissionName::POST_APPROVE->value))->toBeFalse()
        ->and($reporter->can(PermissionName::POST_SCHEDULE->value))->toBeFalse()
        ->and($reporter->can(PermissionName::POST_TAKEDOWN->value))->toBeFalse();
});

it('mengizinkan reporter menyunting naskahnya sendiri saat DRAFT', function (): void {
    $reporter = crewWith(RoleName::REPORTER);
    $post = Post::factory()->draft()->create(['author_id' => $reporter->getKey()]);

    expect($reporter->can('update', $post))->toBeTrue()
        ->and($reporter->can('submit', $post))->toBeTrue();
});

it('TIDAK mengizinkan reporter menyunting naskah saat UNDER_REVIEW', function (): void {
    $reporter = crewWith(RoleName::REPORTER);
    $post = Post::factory()->underReview()->create(['author_id' => $reporter->getKey()]);

    expect($reporter->can('update', $post))->toBeFalse();
});

it('TIDAK mengizinkan reporter menyunting naskah milik reporter lain', function (): void {
    $reporter = crewWith(RoleName::REPORTER);
    $other = crewWith(RoleName::REPORTER);
    $post = Post::factory()->draft()->create(['author_id' => $other->getKey()]);

    expect($reporter->can('update', $post))->toBeFalse()
        ->and($reporter->can('submit', $post))->toBeFalse();
});

it('TIDAK mengizinkan reporter menarik naskah yang sudah diklaim editor', function (): void {
    $reporter = crewWith(RoleName::REPORTER);
    $editor = crewWith(RoleName::DESK_EDITOR);

    $post = Post::factory()->underReview()
        ->create(['author_id' => $reporter->getKey()])
        ->fresh();

    $post->forceFill([
        'claimed_by' => $editor->getKey(),
        'claimed_at' => now(),
        'claim_expires_at' => now()->addHours(4),
    ])->save();

    expect($reporter->can('withdraw', $post->fresh()))->toBeFalse();
});

it('TIDAK mengizinkan desk editor menyetujui naskah buatannya sendiri', function (): void {
    $editor = crewWith(RoleName::DESK_EDITOR);
    $category = Category::factory()->create();
    $editor->assignedCategories()->attach($category->getKey());

    $post = Post::factory()->underReview()->create([
        'author_id' => $editor->getKey(),
        'category_id' => $category->getKey(),
    ]);

    $post->forceFill([
        'claimed_by' => $editor->getKey(),
        'claimed_at' => now(),
        'claim_expires_at' => now()->addHours(4),
    ])->save();

    expect($editor->can('approve', $post->fresh()))->toBeFalse();
});

it('TIDAK mengizinkan desk editor memproses kategori di luar penugasannya', function (): void {
    $editor = crewWith(RoleName::DESK_EDITOR);
    $assigned = Category::factory()->create();
    $other = Category::factory()->create();
    $reporter = crewWith(RoleName::REPORTER);

    $editor->assignedCategories()->attach($assigned->getKey());

    $inScope = Post::factory()->underReview()->create([
        'author_id' => $reporter->getKey(),
        'category_id' => $assigned->getKey(),
    ]);

    $outOfScope = Post::factory()->underReview()->create([
        'author_id' => $reporter->getKey(),
        'category_id' => $other->getKey(),
    ]);

    expect($editor->can('claim', $inScope))->toBeTrue()
        ->and($editor->can('claim', $outOfScope))->toBeFalse();
});

it('mengizinkan desk editor menyetujui naskah yang ia klaim di kategorinya', function (): void {
    $editor = crewWith(RoleName::DESK_EDITOR);
    $reporter = crewWith(RoleName::REPORTER);
    $category = Category::factory()->create();
    $editor->assignedCategories()->attach($category->getKey());

    $post = Post::factory()->underReview()->create([
        'author_id' => $reporter->getKey(),
        'category_id' => $category->getKey(),
    ]);

    $post->forceFill([
        'claimed_by' => $editor->getKey(),
        'claimed_at' => now(),
        'claim_expires_at' => now()->addHours(4),
    ])->save();

    expect($editor->can('approve', $post->fresh()))->toBeTrue()
        ->and($editor->can('schedule', $post->fresh()))->toBeTrue()
        ->and($editor->can('reject', $post->fresh()))->toBeTrue();
});

it('TIDAK mengizinkan desk editor menyetujui naskah yang diklaim editor lain', function (): void {
    $editor = crewWith(RoleName::DESK_EDITOR);
    $other = crewWith(RoleName::DESK_EDITOR);
    $reporter = crewWith(RoleName::REPORTER);
    $category = Category::factory()->create();

    $editor->assignedCategories()->attach($category->getKey());
    $other->assignedCategories()->attach($category->getKey());

    $post = Post::factory()->underReview()->create([
        'author_id' => $reporter->getKey(),
        'category_id' => $category->getKey(),
    ]);

    $post->forceFill([
        'claimed_by' => $other->getKey(),
        'claimed_at' => now(),
        'claim_expires_at' => now()->addHours(4),
    ])->save();

    expect($editor->can('approve', $post->fresh()))->toBeFalse();
});

it('mengizinkan pemred melakukan takedown artikel terbit', function (): void {
    $pemred = crewWith(RoleName::PEMRED);
    $post = Post::factory()->published()->create();

    expect($pemred->can('takedown', $post))->toBeTrue();
});

it('TIDAK mengizinkan takedown artikel yang belum terbit', function (): void {
    $pemred = crewWith(RoleName::PEMRED);
    $draft = Post::factory()->draft()->create();

    expect($pemred->can('takedown', $draft))->toBeFalse();
});

it('mengizinkan super-admin melakukan apa pun lewat Gate::before', function (): void {
    $superAdmin = crewWith(RoleName::SUPER_ADMIN);
    $post = Post::factory()->draft()->create(['author_id' => crewWith(RoleName::REPORTER)->getKey()]);

    expect($superAdmin->can('update', $post))->toBeTrue()
        ->and($superAdmin->can('takedown', $post))->toBeTrue()
        ->and($superAdmin->can(PermissionName::CREW_MANAGE->value))->toBeTrue()
        ->and($superAdmin->hasRole(RoleName::SUPER_ADMIN->value))->toBeTrue();
});

it('membatasi pembaca tanpa verifikasi email dari berkomentar', function (): void {
    $reader = User::factory()->unverifiedReader()->create();

    expect($reader->can(PermissionName::COMMENT_CREATE->value))->toBeFalse()
        ->and($reader->hasVerifiedEmailOrFail())->toBeFalse();
});

it('mengizinkan pembaca terverifikasi berkomentar', function (): void {
    $reader = User::factory()->reader()->withRole(RoleName::READER)->create();

    expect($reader->hasVerifiedEmailOrFail())->toBeTrue()
        ->and($reader->can(PermissionName::COMMENT_CREATE->value))->toBeTrue();
});

it('TIDAK mengizinkan pembaca memoderasi komentar', function (): void {
    $reader = User::factory()->reader()->create();

    expect($reader->can(PermissionName::COMMENT_MODERATE->value))->toBeFalse();
});

it('TIDAK mengizinkan pemred menghapus akunnya sendiri lewat kebijakan', function (): void {
    $pemred = crewWith(RoleName::PEMRED);

    expect($pemred->can('delete', $pemred))->toBeFalse()
        ->and($pemred->can('deleteOwnAccount', $pemred))->toBeTrue();
});

it('TIDAK mengizinkan penghapusan media yang dipakai artikel terbit', function (): void {
    $reporter = crewWith(RoleName::REPORTER);
    $media = Media::factory()->create(['uploader_id' => $reporter->getKey()]);

    Post::factory()->published()->create(['featured_media_id' => $media->getKey()]);

    expect($reporter->can('delete', $media))->toBeFalse();
});

it('mengizinkan penghapusan media yang tidak dipakai artikel', function (): void {
    $reporter = crewWith(RoleName::REPORTER);
    $media = Media::factory()->create(['uploader_id' => $reporter->getKey()]);

    expect($reporter->can('delete', $media))->toBeTrue();
});

it('TIDAK mengizinkan penghapusan kategori yang masih dipakai artikel', function (): void {
    // Gate::before memberi super-admin akses penuh, jadi aturan "kategori yang
    // masih dipakai tidak boleh dihapus" harus diuji langsung ke Policy.
    $superAdmin = crewWith(RoleName::SUPER_ADMIN);
    $category = Category::factory()->create();

    Post::factory()->draft()->create(['category_id' => $category->getKey()]);

    $policy = new App\Policies\CategoryPolicy;

    expect($policy->delete($superAdmin, $category))->toBeFalse()
        ->and($policy->delete($superAdmin, Category::factory()->create()))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Rate Limiter
|--------------------------------------------------------------------------
*/

it('mendaftarkan seluruh rate limiter yang diwajibkan', function (string $limiter): void {
    $limit = RateLimiter::limiter($limiter);

    expect($limit)->not->toBeNull();
})->with([
    'login',
    'comments',
    'search',
    'right-of-reply',
]);
