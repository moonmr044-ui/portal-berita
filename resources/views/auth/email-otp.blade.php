{{-- File: resources/views/auth/email-otp.blade.php --}}
@extends('layouts.public')

@section('title', 'Verifikasi Keamanan Email - Ruang Data')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-16 text-center">
    <p class="text-xs text-slate-400">Menunggu verifikasi keamanan dua langkah...</p>
</div>

{{-- POPUP MODAL VERIFIKASI OTP KRU REDAKSI --}}
<div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-black/75 backdrop-blur-xs overflow-y-auto">
    <div class="relative w-full max-w-[430px] rounded-2xl bg-white shadow-[0_28px_80px_rgba(0,0,0,0.65)] overflow-hidden border border-white/15 my-auto">

        {{-- Dark Editorial Header --}}
        <div class="relative bg-[#111317] text-white px-6 pt-6 pb-5 text-center border-b border-white/10">
            <div class="absolute inset-x-0 top-0 h-1 bg-[#e50914]"></div>

            <div class="mx-auto mb-2.5 flex h-11 w-11 items-center justify-center rounded-full bg-[#e50914]/20 border border-[#e50914]/40 text-red-400">
                <span class="material-symbols-outlined text-[22px]">shield_lock</span>
            </div>

            <h1 class="font-serif text-xl font-bold tracking-wider text-white uppercase">
                Verifikasi Kode OTP Email
            </h1>
            <p class="text-[11px] text-white/65 mt-1">
                Keamanan Dapur Redaksi &bull; Ruang Data
            </p>
        </div>

        <div class="p-6">
            <div class="text-center mb-5">
                <p class="text-xs text-slate-600 leading-relaxed">
                    Kami telah mengirimkan 6 digit kode keamanan ke alamat email:
                    <br><strong class="text-slate-900 font-bold">{{ $email }}</strong>
                </p>
                <p class="mt-1.5 text-[11px] text-red-600 font-medium">
                    (Cek inbox lokal di <a href="http://localhost:8025" target="_blank" class="underline font-bold">http://localhost:8025 - Mailpit</a>)
                </p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-xs text-emerald-800 border border-emerald-200 font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="mb-4 rounded-lg bg-rose-50 p-3 text-xs text-rose-800 border border-rose-200 font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('otp.verify') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="otp" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-2 text-center">
                        Masukkan 6 Digit Angka OTP
                    </label>
                    <input id="otp"
                           name="otp"
                           type="text"
                           inputmode="numeric"
                           pattern="[0-9]*"
                           maxlength="6"
                           required
                           autofocus
                           placeholder="123456"
                           class="w-full text-center tracking-[0.5em] font-mono text-2xl font-black rounded-xl border border-slate-300 bg-slate-50 py-3 text-slate-900 focus:border-[#e50914] focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600/15 transition">
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-[#e50914] hover:bg-red-700 py-3 text-xs font-extrabold uppercase tracking-widest text-white shadow-md transition cursor-pointer">
                    Verifikasi &amp; Masuk ke Newsroom
                </button>
            </form>

            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <form method="POST" action="{{ route('otp.resend') }}">
                    @csrf
                    <button type="submit" class="text-[#e50914] font-bold hover:underline cursor-pointer">
                        Kirim Ulang Kode
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-slate-800 font-semibold cursor-pointer">
                        Ganti Akun Lain
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
