<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;

/**
 * Layanan TOTP 2FA untuk akun kru.
 *
 * - Secret dan recovery code disimpan terenkripsi (Crypt) di database.
 * - Secret hanya dianggap aktif setelah pengguna memverifikasi satu kode
 *   (`two_factor_confirmed_at` terisi).
 * - Recovery code bersifat sekali pakai: setiap pemakaian menghapusnya
 *   dari daftar.
 */
final class TwoFactorService
{
    /**
     * Jumlah recovery code yang dibangkitkan saat enrolment.
     */
    public const RECOVERY_CODE_COUNT = 8;

    public function __construct(
        private readonly Google2FA $google2fa = new Google2FA,
    ) {}

    /**
     * Bangkitkan secret baru (belum dikonfirmasi) untuk pengguna.
     */
    public function generateSecret(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return $secret;
    }

    /**
     * Secret mentah milik pengguna (hasil dekripsi).
     */
    public function secretFor(User $user): ?string
    {
        if ($user->two_factor_secret === null) {
            return null;
        }

        try {
            return Crypt::decryptString($user->two_factor_secret);
        } catch (DecryptException) {
            // Secret rusak/tidak dapat didekripsi (mis. APP_KEY berubah).
            return null;
        }
    }

    /**
     * URL otpauth untuk dipindai aplikasi authenticator (QR code).
     */
    public function provisioningUri(User $user): ?string
    {
        $secret = $this->secretFor($user);

        if ($secret === null || ! $user->hasTwoFactorEnabled()) {
            return null;
        }

        return $this->google2fa->getQRCodeUrl(
            (string) config('app.name'),
            $user->email,
            $secret,
        );
    }

    /**
     * URL otpauth untuk secret yang belum dikonfirmasi (halaman setup).
     */
    public function setupUri(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            (string) config('app.name'),
            $user->email,
            $secret,
        );
    }

    /**
     * Verifikasi kode 6 digit dari aplikasi authenticator.
     *
     * Memberi toleransi jendela waktu (window) 2 agar tetap valid saat
     * terjadi selisih jam ringan antara server dan perangkat pengguna.
     */
    public function verifyCode(User $user, string $code): bool
    {
        $secret = $this->secretFor($user);

        if ($secret === null) {
            return false;
        }

        try {
            return $this->google2fa->verifyKey($secret, $code, window: 2);
        } catch (InvalidCharactersException|SecretKeyTooShortException|IncompatibleWithGoogleAuthenticatorException) {
            return false;
        }
    }

    /**
     * Konfirmasi aktifasi 2FA: verifikasi kode lalu bangkitkan recovery code.
     *
     * @return list<string>|null Recovery code dalam bentuk terang, atau null bila kode salah.
     */
    public function confirm(User $user, string $code): ?array
    {
        if (! $this->verifyCode($user, $code)) {
            return null;
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $recoveryCodes;
    }

    /**
     * Nonaktifkan 2FA dan bersihkan seluruh datanya.
     */
    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    /**
     * Recovery code dalam bentuk terang, atau array kosong bila tidak ada.
     *
     * @return list<string>
     */
    public function recoveryCodes(User $user): array
    {
        $encrypted = $user->two_factor_recovery_codes;

        if ($encrypted === null) {
            return [];
        }

        try {
            $decoded = json_decode(Crypt::decryptString($encrypted), true);
        } catch (DecryptException) {
            return [];
        }

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    /**
     * Konsumsi satu recovery code. Kode yang cocok dihapus permanen
     * sehingga tidak dapat dipakai dua kali.
     */
    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $this->recoveryCodes($user);
        $normalized = mb_strtolower(trim($code));

        if ($normalized === '') {
            return false;
        }

        $remaining = [];
        $matched = false;

        foreach ($codes as $candidate) {
            // Perbandingan konstan-waktu untuk menghindari timing attack.
            if (! $matched && hash_equals(mb_strtolower($candidate), $normalized)) {
                $matched = true;

                continue;
            }

            $remaining[] = $candidate;
        }

        if (! $matched) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(
                json_encode(array_values($remaining))
            ),
        ])->save();

        return true;
    }

    /**
     * Bangkitkan recovery code acak yang mudah dibaca manusia.
     *
     * @return list<string>
     */
    private function generateRecoveryCodes(): array
    {
        $codes = [];

        for ($i = 0; $i < self::RECOVERY_CODE_COUNT; $i++) {
            $codes[] = sprintf(
                '%s-%s',
                strtolower(bin2hex(random_bytes(5))),
                strtolower(bin2hex(random_bytes(5))),
            );
        }

        return $codes;
    }
}
