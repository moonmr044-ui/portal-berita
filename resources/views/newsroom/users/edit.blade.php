{{-- File: resources/views/newsroom/users/edit.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Sunting Pengguna: ' . $user->name)

@section('content')

    <div class="mb-6 max-w-xl mx-auto">
        <a href="{{ route('newsroom.users.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition flex items-center gap-1 mb-2">
            &larr; Kembali ke Manajemen Kru &amp; Pengguna
        </a>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sunting Data Akun Kru &amp; Pengguna</h1>
        <p class="text-xs text-slate-500">
            Perbarui identitas, foto profil, wewenang peran, atau status aktif untuk akun <strong>{{ $user->name }}</strong>.
        </p>
    </div>

    <div class="max-w-xl mx-auto rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        @if (isset($errors) && $errors->any())
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('newsroom.users.update', $user->id) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Moderasi & Ganti Foto Profil --}}
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <label class="block text-xs font-bold uppercase text-slate-700 mb-2.5">Foto Profil Pengguna</label>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    @if ($user->avatarUrl())
                        <img src="{{ $user->avatarUrl() }}"
                             alt="{{ $user->name }}"
                             class="h-16 w-16 rounded-full object-cover border-2 border-white shadow-sm shrink-0">
                    @else
                        <div class="h-16 w-16 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xl shrink-0">
                            {{ $user->initials() }}
                        </div>
                    @endif

                    <div class="flex-1 space-y-2">
                        <input type="file"
                               name="avatar"
                               accept="image/jpeg,image/png,image/webp"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700">
                        <p class="text-[11px] text-slate-500">
                            Unggah foto baru (JPG/PNG/WebP, maks. 4 MB) bila ingin mengganti foto pengguna ini.
                        </p>

                        @if ($user->avatarUrl())
                            <label class="inline-flex items-center gap-2 pt-1 text-xs font-bold text-rose-600 cursor-pointer">
                                <input type="checkbox" name="remove_avatar" value="1" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                <span>Hapus / Reset Foto Profil Saat Ini (Gunakan Inisial)</span>
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <label for="name" class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase text-slate-700 mb-1">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase text-slate-700 mb-1">Kata Sandi Baru (Kosongkan bila tidak diubah)</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••"
                       class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
            </div>

            <div>
                <label for="role" class="block text-xs font-bold uppercase text-slate-700 mb-1">Peran (Role Akses)</label>
                <select id="role" name="role" required class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
                    @php $currentRole = $user->getRoleNames()->first(); @endphp
                    <option value="super-admin" {{ old('role', $currentRole) === 'super-admin' ? 'selected' : '' }}>Super Admin (Wewenang Penuh Redaksi - Wajib OTP)</option>
                    <option value="reader" {{ old('role', $currentRole) === 'reader' ? 'selected' : '' }}>Pembaca Terdaftar (User Publik - Suka & Komentar)</option>
                </select>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                <label for="is_active" class="text-xs font-bold text-slate-700">Akun Aktif (Jika dicabut, pengguna tidak dapat login atau berkomentar)</label>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('newsroom.users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700 shadow-xs cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

@endsection
