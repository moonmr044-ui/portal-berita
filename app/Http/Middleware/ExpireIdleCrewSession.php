<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sesi kru otomatis kedaluwarsa setelah 2 jam idle.
 *
 * Hanya berlaku untuk akun kru (is_crew = true); sesi pembaca mengikuti
 * SESSION_LIFETIME biasa. Aktivitas apa pun memperbarui penanda waktu.
 */
final class ExpireIdleCrewSession
{
    /**
     * Batas idle untuk akun kru, dalam menit.
     */
    public const IDLE_TIMEOUT_MINUTES = 120;

    /**
     * Kunci session untuk penanda aktivitas terakhir.
     */
    private const SESSION_KEY = 'crew_last_activity_at';

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user === null || ! $user->is_crew) {
            return $next($request);
        }

        $now = now()->getTimestamp();
        $last = $request->session()->get(self::SESSION_KEY);

        if (is_int($last) && ($now - $last) > (self::IDLE_TIMEOUT_MINUTES * 60)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('warning', 'Sesi Anda berakhir karena tidak ada aktivitas selama 2 jam. Silakan masuk kembali.');
        }

        $request->session()->put(self::SESSION_KEY, $now);

        return $next($request);
    }
}
