{{-- File: resources/views/public/search.blade.php --}}
@extends('layouts.public')

@section('title', ($query ? 'Pencarian: ' . $query : 'Indeks Berita & Arsip Redaksi') . ' - Ruang Data')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    {{-- Header & Interactive Filter Bar Indeks Berita --}}
    <div class="mb-8 rounded-lg border border-slate-200 bg-white p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-5 border-b-2 border-black">
            <div>
                <h1 class="font-serif text-xl sm:text-2xl font-bold text-black flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600 text-[24px]">calendar_month</span>
                    INDEKS BERITA <span class="text-red-600">&amp; ARSIP REDAKSI</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Telusuri seluruh arsip pemberitaan terverifikasi Ruang Data berdasarkan kata kunci, tanggal terbit, dan rubrik.
                </p>
            </div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                TOTAL {{ number_format($posts->total()) }} ARTIKEL DITEMUKAN
            </span>
        </div>

        <form action="{{ route('search') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <label for="filter-q" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Kata Kunci Berita</label>
                <input type="text"
                       id="filter-q"
                       name="q"
                       value="{{ $query }}"
                       placeholder="Cari judul, topik, atau isi berita..."
                       class="w-full rounded border border-slate-300 bg-slate-50 px-3.5 py-2 text-xs text-slate-800 focus:border-red-600 focus:bg-white focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <label for="filter-date" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Tanggal Terbit</label>
                <input type="date"
                       id="filter-date"
                       name="date"
                       value="{{ $date }}"
                       class="w-full rounded border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:bg-white focus:outline-none">
            </div>

            <div class="sm:col-span-3">
                <label for="filter-kategori" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Rubrik Redaksi</label>
                <select id="filter-kategori"
                        name="kategori"
                        class="w-full rounded border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-800 focus:border-red-600 focus:bg-white focus:outline-none">
                    <option value="">Semua Rubrik</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ $category === $cat->slug ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit"
                        class="w-full rounded bg-black hover:bg-red-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white transition cursor-pointer flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[15px]">search</span>
                    Filter
                </button>
            </div>
        </form>

        {{-- Quick Archive Dates Bar --}}
        @if ($archiveDates->isNotEmpty())
            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mr-1">Arsip Tanggal Tersedia:</span>
                <a href="{{ route('search', array_filter(['q' => $query, 'kategori' => $category])) }}"
                   class="px-2.5 py-1 rounded text-[11px] font-semibold transition {{ $date === '' ? 'bg-black text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Semua Tanggal
                </a>
                @foreach ($archiveDates as $arch)
                    <a href="{{ route('search', array_filter(['q' => $query, 'kategori' => $category, 'date' => $arch->pub_date])) }}"
                       class="px-2.5 py-1 rounded text-[11px] font-semibold transition {{ $date === $arch->pub_date ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ \Carbon\Carbon::parse($arch->pub_date)->translatedFormat('d M Y') }} ({{ $arch->total }})
                    </a>
                @endforeach
            </div>
        @endif

        @if ($query !== '' || $date !== '' || $category !== '')
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span>Filter aktif:</span>
                @if ($query !== '')
                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2.5 py-0.5 text-slate-800 font-semibold">
                        Kata kunci: "{{ $query }}"
                    </span>
                @endif
                @if ($date !== '')
                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2.5 py-0.5 text-slate-800 font-semibold">
                        Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                    </span>
                @endif
                @if ($category !== '')
                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2.5 py-0.5 text-slate-800 font-semibold">
                        Rubrik: {{ ucfirst($category) }}
                    </span>
                @endif
                <a href="{{ route('search') }}" class="text-red-600 hover:underline font-bold ml-1">Reset Semua Filter</a>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <div class="space-y-4">
                @forelse ($posts as $post)
                    <article class="group flex flex-col sm:flex-row gap-4 rounded-lg border border-slate-200 bg-white p-4 shadow-xs hover:border-slate-300 hover:shadow-sm transition">
                        <div class="relative aspect-[16/10] sm:aspect-[4/3] w-full sm:w-48 shrink-0 overflow-hidden rounded bg-slate-100">
                            <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                <img src="{{ $post->featuredMedia?->url() ?? asset('images/hero-editorial.jpg') }}"
                                     onerror="this.onerror=null;this.src='{{ asset('images/hero-editorial.jpg') }}';"
                                     alt="{{ $post->title }}"
                                     class="h-full w-full object-cover group-hover:scale-105 transition duration-500"
                                     loading="lazy">
                            </a>
                        </div>

                        <div class="flex flex-1 flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase text-white shadow-xs"
                                          style="background-color: {{ $post->category?->safeBadgeColor() ?? '#dc2626' }}">
                                        {{ $post->category?->name }}
                                    </span>
                                    <span class="text-[11px] text-slate-400">
                                        {{ $post->published_at?->translatedFormat('d M Y') }} &bull; {{ $post->published_at?->format('H:i') }} WIB
                                    </span>
                                </div>
                                <h2 class="font-serif text-base sm:text-lg font-bold text-slate-900 group-hover:text-red-600 transition leading-snug">
                                    <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                        {{ $post->title }}
                                    </a>
                                </h2>
                                <p class="mt-1.5 text-xs sm:text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                    {{ $post->excerpt }}
                                </p>
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                                <span>Oleh <strong class="text-slate-700 font-semibold">{{ $post->authorDisplayName() }}</strong></span>
                                <span>{{ number_format($post->views_count) }} pembaca</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-slate-800">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-amber-600 text-[22px]">info</span>
                            <div>
                                <h3 class="font-bold text-sm text-slate-900">
                                    Tidak ditemukan berita yang persis cocok dengan filter tersebut.
                                </h3>
                                <p class="text-xs text-slate-600 mt-1">
                                    Silakan pilih salah satu <strong>Arsip Tanggal Tersedia</strong> di atas atau <a href="{{ route('search') }}" class="text-red-600 font-bold underline">tampilkan seluruh indeks berita</a>.
                                </p>
                            </div>
                        </div>
                    </div>

                    @if ($fallbackPosts->isNotEmpty())
                        <div class="pt-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-black border-b-2 border-black pb-2 mb-4">
                                ARSIP BERITA TERBARU LAINNYA
                            </h3>
                            <div class="space-y-4">
                                @foreach ($fallbackPosts as $post)
                                    <article class="group flex flex-col sm:flex-row gap-4 rounded-lg border border-slate-200 bg-white p-4 shadow-xs hover:border-slate-300 transition">
                                        <div class="relative aspect-[16/10] sm:aspect-[4/3] w-full sm:w-44 shrink-0 overflow-hidden rounded bg-slate-100">
                                            <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                                <img src="{{ $post->featuredMedia?->url() ?? asset('images/hero-editorial.jpg') }}"
                                                     onerror="this.onerror=null;this.src='{{ asset('images/hero-editorial.jpg') }}';"
                                                     alt="{{ $post->title }}"
                                                     class="h-full w-full object-cover group-hover:scale-105 transition"
                                                     loading="lazy">
                                            </a>
                                        </div>
                                        <div class="flex flex-1 flex-col justify-between">
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase text-white"
                                                          style="background-color: {{ $post->category?->safeBadgeColor() ?? '#dc2626' }}">
                                                        {{ $post->category?->name }}
                                                    </span>
                                                    <span class="text-[11px] text-slate-400">
                                                        {{ $post->published_at?->translatedFormat('d M Y') }}
                                                    </span>
                                                </div>
                                                <h4 class="font-serif text-base font-bold text-slate-900 group-hover:text-red-600 transition">
                                                    <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                                        {{ $post->title }}
                                                    </a>
                                                </h4>
                                                <p class="mt-1 text-xs text-slate-600 line-clamp-2">{{ $post->excerpt }}</p>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforelse
            </div>

            @if ($posts instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>

        <aside class="lg:col-span-4">
            <div class="sticky top-6">
                <div class="flex items-center justify-between pb-2 mb-4 border-b-2 border-black">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-black">
                        MOST <span class="text-red-600">READ</span>
                    </h3>
                    <span class="text-[10px] font-bold uppercase text-slate-400">TOP 5</span>
                </div>

                <div class="space-y-4">
                    @foreach ($popularPosts as $index => $popPost)
                        <article class="group flex items-start gap-3.5 {{ ! $loop->last ? 'border-b border-slate-100 pb-3.5' : '' }}">
                            <span class="font-serif text-2xl font-black text-slate-200 group-hover:text-red-600 transition leading-none select-none">
                                0{{ $index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <span class="text-[9px] font-bold uppercase tracking-wider block mb-0.5"
                                      style="color: {{ $popPost->category?->safeBadgeColor() ?? '#dc2626' }}">
                                    {{ $popPost->category?->name }}
                                </span>
                                <h4 class="text-xs sm:text-[13px] font-bold text-slate-800 group-hover:text-red-600 transition leading-snug line-clamp-2">
                                    <a href="{{ route('posts.show', [$popPost->category->slug, $popPost->slug]) }}">
                                        {{ $popPost->title }}
                                    </a>
                                </h4>
                                <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400">
                                    <span>{{ number_format($popPost->views_count) }} pembaca</span>
                                    <span>{{ $popPost->published_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
