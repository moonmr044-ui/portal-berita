<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi akses hanya untuk akun kru (is_crew = true).
 *
 * Akun pembaca yang mencoba membuka panel newsroom mendapat 403,
 * bukan pengalihan, agar keberadaan panel tidak membocorkan informasi
 * kepada pengguna yang tidak berhak.
 */
final class EnsureUserIsCrew
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        abort_if($user === null, 403, 'Akses ditolak.');
        abort_unless($user->is_crew, 403, 'Panel ini khusus untuk kru redaksi.');
        abort_unless($user->is_active, 403, 'Akun Anda dinonaktifkan.');

        return $next($request);
    }
}
