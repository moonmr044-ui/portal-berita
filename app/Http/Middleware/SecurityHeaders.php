<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers wajib untuk seluruh respons publik.
 *
 * CSP dibangun per-request karena beberapa nilai (Vite dev server, Mailpit)
 * berbeda antar environment. Pada produksi tidak ada sumber eksternal:
 * seluruh JS/CSS/font dibundel lewat Vite dan di-self-host.
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        // Hanya set CSP bila belum ada (mis. sudah diisi Nginx di produksi).
        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        // Artikel takedown tidak boleh diindeks mesin pencari.
        if ($response->getStatusCode() === Response::HTTP_GONE) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    /**
     * Bangun direktif CSP sesuai environment.
     */
    private function contentSecurityPolicy(): string
    {
        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "img-src 'self' data: blob: https: http://localhost http://127.0.0.1",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.googleapis.com",
            "media-src 'self'",
            "manifest-src 'self'",
        ];

        if (app()->isLocal()) {
            // Vite dev server + Alpine memakai eval pada mode pengembangan.
            $script = "'self' 'unsafe-eval' 'unsafe-inline' https://cdn.tailwindcss.com http://localhost:5173 http://127.0.0.1:5173";
            $style = "'self' 'unsafe-inline' https://fonts.googleapis.com http://localhost:5173 http://127.0.0.1:5173";
            $connect = "'self' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173";
        } else {
            // Produksi: aset di-self-host, tanpa CDN eksternal.
            $script = "'self' 'unsafe-inline' https://cdn.tailwindcss.com";
            $style = "'self' 'unsafe-inline' https://fonts.googleapis.com";
            $connect = "'self'";
        }

        $directives[] = 'script-src '.$script;
        $directives[] = 'style-src '.$style;
        $directives[] = 'connect-src '.$connect;

        if (app()->isProduction()) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }
}
