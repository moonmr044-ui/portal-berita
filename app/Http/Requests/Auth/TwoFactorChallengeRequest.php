<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Tantangan 2FA saat login.
 *
 * Menerima kode TOTP 6 digit ATAU recovery code (format heksadesimal
 * dengan pemisah tanda hubung), sehingga panjangnya tidak dipatok 6.
 */
final class TwoFactorChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->is_crew;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'min:6', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode verifikasi wajib diisi.',
            'code.min' => 'Kode verifikasi minimal 6 karakter.',
        ];
    }

    /**
     * Normalisasi: buang spasi agar recovery code mudah disalin-tempel.
     */
    protected function prepareForValidation(): void
    {
        $code = $this->input('code');

        if (is_string($code)) {
            $this->merge(['code' => trim($code)]);
        }
    }
}
