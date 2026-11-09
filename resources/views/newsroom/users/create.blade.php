{{-- File: resources/views/newsroom/users/create.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Tambah Kru Baru')

@section('content')

    <div class="mb-6 max-w-xl mx-auto">
        <a href="{{ route('newsroom.users.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition flex items-center gap-1 mb-2">
            &larr; Kembali ke Manajemen Kru
        </a>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tambah Kru Redaksi Baru</h1>
        <p class="text-xs text-slate-500">
            Daftarkan wartawan, editor, atau administrator baru ke sistem portal berita.
        </p>
    </div>

    <div class="max-w-xl mx-auto rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        @if (isset($errors) && $errors->any())
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('newsroom.users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       placeholder="Contoh: Rian Pratama"
                       class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase text-slate-700 mb-1">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       placeholder="rian@portalberita.test"
                       class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase text-slate-700 mb-1">Kata Sandi (Minimal 8 Karakter)</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••"
                       class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
            </div>

            <div>
                <label for="role" class="block text-xs font-bold uppercase text-slate-700 mb-1">Peran (Role Akses)</label>
                <select id="role" name="role" required class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
                    <option value="super-admin" {{ old('role') === 'super-admin' ? 'selected' : '' }}>Super Admin (Wewenang Penuh Redaksi - Wajib OTP)</option>
                    <option value="reader" {{ old('role', 'reader') === 'reader' ? 'selected' : '' }}>Pembaca Terdaftar (User Publik - Suka & Komentar)</option>
                </select>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('newsroom.users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700 shadow-xs">
                    Simpan Akun Kru
                </button>
            </div>
        </form>
    </div>

@endsection
