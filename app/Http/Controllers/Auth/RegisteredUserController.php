<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Pendaftaran akun pembaca (reader).
 */
final class RegisteredUserController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $throttleKey = 'register|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan pendaftaran. Coba beberapa saat lagi.',
            ]);
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->lower()->toString(),
            'avatar_path' => $avatarPath,
            'password' => $request->string('password')->toString(),
            'is_crew' => false,
            'is_active' => true,
        ]);

        $user->assignRole(RoleName::READER->value);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        $prev = url()->previous();
        $isAuthUrl = str_contains($prev, '/login') || str_contains($prev, '/register');
        $targetUrl = (! empty($prev) && ! $isAuthUrl) ? $prev : route('home');

        return redirect()
            ->intended($targetUrl)
            ->with('status', 'Selamat datang, ' . $user->name . '! Akun pembaca Anda sudah aktif untuk menyukai dan mengomentari berita.');
    }
}
