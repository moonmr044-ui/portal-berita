<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginOtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EmailOtpController extends Controller
{
    public function challenge(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if ($user === null) {
            return redirect()->route('login');
        }

        // Hanya role sensitif yang butuh OTP Email
        if (! $user->hasAnyRole(['super-admin', 'pemred', 'desk-editor'])) {
            return redirect()->route('newsroom.dashboard');
        }

        // Jika sudah terverifikasi di sesi ini, langsung masuk
        if ($request->session()->get('email_otp_verified') === true) {
            return redirect()->route('newsroom.dashboard');
        }

        // Cek apakah kode OTP sudah pernah dibuat dan masih aktif
        $expiresAt = $request->session()->get('email_otp_expires_at');
        if (! $expiresAt || now()->getTimestamp() > $expiresAt) {
            $this->generateAndSendOtp($request);
        }

        return view('auth.email-otp', [
            'email' => $user->email,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Masukkan 6 digit kode OTP dari email.',
            'otp.size' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        $storedOtp = $request->session()->get('email_otp');
        $expiresAt = $request->session()->get('email_otp_expires_at');

        if (! $storedOtp || ! $expiresAt || now()->getTimestamp() > $expiresAt) {
            return back()->withErrors([
                'otp' => 'Kode OTP sudah kedaluwarsa (lewat dari 5 menit). Silakan klik tombol "Kirim Ulang".',
            ]);
        }

        if (trim((string) $request->input('otp')) !== (string) $storedOtp) {
            return back()->withErrors([
                'otp' => 'Kode OTP yang Anda masukkan salah. Periksa kembali email Anda.',
            ]);
        }

        // Berhasil verifikasi
        $request->session()->put('email_otp_verified', true);
        $request->session()->forget(['email_otp', 'email_otp_expires_at']);

        return redirect()->intended(route('newsroom.dashboard'))
            ->with('status', 'Verifikasi keamanan email berhasil. Selamat datang di Newsroom!');
    }

    public function resend(Request $request): RedirectResponse
    {
        $this->generateAndSendOtp($request);

        return back()->with('status', 'Kode OTP baru telah berhasil dikirimkan ke email Anda.');
    }

    private function generateAndSendOtp(Request $request): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $otpCode = sprintf('%06d', random_int(100000, 999999));
        $request->session()->put('email_otp', $otpCode);
        $request->session()->put('email_otp_expires_at', now()->addMinutes(5)->getTimestamp());

        try {
            Mail::to($user->email)->send(new LoginOtpMail($otpCode, $user->name));
        } catch (\Throwable $e) {
            // Jika ada error mail server, jangan sampai sistem crash
            report($e);
        }
    }
}
