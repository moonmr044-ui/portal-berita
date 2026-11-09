<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\RoleName;
use App\Mail\LoginOtpMail;
use App\Models\User;
use Database\Seeders\NewsPortalDemoSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsroomOtpAndLoginFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(NewsPortalDemoSeeder::class);
    }

    public function test_super_admin_requires_email_otp_and_can_verify(): void
    {
        Mail::fake();

        $admin = User::where('email', 'ahmadarifaie64@gmail.com')->first();

        // 1. Login with password
        $res = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $res->assertRedirect('/verifikasi-otp');

        // 2. Load OTP challenge page (sends OTP email)
        $res = $this->get('/verifikasi-otp');
        $res->assertOk();
        $res->assertSee('Verifikasi Kode OTP Email');
        Mail::assertSent(LoginOtpMail::class);

        // 3. Cannot access /newsroom before OTP verification
        $this->get('/newsroom')->assertRedirect('/verifikasi-otp');

        // 4. Verify with correct OTP
        $otp = session('email_otp');
        $this->assertNotEmpty($otp);

        $res = $this->post('/verifikasi-otp', [
            'otp' => $otp,
        ]);

        $res->assertRedirect('/newsroom');
        $this->get('/newsroom')->assertOk();
    }

    public function test_reporter_does_not_require_otp_and_enters_newsroom_directly(): void
    {
        $reporter = User::role(RoleName::REPORTER->value)->first();

        $res = $this->post('/login', [
            'email' => $reporter->email,
            'password' => 'password',
        ]);

        $res->assertRedirect('/newsroom');
        $this->get('/newsroom')->assertOk();
    }

    public function test_reader_is_redirected_to_homepage(): void
    {
        $reader = User::role(RoleName::READER->value)->first();

        $res = $this->post('/login', [
            'email' => $reader->email,
            'password' => 'password',
        ]);

        $res->assertRedirect('/');
    }
}
