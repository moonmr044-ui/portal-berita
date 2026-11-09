@extends('layouts.newsroom')

{{-- File: resources/views/newsroom/two-factor/setup.blade.php --}}
@section('title', 'Aktivasi Verifikasi Dua Langkah')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-2 text-2xl font-bold tracking-tight">Aktivasi Verifikasi Dua Langkah</h1>
        <p class="mb-6 text-sm text-slate-600">
            Akun kru wajib mengaktifkan verifikasi dua langkah (TOTP) sebelum dapat mengakses newsroom.
        </p>

        <div class="mb-6 rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
            Secret di bawah hanya ditampilkan sekali. Simpan di aplikasi authenticator Anda
            (Google Authenticator, Authy, 1Password, dan sejenisnya).
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-4 rounded border border-rose-200 bg-rose-50 p-3 text-sm text-rose-900">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 rounded border border-slate-200 bg-white p-4">
            <h2 class="mb-2 text-sm font-semibold text-slate-700">Secret Anda</h2>
            <code class="block break-all rounded bg-slate-50 px-3 py-2 font-mono text-sm text-slate-900">
                {{ $secret }}
            </code>

            <h2 class="mb-1 mt-4 text-sm font-semibold text-slate-700">URI otpauth (untuk QR code)</h2>
            <code class="block break-all text-xs text-slate-600">{{ $provisioningUri }}</code>
        </div>

        <form method="POST" action="{{ route('newsroom.two-factor.confirm') }}" class="space-y-4">
            @csrf

            <div>
                <label for="code" class="mb-1 block text-sm font-medium">
                    Masukkan 6 angka dari aplikasi authenticator
                </label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                       pattern="[0-9]{6}" maxlength="6" required autofocus
                       class="w-40 rounded border border-slate-300 px-3 py-2 text-center font-mono text-lg tracking-widest focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
            </div>

            <button type="submit"
                    class="rounded bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Aktifkan verifikasi dua langkah
            </button>
        </form>
    </div>
@endsection
