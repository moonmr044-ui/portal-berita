@extends('layouts.newsroom')

{{-- File: resources/views/newsroom/two-factor/enabled.blade.php --}}
@section('title', 'Verifikasi Dua Langkah Aktif')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-2 text-2xl font-bold tracking-tight">Verifikasi Dua Langkah Aktif</h1>

        <p class="mb-6 text-sm text-slate-600">
            Akun Anda sudah dilindungi TOTP sejak
            {{ auth()->user()?->two_factor_confirmed_at?->toWib()->translatedFormat('d F Y, H:i') }} WIB.
        </p>

        <div class="rounded border border-slate-200 bg-white p-4 text-sm text-slate-700">
            <p class="mb-3">
                Menonaktifkan verifikasi dua langkah akan langsung menghentikan akses Anda ke newsroom
                sampai 2FA diaktifkan kembali.
            </p>

            <form method="POST" action="{{ route('newsroom.two-factor.disable') }}">
                @csrf
                <button type="submit"
                        class="rounded border border-rose-300 bg-rose-50 px-4 py-2 font-semibold text-rose-800 hover:bg-rose-100">
                    Nonaktifkan verifikasi dua langkah
                </button>
            </form>
        </div>
    </div>
@endsection
