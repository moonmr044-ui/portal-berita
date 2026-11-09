@extends('layouts.public')

{{-- File: resources/views/auth/login.blade.php --}}
@section('title', 'Masuk - Ruang Data')

@section('content')
    {{-- Latar belakang halaman saat URL /login diakses langsung (Modal Popup otomatis terbuka di atasnya) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center select-none">
        <span class="inline-block px-3 py-1 bg-red-50 text-red-600 text-xs font-bold uppercase tracking-widest rounded-full mb-3">
            PORTAL OTENTIKASI RUANG DATA
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl font-bold text-slate-900">
            Masuk ke Akun Anda
        </h1>
        <p class="mt-2 text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
            Silakan gunakan jendela popup yang tampil di layar untuk masuk atau mendaftar akun pembaca.
        </p>
        <div class="mt-6 flex items-center justify-center gap-3">
            <button type="button"
                    onclick="openAuthModal('login')"
                    class="rounded-lg bg-black hover:bg-red-600 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition cursor-pointer">
                Buka Popup Masuk
            </button>
            <a href="{{ route('home') }}"
               class="rounded-lg border border-slate-300 bg-white hover:bg-slate-50 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-slate-700 transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>
@endsection
