{{-- File: resources/views/newsroom/dashboard.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Dasbor Redaksi')

@section('content')

    {{-- Header Sambutan & Action --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Studio Newsroom Redaksi</h1>
            <p class="text-xs text-slate-500 mt-1">
                Selamat bertugas, <strong class="text-slate-800">{{ $user->name }}</strong>. Pantau alur naskah dan produktivitas redaksi hari ini.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('newsroom.posts.create') }}"
               class="rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tulis Berita Baru
            </a>
            @if ($user->hasAnyRole(['super-admin', 'pemred']))
                <a href="{{ route('newsroom.curation.index') }}"
                   class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                    Kurasi Headline
                </a>
            @endif
        </div>
    </div>

    {{-- 1. Kartu Metrik Ringkas Berlogo & Berwarna --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 mb-8">
        {{-- Card 1: Total Artikel --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Total Artikel</span>
                <span class="material-symbols-outlined text-[20px] text-blue-600 select-none">article</span>
            </div>
            <div class="mt-2 text-2xl font-black text-blue-800">{{ number_format($stats['total_posts']) }}</div>
            <span class="text-[10px] text-blue-600 mt-1">Di seluruh rubrik</span>
        </div>

        {{-- Card 2: Terbit ke Publik --}}
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Terbit ke Publik</span>
                <span class="material-symbols-outlined text-[20px] text-emerald-600 select-none">check_circle</span>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-800">{{ number_format($stats['published']) }}</div>
            <span class="text-[10px] text-emerald-600 mt-1">Status Live</span>
        </div>

        {{-- Card 3: Artikel Diturunkan --}}
        <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Artikel Diturunkan</span>
                <span class="material-symbols-outlined text-[20px] text-amber-600 select-none">unpublished</span>
            </div>
            <div class="mt-2 text-2xl font-black text-amber-800">{{ number_format($stats['takedown']) }}</div>
            <span class="text-[10px] text-amber-600 mt-1">Takedown Redaksi</span>
        </div>

        {{-- Card 4: Total Pembaca --}}
        <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Total Pembaca</span>
                <span class="material-symbols-outlined text-[20px] text-rose-600 select-none">visibility</span>
            </div>
            <div class="mt-2 text-2xl font-black text-rose-800">{{ number_format($stats['total_views']) }}</div>
            <span class="text-[10px] text-rose-600 mt-1">Akumulasi views</span>
        </div>

        {{-- Card 5: Interaksi Pembaca (Cyan / Teal Modern Khas Studio Ruang Data) --}}
        <div class="col-span-2 sm:col-span-1 rounded-xl border border-cyan-200 bg-cyan-50/50 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-800">Interaksi Pembaca</span>
                <span class="material-symbols-outlined text-[20px] text-cyan-600 select-none">forum</span>
            </div>
            <div class="mt-2 grid grid-cols-2 gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-rose-500 select-none">favorite</span>
                    <div>
                        <div class="text-[10px] font-bold text-cyan-800/80 uppercase leading-none">Suka</div>
                        <div class="text-lg font-black text-cyan-950 mt-0.5 leading-none">{{ number_format($stats['total_likes']) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 pl-2 border-l border-cyan-200">
                    <span class="material-symbols-outlined text-[16px] text-cyan-600 select-none">chat_bubble</span>
                    <div>
                        <div class="text-[10px] font-bold text-cyan-800/80 uppercase leading-none">Komen</div>
                        <div class="text-lg font-black text-cyan-950 mt-0.5 leading-none">{{ number_format($stats['total_comments']) }}</div>
                    </div>
                </div>
            </div>
            <span class="text-[10px] text-cyan-700 mt-1">Akun Terdaftar</span>
        </div>
    </div>

    {{-- 2. Daftar Aktivitas Naskah Terbaru --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <span class="h-3.5 w-1 bg-rose-600 inline-block"></span>
                    Naskah Terbaru Redaksi
                </h2>
                <a href="{{ route('newsroom.posts.index') }}" class="text-xs font-bold text-rose-600 hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Judul Berita</th>
                            <th class="px-4 py-3">Rubrik</th>
                            <th class="px-4 py-3">Penulis</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentPosts as $post)
                            <tr class="hover:bg-slate-50/75 transition">
                                <td class="px-6 py-3.5">
                                    <div class="font-bold text-slate-900 line-clamp-1 max-w-xs sm:max-w-md">
                                        {{ $post->title }}
                                    </div>
                                    <span class="text-[10px] text-slate-400">
                                        {{ $post->created_at?->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase badge-category"
                                          style="--badge-color: {{ $post->category?->safeBadgeColor() }}">
                                        {{ $post->category?->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-slate-600">
                                    {{ $post->authorDisplayName() }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase ring-1 {{ $post->status->badgeClass() }}">
                                        {{ $post->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('newsroom.posts.edit', $post->id) }}"
                                       class="font-bold text-slate-700 hover:text-rose-600 underline">
                                        Kelola
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                    Belum ada naskah berita di dapur redaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sidebar Info Kru --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-3 border-b border-slate-100 pb-2">
                    Profil Wewenang Anda
                </h3>
                <dl class="space-y-2.5 text-xs">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Nama Lengkap</dt>
                        <dd class="font-bold text-slate-800">{{ $user->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Email Redaksi</dt>
                        <dd class="font-mono text-slate-800">{{ $user->email }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Peran RBAC</dt>
                        <dd class="font-bold text-rose-600 uppercase">
                            {{ $user->getRoleNames()->first() ?? 'Kru' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Proteksi OTP Email</dt>
                        <dd class="font-bold text-emerald-600">Aktif Terverifikasi</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-3 border-b border-slate-100 pb-2">
                    Rubrik Penugasan
                </h3>
                @if ($assignedCategories->isEmpty())
                    <p class="text-xs text-slate-400 leading-relaxed">
                        @if ($user->hasAnyRole(['super-admin', 'pemred']))
                            Sebagai Pemred/Admin, Anda berwenang mengawasi seluruh rubrik berita.
                        @else
                            Belum ada rubrik khusus yang ditugaskan kepada Anda.
                        @endif
                    </p>
                @else
                    <ul class="flex flex-wrap gap-1.5">
                        @foreach ($assignedCategories as $cat)
                            <li class="badge-category rounded px-2.5 py-1 text-xs font-semibold"
                                style="--badge-color: {{ $cat->safeBadgeColor() }}">
                                {{ $cat->name }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

@endsection
