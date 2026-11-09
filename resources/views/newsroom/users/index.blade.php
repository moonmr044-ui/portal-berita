{{-- File: resources/views/newsroom/users/index.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Manajemen Kru & Pembaca')

@section('content')

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Kru &amp; Pengguna Publik</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola hak akses kru redaksi serta moderasi profil dan foto akun pembaca publik.
            </p>
        </div>

        <div>
            <a href="{{ route('newsroom.users.create') }}"
               class="rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Tambah Kru Baru
            </a>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Profil &amp; Nama Pengguna</th>
                        <th class="px-4 py-3.5">Alamat Email</th>
                        <th class="px-4 py-3.5">Peran (Role)</th>
                        <th class="px-4 py-3.5">Keamanan Login</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if ($user->avatarUrl())
                                        <img src="{{ $user->avatarUrl() }}"
                                             alt="{{ $user->name }}"
                                             class="h-9 w-9 rounded-full object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ $user->initials() }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900">
                                            {{ $user->name }}
                                            @if ($user->id === auth()->id())
                                                <span class="ml-1 text-[10px] text-rose-600 font-semibold">(Anda)</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $user->avatarUrl() ? 'Foto Profil Terpasang' : 'Inisial Default' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-mono text-slate-600">
                                {{ $user->email }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @foreach ($user->roles as $role)
                                    <span class="rounded {{ $role->name === 'reader' ? 'bg-slate-100 border-slate-300 text-slate-700' : 'bg-rose-50 border-rose-200 text-rose-700' }} border px-2 py-0.5 text-[10px] font-bold uppercase">
                                        {{ $role->name === 'reader' ? 'Pembaca Publik' : $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($user->hasAnyRole(['super-admin', 'pemred', 'desk-editor']))
                                    <span class="text-emerald-700 font-semibold flex items-center gap-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Wajib OTP Email
                                    </span>
                                @else
                                    <span class="text-slate-400">Login Langsung (Tanpa OTP)</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-3">
                                <a href="{{ route('newsroom.users.edit', $user->id) }}" class="font-bold text-slate-700 hover:text-rose-600">
                                    Edit / Moderasi Foto
                                </a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('newsroom.users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Hapus akun ini dari sistem?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-bold text-rose-600 hover:underline cursor-pointer">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    </div>

@endsection
