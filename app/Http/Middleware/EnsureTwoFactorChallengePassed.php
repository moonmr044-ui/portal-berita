<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTwoFactorChallengePassed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user === null || ! $user->is_crew) {
            return $next($request);
        }

        // Role sensitif redaksi wajib verifikasi OTP Email
        if ($user->hasAnyRole(['super-admin', 'pemred', 'desk-editor'])) {
            if ($request->session()->get('email_otp_verified') !== true) {
                return redirect()->route('otp.challenge');
            }
        }

        // Reporter dan role non-sensitif bebas OTP
        return $next($request);
    }
}
