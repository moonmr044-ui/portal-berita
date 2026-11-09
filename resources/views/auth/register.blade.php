@extends('layouts.public')

{{-- File: resources/views/auth/register.blade.php --}}
@section('title', 'Daftar Akun Pembaca - Ruang Data')

@section('content')
    {{-- Latar belakang halaman saat URL /register diakses langsung (Modal Popup otomatis terbuka di atasnya) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center select-none">
        <span class="inline-block px-3 py-1 bg-red-50 text-red-600 text-xs font-bold uppercase tracking-widest rounded-full mb-3">
            REGISTRASI PEMBACA RUANG DATA
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl font-bold text-slate-900">
            Daftar Akun Pembaca
        </h1>
        <p class="mt-2 text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
            Silakan lengkapi formulir pada jendela popup untuk membuat akun pembaca baru.
        </p>
        <div class="mt-6 flex items-center justify-center gap-3">
            <button type="button"
                    onclick="openAuthModal('register')"
                    class="rounded-lg bg-red-600 hover:bg-red-700 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition cursor-pointer">
                Buka Popup Pendaftaran
            </button>
            <a href="{{ route('home') }}"
               class="rounded-lg border border-slate-300 bg-white hover:bg-slate-50 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-slate-700 transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>
@endsection
