{{-- File: resources/views/public/epaper/show.blade.php --}}
@extends('layouts.public')

@section('title', ($epaper?->edition_number ?? 'E-Paper Hari Ini') . ' - Edisi Cetak Digital Ruang Data')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

    {{-- Top Control Bar (Action Buttons & Edition Switcher) --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-4 shadow-xs print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-black transition">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Beranda
            </a>
            <span class="text-slate-300">|</span>
            <span class="text-xs font-bold uppercase tracking-wider text-red-600 flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">newspaper</span>
                KORAN DIGITAL RUANG DATA
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if ($editions->count() > 1)
                <form action="{{ route('epaper.show') }}" method="GET" class="inline-flex items-center gap-1.5">
                    <label for="edisi-select" class="text-[11px] font-bold text-slate-500 uppercase">Edisi:</label>
                    <select id="edisi-select"
                            name="edisi"
                            onchange="this.form.submit()"
                            class="rounded border border-slate-300 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-800">
                        @foreach ($editions as $ed)
                            <option value="{{ $ed->id }}" {{ $epaper?->id === $ed->id ? 'selected' : '' }}>
                                {{ $ed->edition_number }} ({{ $ed->edition_date->translatedFormat('d M Y') }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded border border-slate-300 bg-white hover:bg-slate-100 px-3.5 py-2 text-xs font-bold uppercase tracking-wider text-slate-800 transition cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">print</span>
                Cetak Lembaran
            </button>

            <a href="{{ route('epaper.download', $epaper ? ['edisi' => $epaper->id] : []) }}"
               class="inline-flex items-center gap-1.5 rounded bg-red-600 hover:bg-red-700 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white transition shadow-xs">
                <span class="material-symbols-outlined text-[16px]">download</span>
                Unduh PDF Edisi Cetak ({{ number_format($epaper?->downloads_count ?? 0) }})
            </a>
        </div>
    </div>

    {{-- Broadsheet Newspaper Sheet --}}
    <div class="bg-[#fcfaf5] border border-slate-300 shadow-xl rounded-xs p-6 sm:p-10 lg:p-12 text-slate-900">

        {{-- Broadsheet Masthead --}}
        <header class="border-b-4 border-double border-black pb-4 mb-6 text-center">
            <div class="flex items-center justify-between text-[10px] sm:text-xs font-bold uppercase tracking-widest text-slate-600 border-b border-slate-300 pb-2 mb-4">
                <span>{{ $epaper?->edition_number ?? ('EDISI #' . now()->format('ymd')) }}</span>
                <span>{{ ($epaper?->edition_date ?? now())->translatedFormat('l, d F Y') }}</span>
                <span class="text-red-700">EDISI CETAK TERVERIFIKASI</span>
            </div>

            <h1 class="font-serif text-4xl sm:text-6xl font-black tracking-tight text-black uppercase">
                RUANG DATA
            </h1>
            <p class="mt-1 text-[10px] sm:text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">
                INDEPENDENT JOURNALISM &bull; DATA PERSPECTIVES &bull; LAPORAN UTAMA HARIAN
            </p>
        </header>

        {{-- Main Lead Story (Headline) --}}
        @if ($headlinePost)
            <article class="border-b-2 border-black pb-8 mb-8">
                <div class="mb-3 flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-red-600 text-white text-[10px] font-extrabold uppercase tracking-widest">
                        LAPORAN UTAMA &bull; {{ $headlinePost->category?->name }}
                    </span>
                    <span class="text-xs text-slate-500">
                        Oleh <strong>{{ $headlinePost->authorDisplayName() }}</strong> &bull; {{ $headlinePost->published_at?->translatedFormat('d M Y') }} {{ $headlinePost->published_at?->format('H:i') }} WIB
                    </span>
                </div>

                <h2 class="font-serif text-2xl sm:text-4xl lg:text-5xl font-bold text-black leading-tight mb-5">
                    <a href="{{ route('posts.show', [$headlinePost->category->slug, $headlinePost->slug]) }}" class="hover:text-red-700 transition">
                        {{ $headlinePost->title }}
                    </a>
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <div class="lg:col-span-7">
                        <div class="aspect-[16/10] w-full overflow-hidden border border-slate-300 bg-slate-200">
                            <img src="{{ $headlinePost->featuredMedia?->url() ?? asset('images/hero-editorial.jpg') }}"
                                 onerror="this.onerror=null;this.src='{{ asset('images/hero-editorial.jpg') }}';"
                                 alt="{{ $headlinePost->title }}"
                                 class="w-full h-full object-cover">
                        </div>
                        @if ($headlinePost->excerpt)
                            <p class="mt-2 text-xs italic text-slate-600 border-l-2 border-red-600 pl-2.5">
                                {{ $headlinePost->excerpt }}
                            </p>
                        @endif
                    </div>

                    <div class="lg:col-span-5 font-serif text-sm text-slate-800 leading-relaxed space-y-3 text-justify">
                        <p class="first-letter:float-left first-letter:font-serif first-letter:text-4xl first-letter:font-black first-letter:pr-2 first-letter:leading-none first-letter:text-black">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $headlinePost->body), 950) }}
                        </p>
                        <div class="pt-2 print:hidden">
                            <a href="{{ route('posts.show', [$headlinePost->category->slug, $headlinePost->slug]) }}"
                               class="inline-flex items-center gap-1 text-xs font-sans font-bold uppercase tracking-wider text-red-600 hover:text-black">
                                Baca Artikel Lengkap &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        @endif

        {{-- Newspaper Multi-Column Grid for Other Published Posts --}}
        <div>
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-red-700 mb-4">
                SOROTAN REDAKSI &amp; INDEKS PEMBERITAAN HARIAN
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 divide-y md:divide-y-0 border-t border-slate-300 pt-5">
                @foreach ($posts->filter(fn ($p) => $p->id !== $headlinePost?->id) as $post)
                    <article class="flex flex-col justify-between pb-4 border-b border-slate-200">
                        <div>
                            <div class="aspect-[16/10] w-full overflow-hidden border border-slate-300 bg-slate-100 mb-2.5">
                                <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                    <img src="{{ $post->featuredMedia?->url() ?? asset('images/hero-editorial.jpg') }}"
                                         onerror="this.onerror=null;this.src='{{ asset('images/hero-editorial.jpg') }}';"
                                         alt="{{ $post->title }}"
                                         class="w-full h-full object-cover">
                                </a>
                            </div>

                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-red-600 block">
                                {{ $post->category?->name }}
                            </span>

                            <h4 class="font-serif text-lg font-bold text-black hover:text-red-700 transition leading-snug mt-0.5">
                                <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                    {{ $post->title }}
                                </a>
                            </h4>

                            <p class="mt-2 font-serif text-xs text-slate-700 leading-relaxed text-justify line-clamp-4">
                                {{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->body), 220) }}
                            </p>
                        </div>

                        <div class="mt-3 pt-2 border-t border-slate-200/80 flex items-center justify-between text-[10px] text-slate-500">
                            <span>Oleh <strong>{{ $post->authorDisplayName() }}</strong></span>
                            <span>{{ $post->published_at?->translatedFormat('d M Y') }} &bull; {{ number_format($post->views_count) }} pembaca</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        {{-- Broadsheet Footer --}}
        <footer class="mt-10 pt-4 border-t-2 border-black flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
            <span>Diterbitkan secara resmi oleh Redaksi <strong>Ruang Data</strong></span>
            <span>Dokumen E-Paper Bebas Akses Publik &bull; {{ ($epaper?->edition_date ?? now())->translatedFormat('d F Y') }}</span>
        </footer>
    </div>
</div>
@endsection
