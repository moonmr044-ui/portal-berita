<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Verifikasi email pembaca.
 *
 * Komentar, reaksi, dan bookmark hanya tersedia setelah email terverifikasi.
 */
final class EmailVerificationController extends Controller
{
    /**
     * Halaman pemberitahuan "cek email Anda".
     */
    public function notice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user?->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('auth.verify-email');
    }

    /**
     * Tautan verifikasi dari email.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home')->with('status', 'Email Anda sudah terverifikasi.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('home')->with('status', 'Email berhasil diverifikasi.');
    }

    /**
     * Kirim ulang tautan verifikasi.
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || $user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'Tautan verifikasi baru telah dikirim ke email Anda.');
    }
}
