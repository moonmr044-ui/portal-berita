<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $throttleKey = $request->throttleKey();

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Alamat email atau kata sandi tidak cocok.',
            ]);
        }

        $user = Auth::user();

        if ($user instanceof User && ! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akun ini dinonaktifkan. Hubungi administrator redaksi.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        if ($user instanceof User) {
            $user->forceFill(['last_login_at' => now()])->save();
        }

        if ($user instanceof User && $user->is_crew) {
            $request->session()->put('crew_last_activity_at', now()->getTimestamp());
        }

        return $this->redirectAfterLogin($user);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Anda telah keluar.');
    }

    private function redirectAfterLogin(?User $user): RedirectResponse
    {
        if ($user instanceof User && $user->is_crew) {
            if ($user->hasAnyRole(['super-admin', 'pemred', 'desk-editor'])) {
                return redirect()->route('otp.challenge');
            }

            return redirect()->intended(route('newsroom.dashboard'));
        }

        $prev = url()->previous();
        $isAuthUrl = str_contains($prev, '/login') || str_contains($prev, '/register');
        $targetUrl = (! empty($prev) && ! $isAuthUrl) ? $prev : route('home');

        return redirect()
            ->intended($targetUrl)
            ->with('status', 'Berhasil masuk sebagai ' . ($user?->name ?? 'Pembaca') . '.');
    }
}
