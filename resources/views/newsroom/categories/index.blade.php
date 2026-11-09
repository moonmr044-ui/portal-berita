{{-- File: resources/views/newsroom/categories/index.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Manajemen Rubrik Kategori')

@section('content')

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Rubrik & Kategori Berita</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Atur rubrik menu navigasi yang tampil di homepage dan pengelompokan berita.
            </p>
        </div>

        <div>
            <a href="{{ route('newsroom.categories.create') }}"
               class="rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Rubrik Baru
            </a>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Urutan</th>
                        <th class="px-4 py-3.5">Nama Rubrik</th>
                        <th class="px-4 py-3.5">Slug URL</th>
                        <th class="px-4 py-3.5">Warna Badge</th>
                        <th class="px-4 py-3.5">Total Artikel</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($categories as $cat)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-4 font-bold text-slate-700">
                                #{{ $cat->sort_order }}
                            </td>
                            <td class="px-4 py-4 font-bold text-slate-900">
                                <span class="badge-category rounded px-2 py-0.5 text-xs font-semibold mr-1.5"
                                      style="--badge-color: {{ $cat->safeBadgeColor() }}">
                                    {{ $cat->name }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-mono text-slate-500">
                                /kategori/{{ $cat->slug }}
                            </td>
                            <td class="px-4 py-4 font-mono text-slate-700">
                                <span class="inline-block h-3.5 w-3.5 rounded-full border border-slate-200 align-middle mr-1" style="background-color: {{ $cat->badge_color }}"></span>
                                {{ $cat->badge_color }}
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-700">
                                {{ number_format($cat->posts_count) }} artikel
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $cat->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-3">
                                <a href="{{ route('newsroom.categories.edit', $cat->id) }}" class="font-bold text-slate-700 hover:text-rose-600">
                                    Edit
                                </a>
                                @if ($cat->posts_count === 0)
                                    <form method="POST" action="{{ route('newsroom.categories.destroy', $cat->id) }}" class="inline" onsubmit="return confirm('Hapus rubrik ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-bold text-rose-600 hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
