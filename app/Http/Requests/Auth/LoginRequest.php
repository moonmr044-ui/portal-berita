<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Login untuk seluruh pengguna (kru maupun pembaca).
 *
 * Pembatasan 5 percobaan/menit per kombinasi email+IP ditegakkan lewat
 * rate limiter bernama `login` (lihat AppServiceProvider).
 */
final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Kunci throttle rate limiter: email + IP.
     */
    public function throttleKey(): string
    {
        return mb_strtolower((string) $this->input('email')).'|'.$this->ip();
    }

    /**
     * Apakah email ini milik akun kru (menentukan alur 2FA).
     */
    public function targetsCrew(): bool
    {
        return $this->user() !== null && $this->user()->is_crew;
    }

    /**
     * Daftar peran yang boleh masuk panel newsroom.
     *
     * @return list<string>
     */
    public static function crewRoles(): array
    {
        return [
            RoleName::SUPER_ADMIN->value,
            RoleName::PEMRED->value,
            RoleName::DESK_EDITOR->value,
            RoleName::REPORTER->value,
        ];
    }

    /**
     * Rule tambahan bila nanti diperlukan (mis. captcha setelah N gagal).
     *
     * @return array<string, mixed>
     */
    public static function crewRoleRule(): array
    {
        return [Rule::in(self::crewRoles())];
    }
}
