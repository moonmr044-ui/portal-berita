{{-- File: resources/views/newsroom/posts/index.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Manajemen Naskah Berita')

@section('content')

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Naskah Redaksi</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola naskah tulisan, tinjau draf reporter, dan pantau status publikasi berita.
            </p>
        </div>

        <div>
            <a href="{{ route('newsroom.posts.create') }}"
               class="rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tulis Berita Baru
            </a>
        </div>
    </div>

    {{-- Filter Tab Status --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 pb-2">
        <div class="flex items-center gap-1 overflow-x-auto text-xs font-bold">
            <a href="{{ route('newsroom.posts.index', ['status' => 'all']) }}"
               class="rounded-lg px-3 py-2 {{ $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-200' }} transition">
                Semua ({{ $counts['all'] }})
            </a>
            <a href="{{ route('newsroom.posts.index', ['status' => 'published']) }}"
               class="rounded-lg px-3 py-2 {{ $statusFilter === 'published' ? 'bg-emerald-600 text-white' : 'text-emerald-700 hover:bg-emerald-100' }} transition">
                Terbit ({{ $counts['published'] }})
            </a>
            <a href="{{ route('newsroom.posts.index', ['status' => 'takedown']) }}"
               class="rounded-lg px-3 py-2 {{ $statusFilter === 'takedown' ? 'bg-slate-800 text-white' : 'text-slate-700 hover:bg-slate-200' }} transition">
                Diturunkan ({{ $counts['takedown'] }})
            </a>
        </div>

        <form action="{{ route('newsroom.posts.index') }}" method="GET" class="relative max-w-xs">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <input type="text"
                   name="q"
                   value="{{ $search }}"
                   placeholder="Cari naskah..."
                   class="rounded-lg border border-slate-300 bg-white py-1.5 pl-3 pr-8 text-xs focus:border-slate-900 focus:outline-none">
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </form>
    </div>

    {{-- Tabel Naskah --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Judul Naskah</th>
                        <th class="px-4 py-3.5">Rubrik</th>
                        <th class="px-4 py-3.5">Penulis</th>
                        <th class="px-4 py-3.5">Editor</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Pembaca</th>
                        <th class="px-6 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($posts as $post)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 line-clamp-1 max-w-sm sm:max-w-md">
                                    {{ $post->title }}
                                </div>
                                <div class="mt-0.5 text-[10px] text-slate-400">
                                    Dibuat: {{ $post->created_at?->toWib()->translatedFormat('d M Y, H:i') }} WIB
                                </div>
                                @if ($post->status === \App\Enums\PostStatus::REJECTED && $post->editorial_notes)
                                    <div class="mt-1 text-[11px] text-rose-600 bg-rose-50 p-1.5 rounded border border-rose-200">
                                        Catatan Editor: {{ $post->editorial_notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase badge-category"
                                      style="--badge-color: {{ $post->category?->safeBadgeColor() }}">
                                    {{ $post->category?->name }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap font-medium text-slate-700">
                                {{ $post->authorDisplayName() }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-slate-500">
                                {{ $post->editor?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase ring-1 {{ $post->status->badgeClass() }}">
                                    {{ $post->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-slate-500 font-mono">
                                {{ number_format($post->views_count) }}
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('newsroom.posts.edit', $post->id) }}"
                                   class="font-bold text-slate-800 hover:text-rose-600 transition">
                                    {{ $post->status === \App\Enums\PostStatus::UNDER_REVIEW ? 'Review' : 'Sunting' }}
                                </a>
                                @if ($post->status === \App\Enums\PostStatus::PUBLISHED)
                                    <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}"
                                       target="_blank"
                                       class="font-medium text-slate-400 hover:text-slate-600 transition">
                                        Pratinjau &nearr;
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada naskah ditemukan untuk kriteria ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $posts->links() }}
        </div>
    </div>

@endsection
