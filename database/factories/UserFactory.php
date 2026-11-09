<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Secret TOTP valid (base32, 16 karakter) untuk akun kru hasil factory.
     */
    public const FACTORY_TOTP_SECRET = 'JBSWY3DPEHPK3PXP';

    protected static ?string $password;

    /**
     * Recovery code contoh untuk akun kru hasil factory.
     *
     * Jumlahnya disamakan dengan TwoFactorService::RECOVERY_CODE_COUNT agar
     * test yang memeriksa sisa kode setelah pemakaian tetap konsisten.
     *
     * @return list<string>
     */
    public static function factoryRecoveryCodes(): array
    {
        return [
            'aaaa1111bbbb2222cccc',
            'dddd3333eeee4444ffff',
            'gggg5555hhhh6666iiii',
            'jjjj7777kkkk8888llll',
            'mmmm9999nnnn0000oooo',
            'pppp1111qqqq2222rrrr',
            'ssss3333tttt4444uuuu',
            'vvvv5555wwww6666xxxx',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('RahasiaPortal2026!'),
            'remember_token' => Str::random(10),
            'is_crew' => false,
            'is_active' => true,
        ];
    }

    /**
     * Email belum diverifikasi (kondisi default pembaca baru).
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Akun pembaca terverifikasi.
     */
    public function reader(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_crew' => false,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Akun pembaca yang belum memverifikasi email.
     *
     * Menggabungkan is_crew=false dengan email_verified_at=null, karena
     * memanggil unverified() lalu reader() akan menimpa kembali nilainya.
     */
    public function unverifiedReader(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_crew' => false,
            'email_verified_at' => null,
        ]);
    }

    /**
     * Akun kru. 2FA wajib, jadi factory menyertakan secret terkonfirmasi
     * agar test dapat memfokuskan diri pada perilaku lain.
     *
     * Secret disimpan TERENKRIPSI karena TwoFactorService hanya membaca
     * secret lewat Crypt::decryptString.
     */
    public function crew(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_crew' => true,
            'email_verified_at' => now(),
            'two_factor_secret' => Crypt::encryptString(self::FACTORY_TOTP_SECRET),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode(self::factoryRecoveryCodes())),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Kru tanpa 2FA (untuk menguji penolakan akses newsroom).
     */
    public function crewWithoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_crew' => true,
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    /**
     * Akun nonaktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Buat akun kru sekaligus tetapkan perannya.
     */
    public function withRole(RoleName $role): static
    {
        return $this->afterCreating(static function (User $user) use ($role): void {
            $user->assignRole($role->value);
        });
    }
}
