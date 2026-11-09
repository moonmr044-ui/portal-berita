<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsroom;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Http\Requests\Auth\TwoFactorConfirmRequest;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Enrolment & tantangan 2FA TOTP untuk akun kru.
 *
 * Alur:
 *  1. setup()   -> tampilkan secret + QR, minta kode verifikasi pertama
 *  2. confirm() -> aktifkan 2FA, tampilkan recovery code sekali
 *  3. challenge()-> minta kode saat login berikutnya
 *  4. verify()  -> verifikasi kode atau recovery code
 */
final class TwoFactorController extends Controller
{
    /**
     * Kunci session untuk secret yang menunggu konfirmasi.
     */
    private const PENDING_SECRET_KEY = 'two_factor_pending_secret';

    /**
     * Kunci session penanda 2FA sudah lolos pada sesi ini.
     */
    public const SESSION_PASSED_KEY = 'two_factor_passed_at';

    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function __construct(
        private readonly TwoFactorService $twoFactor,
    ) {}

    /**
     * Langkah 1: tampilkan secret & QR untuk dipindai.
     */
    public function setup(Request $request): View
    {
        $user = $this->crewOrFail($request);

        if ($user->hasTwoFactorEnabled()) {
            return view('newsroom.two-factor.enabled', ['user' => $user]);
        }

        $secret = $request->session()->get(self::PENDING_SECRET_KEY);

        if (! is_string($secret) || $secret === '') {
            // Secret baru hanya dibangkitkan bila belum ada secret tertunda
            // di sesi. generateSecret() sengaja TIDAK dipanggil berulang agar
            // kode yang sudah dipindai ke authenticator tidak berubah.
            $secret = $this->twoFactor->generateSecret($user);
            $request->session()->put(self::PENDING_SECRET_KEY, $secret);
        }

        return view('newsroom.two-factor.setup', [
            'secret' => $secret,
            'provisioningUri' => $this->twoFactor->setupUri($user, $secret),
        ]);
    }

    /**
     * Langkah 2: verifikasi kode pertama, aktifkan 2FA, tampilkan recovery code.
     */
    public function confirm(TwoFactorConfirmRequest $request): RedirectResponse
    {
        $user = $this->crewOrFail($request);

        $secret = $request->session()->get(self::PENDING_SECRET_KEY);

        if (! is_string($secret) || $secret === '') {
            return redirect()
                ->route('newsroom.two-factor.setup')
                ->with('warning', 'Sesi pengaturan berakhir. Mulai ulang aktivasi 2FA.');
        }

        $recoveryCodes = $this->twoFactor->confirm($user, $request->string('code')->toString());

        if ($recoveryCodes === null) {
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi tidak valid. Pastikan jam perangkat Anda akurat.',
            ]);
        }

        $request->session()->forget(self::PENDING_SECRET_KEY);
        $request->session()->put(self::SESSION_PASSED_KEY, now()->getTimestamp());

        return redirect()
            ->route('newsroom.two-factor.recovery-codes')
            ->with('recovery_codes', $recoveryCodes)
            ->with('status', 'Verifikasi dua langkah berhasil diaktifkan.');
    }

    /**
     * Tampilkan recovery code (sekali, setelah aktivasi).
     */
    public function recoveryCodes(Request $request): View|RedirectResponse
    {
        $user = $this->crewOrFail($request);

        $codes = $request->session()->get('recovery_codes');

        if (! is_array($codes)) {
            return redirect()->route('newsroom.dashboard');
        }

        return view('newsroom.two-factor.recovery-codes', ['codes' => $codes]);
    }

    /**
     * Langkah 3: halaman tantangan setelah login dengan kredensial benar.
     */
    public function challenge(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user === null || ! $user->is_crew) {
            return redirect()->route('login');
        }

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('newsroom.two-factor.setup');
        }

        if ($this->hasPassedChallenge($request)) {
            return redirect()->route('newsroom.dashboard');
        }

        return view('newsroom.two-factor.challenge');
    }

    /**
     * Langkah 4: verifikasi kode TOTP atau recovery code.
     */
    public function verify(TwoFactorChallengeRequest $request): RedirectResponse
    {
        $user = $this->crewOrFail($request);
        $throttleKey = '2fa|'.$user->getKey().'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'code' => 'Terlalu banyak percobaan. Coba lagi dalam satu menit.',
            ]);
        }

        $code = $request->string('code')->toString();

        $valid = $this->twoFactor->verifyCode($user, $code)
            || $this->twoFactor->consumeRecoveryCode($user, $code);

        if (! $valid) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'code' => 'Kode tidak valid. Gunakan kode dari aplikasi authenticator atau recovery code.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_PASSED_KEY, now()->getTimestamp());
        $request->session()->put('crew_last_activity_at', now()->getTimestamp());

        return redirect()->intended(route('newsroom.dashboard'));
    }

    /**
     * Nonaktifkan 2FA (khusus akun kru yang sudah lolos tantangan).
     */
    public function disable(Request $request): RedirectResponse
    {
        $user = $this->crewOrFail($request);

        $this->twoFactor->disable($user);
        $request->session()->forget(self::SESSION_PASSED_KEY);

        return redirect()
            ->route('newsroom.two-factor.setup')
            ->with('warning', 'Verifikasi dua langkah dinonaktifkan. Aktifkan kembali untuk mengakses newsroom.');
    }

    /**
     * Apakah sesi ini sudah melewati tantangan 2FA dalam 2 jam terakhir.
     */
    public function hasPassedChallenge(Request $request): bool
    {
        $passedAt = $request->session()->get(self::SESSION_PASSED_KEY);

        if (! is_int($passedAt)) {
            return false;
        }

        return (now()->getTimestamp() - $passedAt) < 7200;
    }

    /**
     * Ambil pengguna kru atau batalkan dengan redirect.
     */
    private function crewOrFail(Request $request): User
    {
        $user = $request->user();

        abort_if($user === null || ! $user->is_crew, 403, 'Halaman ini hanya untuk kru redaksi.');

        return $user;
    }
}
