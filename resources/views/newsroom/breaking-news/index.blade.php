{{-- File: resources/views/newsroom/breaking-news/index.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Manajemen Breaking News Ticker')

@section('content')

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Breaking News Ticker</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola siaran teks berjalan (*running text*) darurat merah yang tampil di bar atas homepage.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Form Tambah Breaking News (5 Kolom) --}}
        <div class="lg:col-span-5">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="flex h-3 w-3 rounded-full bg-rose-600 animate-pulse"></span>
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900">Siarkan Berita Darurat Baru</h2>
                </div>

                @if (isset($errors) && $errors->any())
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('newsroom.breaking-news.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="title" class="block text-xs font-bold uppercase text-slate-700 mb-1">
                            Teks Berita Darurat <span class="text-rose-600">*</span>
                        </label>
                        <textarea id="title"
                                  name="title"
                                  rows="3"
                                  required
                                  placeholder="Contoh: BREAKING NEWS: Gempa M 5.4 Guncang Pesisir Banten, Getaran Terasa hingga Jakarta..."
                                  class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">{{ old('title') }}</textarea>
                    </div>

                    <div>
                        <label for="url" class="block text-xs font-bold uppercase text-slate-700 mb-1">
                            Tautan Berita Terkait (Opsional)
                        </label>
                        <input type="url"
                               id="url"
                               name="url"
                               value="{{ old('url') }}"
                               placeholder="https://..."
                               class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="duration_hours" class="block text-xs font-bold uppercase text-slate-700 mb-1">
                            Durasi Siaran Ticker
                        </label>
                        <select id="duration_hours" name="duration_hours" class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
                            <option value="2">2 Jam ke Depan</option>
                            <option value="4" selected>4 Jam ke Depan</option>
                            <option value="8">8 Jam ke Depan</option>
                            <option value="24">24 Jam Penuh</option>
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-rose-600 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-rose-700 transition flex items-center justify-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        Siarkan Sekarang di Ticker
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabel Daftar Breaking News (7 Kolom) --}}
        <div class="lg:col-span-7">
            <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900">Riwayat & Status Siaran Ticker</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5">Teks Siaran</th>
                                <th class="px-4 py-3.5">Mulai / Berakhir</th>
                                <th class="px-4 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($items as $item)
                                <tr class="hover:bg-slate-50/75 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900 line-clamp-2 max-w-xs">
                                            {{ $item->title }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-[11px] text-slate-500">
                                        Mulai: {{ $item->starts_at?->toWib()->translatedFormat('d M, H:i') }} WIB<br>
                                        Selesai: {{ $item->ends_at?->toWib()->translatedFormat('d M, H:i') }} WIB
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <form method="POST" action="{{ route('newsroom.breaking-news.toggle', $item->id) }}">
                                            @csrf
                                            <button type="submit" class="rounded px-2.5 py-1 text-[10px] font-bold uppercase transition {{ $item->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                                {{ $item->is_active ? '● LIVE TAYANG' : '○ NONAKTIF' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <form method="POST" action="{{ route('newsroom.breaking-news.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Hapus siaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-bold text-rose-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                        Belum ada siaran breaking news.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-200">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
