{{-- File: resources/views/public/home.blade.php --}}
@extends('layouts.public')

@section('title', 'Ruang Data - Independent Journalism & Data Perspectives')

@php
    $heroUrl = $headlinePost?->featuredMedia?->url() ?? asset('images/hero-editorial.jpg');
@endphp

@section('ambient_image', $heroUrl)

{{-- 1. FRAMED HERO SECTION INSIDE AMBIENT BACKDROP (EXACT MATCH TO REFERENCE) --}}
@if ($headlinePost)
@section('hero')
<div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 pt-1 pb-5 sm:pt-1.5 sm:pb-9">
    <article class="relative w-full h-[310px] sm:h-[460px] lg:h-[500px] overflow-hidden rounded-[2px] shadow-[0_24px_60px_rgba(0,0,0,0.65)] group bg-[#181a1f]">
        {{-- Sharp Full-Clarity Hero Photograph --}}
        <img src="{{ $heroUrl }}"
             onerror="this.onerror=null;this.src='{{ asset('images/hero-editorial.jpg') }}';"
             alt="{{ $headlinePost->title }}"
             class="w-full h-full object-cover object-center group-hover:scale-[1.02] transition-transform duration-700">

        {{-- Localized Bottom Vignette (Keeps Top 55% Bright & Clear, Bottom Smoothly Dark for Crisp White Typography) --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/45 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/90 via-black/35 to-transparent"></div>

        {{-- Hero Inner Content Overlay --}}
        <div class="absolute inset-x-0 bottom-0 z-10 p-4 sm:p-10 lg:p-12 flex flex-row items-end justify-between gap-3 sm:gap-6">
            <div class="max-w-2xl flex-1 min-w-0">
                {{-- Single Red BREAKING Badge with Star Spark Icon (Exact Reference Match) --}}
                <div class="mb-2 sm:mb-3">
                    <span class="inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-0.5 sm:py-1 bg-[#e50914] text-white text-[9px] sm:text-[10px] font-extrabold uppercase tracking-[0.14em] rounded-[2px] shadow-md">
                        <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2L14.8 9.2L22 12L14.8 14.8L12 22L9.2 14.8L2 12L9.2 9.2L12 2Z"/>
                        </svg>
                        BREAKING
                    </span>
                </div>

                {{-- Editorial Serif Headline --}}
                <h2 class="font-serif text-xl sm:text-4xl lg:text-[44px] font-bold text-white leading-[1.15] sm:leading-[1.12] tracking-tight drop-shadow-sm line-clamp-2 sm:line-clamp-3">
                    <a href="{{ route('posts.show', [$headlinePost->category->slug, $headlinePost->slug]) }}"
                       class="hover:text-red-400 transition-colors">
                        {{ $headlinePost->title }}
                    </a>
                </h2>

                {{-- Excerpt --}}
                @if ($headlinePost->excerpt)
                    <p class="mt-1.5 sm:mt-2.5 text-[11px] sm:text-sm text-white/75 leading-relaxed font-sans line-clamp-1 sm:line-clamp-2 max-w-xl">
                        {{ $headlinePost->excerpt }}
                    </p>
                @endif

                {{-- Metadata Line --}}
                <div class="mt-2.5 sm:mt-4 flex items-center gap-3 sm:gap-4 text-[10px] sm:text-[11px] text-white/70 flex-wrap">
                    <span class="inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px] text-white/65">schedule</span>
                        <span>{{ $headlinePost->published_at?->translatedFormat('d M Y') }} &bull; {{ $headlinePost->published_at?->format('H:i') }} WIB</span>
                    </span>
                    <span class="inline-flex items-center gap-1" title="Dilihat">
                        <span class="material-symbols-outlined text-[14px] text-white/65">visibility</span>
                        <span>{{ number_format((int) ($headlinePost->views_count ?? 0)) }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1" title="Disukai">
                        <span class="material-symbols-outlined text-[14px] text-white/65">favorite_border</span>
                        <span>{{ number_format((int) ($headlinePost->reactions_count ?? 0)) }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1" title="Komentar">
                        <span class="material-symbols-outlined text-[14px] text-white/65">chat_bubble_outline</span>
                        <span>{{ number_format((int) ($headlinePost->comments_count ?? 0)) }}</span>
                    </span>
                    <span class="hidden sm:inline">&bull; Oleh <strong class="text-white/90 font-semibold">{{ $headlinePost->authorDisplayName() }}</strong></span>
                </div>
            </div>

            {{-- Right Circular Solid Red Play Button --}}
            <div class="shrink-0">
                <a href="{{ route('posts.show', [$headlinePost->category->slug, $headlinePost->slug]) }}"
                   class="group/btn flex flex-col items-center gap-1.5 sm:gap-2">
                    <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-full bg-[#e50914] group-hover/btn:bg-red-700 flex items-center justify-center text-white shadow-xl group-hover/btn:scale-105 transition-all duration-300">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 ml-0.5 fill-current" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                    <span class="hidden sm:block text-[8.5px] sm:text-[9px] font-bold uppercase tracking-[0.16em] text-white/75 group-hover/btn:text-white transition">
                        BACA SELENGKAPNYA
                    </span>
                </a>
            </div>
        </div>
    </article>
</div>
@endsection
@endif

@section('content')


{{-- 2. MAIN CONTAINER: TRENDING STORIES (LEFT 8) + LATEST STORIES (RIGHT 4) --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">

        {{-- LEFT COLUMN: TRENDING STORIES (8 COLS) --}}
        <div class="lg:col-span-8">
            {{-- Section Title --}}
            <div class="flex items-center justify-between pb-2 mb-6 border-b-2 border-black">
                <h3 class="text-base sm:text-lg font-bold uppercase tracking-wider text-black">
                    TRENDING <span class="text-red-600">STORIES</span>
                </h3>
                <span class="text-xs uppercase tracking-wider font-semibold text-slate-400">
                    CURATED TODAY
                </span>
            </div>

            {{-- Grid Pair 1: Top 2 Cards --}}
            @php
                $firstPair = $pickPosts->slice(0, 2);
                $secondPair = $pickPosts->slice(2, 2);
                if ($firstPair->isEmpty() && isset($latestPosts)) {
                    $firstPair = $latestPosts->take(2);
                }
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                @foreach ($firstPair as $post)
                    <article class="group flex flex-col justify-between">
                        <div>
                            {{-- Image with Badge --}}
                            <div class="relative aspect-[16/10] w-full overflow-hidden rounded bg-slate-100">
                                <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                    <img src="{{ $post->featuredMedia?->url() ?? 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=600&q=80' }}"
                                         alt="{{ $post->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                         loading="lazy">
                                </a>
                                <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider text-white shadow-xs"
                                      style="background-color: {{ $post->category?->safeBadgeColor() ?? '#dc2626' }}">
                                    {{ $post->category->name }}
                                </span>
                            </div>

                            {{-- Title --}}
                            <h4 class="font-serif font-bold text-base sm:text-lg text-slate-900 group-hover:text-red-600 transition leading-snug line-clamp-2 mt-3">
                                <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                    {{ $post->title }}
                                </a>
                            </h4>

                            {{-- Excerpt --}}
                            <p class="text-xs sm:text-sm text-slate-500 line-clamp-2 leading-relaxed mt-1.5">
                                {{ $post->excerpt }}
                            </p>
                        </div>

                        {{-- Card Meta --}}
                        <div class="text-[11px] text-slate-400 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                            <span>{{ $post->published_at?->translatedFormat('d M Y') }}</span>
                            <div class="flex items-center gap-3">
                                <span class="flex items-center gap-1" title="Dilihat">
                                    <span class="material-symbols-outlined text-[13px]">visibility</span>
                                    {{ number_format((int) ($post->views_count ?? 0)) }}
                                </span>
                                <span class="flex items-center gap-1" title="Disukai">
                                    <span class="material-symbols-outlined text-[13px]">favorite_border</span>
                                    {{ number_format((int) ($post->reactions_count ?? 0)) }}
                                </span>
                                <span class="flex items-center gap-1" title="Komentar">
                                    <span class="material-symbols-outlined text-[13px]">chat_bubble_outline</span>
                                    {{ number_format((int) ($post->comments_count ?? 0)) }}
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Grid Pair 2: Next 2 Cards (with Play icon badge on bottom-right of image) --}}
            @if ($secondPair->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                    @foreach ($secondPair as $post)
                        <article class="group flex flex-col justify-between">
                            <div>
                                <div class="relative aspect-[16/10] w-full overflow-hidden rounded bg-slate-100">
                                    <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                        <img src="{{ $post->featuredMedia?->url() ?? 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=600&q=80' }}"
                                             alt="{{ $post->title }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                             loading="lazy">
                                    </a>
                                    <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider text-white shadow-xs"
                                          style="background-color: {{ $post->category?->safeBadgeColor() ?? '#7c3aed' }}">
                                        {{ $post->category->name }}
                                    </span>
                                    <span class="absolute bottom-2.5 right-2.5 w-6 h-6 rounded bg-red-600 text-white flex items-center justify-center shadow-md">
                                        <span class="material-symbols-outlined text-[15px]">play_arrow</span>
                                    </span>
                                </div>

                                <h4 class="font-serif font-bold text-base sm:text-lg text-slate-900 group-hover:text-red-600 transition leading-snug line-clamp-2 mt-3">
                                    <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                        {{ $post->title }}
                                    </a>
                                </h4>

                                <p class="text-xs sm:text-sm text-slate-500 line-clamp-2 leading-relaxed mt-1.5">
                                    {{ $post->excerpt }}
                                </p>
                            </div>

                            <div class="text-[11px] text-slate-400 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                <span>{{ $post->published_at?->translatedFormat('d M Y') }}</span>
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center gap-1" title="Dilihat">
                                        <span class="material-symbols-outlined text-[13px]">visibility</span>
                                        {{ number_format((int) ($post->views_count ?? 0)) }}
                                    </span>
                                    <span class="flex items-center gap-1" title="Disukai">
                                        <span class="material-symbols-outlined text-[13px]">favorite_border</span>
                                        {{ number_format((int) ($post->reactions_count ?? 0)) }}
                                    </span>
                                    <span class="flex items-center gap-1" title="Komentar">
                                        <span class="material-symbols-outlined text-[13px]">chat_bubble_outline</span>
                                        {{ number_format((int) ($post->comments_count ?? 0)) }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- RIGHT COLUMN: LATEST STORIES (4 COLS) --}}
        <div class="lg:col-span-4">
            {{-- Section Title with Red Dot --}}
            <div class="flex items-center justify-between pb-2 mb-6 border-b-2 border-black">
                <h3 class="text-base sm:text-lg font-bold uppercase tracking-wider text-black flex items-center gap-2">
                    LATEST <span class="text-red-600">STORIES</span>
                </h3>
                <span class="w-2.5 h-2.5 rounded-full bg-red-600 inline-block animate-pulse"></span>
            </div>

            {{-- Bordered Container Box --}}
            <div class="border border-slate-200 bg-white rounded-lg p-5 shadow-xs">
                <div class="space-y-4">
                    @forelse ($latestUpdates ?? $popularPosts as $item)
                        <article class="{{ ! $loop->last ? 'border-b border-slate-100 pb-3.5' : '' }}">
                            <span class="text-[10px] font-black text-red-600 uppercase tracking-wider block mb-1">
                                {{ $item->published_at?->diffForHumans() ?? 'BARU SAJA' }}
                            </span>
                            <h4 class="text-xs sm:text-[13px] font-bold text-slate-800 hover:text-red-600 transition leading-snug">
                                <a href="{{ route('posts.show', [$item->category->slug, $item->slug]) }}">
                                    {{ $item->title }}
                                </a>
                            </h4>
                        </article>
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada pembaruan terkini.</p>
                    @endforelse
                </div>

                {{-- View All Live Updates Button --}}
                <div class="mt-5 pt-3 border-t border-slate-100">
                    <a href="{{ route('search') }}"
                       class="block w-full py-2.5 text-center text-xs font-bold uppercase tracking-wider text-slate-700 border border-slate-300 rounded hover:bg-black hover:text-white hover:border-black transition">
                        VIEW ALL LIVE UPDATES
                    </a>
                </div>
            </div>

            {{-- Popular Topics Pills --}}
            <div class="mt-8">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">
                    TRENDING TOPICS
                </h4>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($popularTags as $tag)
                        <a href="{{ route('search', ['q' => $tag->name]) }}"
                           class="px-2.5 py-1 bg-slate-100 hover:bg-red-50 hover:text-red-600 rounded text-xs font-semibold text-slate-700 transition">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>


{{-- 3. FEATURED NEWS (SLIDER CAROUSEL WITH WORKING ARROWS) --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 border-t border-slate-200">
    {{-- Header --}}
    <div class="flex items-end justify-between pb-3 mb-6 border-b-2 border-black">
        <div>
            <h3 class="text-lg sm:text-xl font-bold uppercase tracking-wider text-black">
                FEATURED <span class="text-red-600">NEWS</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">
                Editor's hand-picked comprehensive reads
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="slideFeatured(-1)"
                    class="w-9 h-9 rounded-full border border-slate-300 hover:border-black hover:bg-black hover:text-white flex items-center justify-center transition text-slate-700 cursor-pointer shadow-xs active:scale-90"
                    title="Sebelumnya" aria-label="Geser ke kiri">
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </button>
            <button type="button" onclick="slideFeatured(1)"
                    class="w-9 h-9 rounded-full border border-slate-300 hover:border-black hover:bg-black hover:text-white flex items-center justify-center transition text-slate-700 cursor-pointer shadow-xs active:scale-90"
                    title="Berikutnya" aria-label="Geser ke kanan">
                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </button>
        </div>
    </div>

    {{-- Carousel Row (Horizontal Scrolling Slider) --}}
    <div id="featured-grid" class="flex gap-6 overflow-x-auto scroll-smooth snap-x" style="scrollbar-width: none; -ms-overflow-style: none;">
        @foreach ($featuredPosts as $post)
            <article class="w-[82%] sm:w-[calc(50%-12px)] lg:w-[calc(25%-18px)] shrink-0 snap-start group flex flex-col justify-between">
                <div>
                    {{-- Image with Pill Badge --}}
                    <div class="relative aspect-[16/10] w-full overflow-hidden rounded bg-slate-100">
                        <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                            <img src="{{ $post->featuredMedia?->url() ?? 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=600&q=80' }}"
                                 alt="{{ $post->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                        </a>
                        <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider text-white shadow-xs"
                              style="background-color: {{ $post->category?->safeBadgeColor() ?? '#dc2626' }}">
                            {{ $post->category->name }}
                        </span>
                    </div>

                    {{-- Title --}}
                    <h4 class="font-serif font-bold text-sm sm:text-base text-slate-900 group-hover:text-red-600 transition leading-snug line-clamp-2 mt-3">
                        <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                            {{ $post->title }}
                        </a>
                    </h4>

                    {{-- Excerpt --}}
                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mt-1">
                        {{ $post->excerpt }}
                    </p>
                </div>

                {{-- Bottom Meta --}}
                <div class="text-[11px] text-slate-400 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span>{{ $post->published_at?->translatedFormat('d M Y') }}</span>
                    <div class="flex items-center gap-2.5">
                        <span class="flex items-center gap-1" title="Dilihat">
                            <span class="material-symbols-outlined text-[13px]">visibility</span>
                            {{ number_format((int) ($post->views_count ?? 0)) }}
                        </span>
                        <span class="flex items-center gap-1" title="Disukai">
                            <span class="material-symbols-outlined text-[13px]">favorite_border</span>
                            {{ number_format((int) ($post->reactions_count ?? 0)) }}
                        </span>
                        <span class="flex items-center gap-1" title="Komentar">
                            <span class="material-symbols-outlined text-[13px]">chat_bubble_outline</span>
                            {{ number_format((int) ($post->comments_count ?? 0)) }}
                        </span>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>


{{-- 4. INTERACTIVE DESK: SURVEI PEMBACA, INDEKS BERITA, E-PAPER (REAL DATABASE SYSTEM) --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 border-t border-slate-200 mb-8">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        {{-- Widget 1: Survei Pembaca (100% Real Database Poll) --}}
        <div class="md:col-span-5 border border-slate-200 rounded-lg p-5 bg-white flex flex-col justify-between shadow-xs">
            @if ($activePoll)
                @php
                    $totalPollVotes = $activePoll->totalVotes();
                    $hasVoted = $userPollVote !== null;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-red-600 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">poll</span>
                            SURVEI PEMBACA
                        </span>
                        <span class="text-[11px] text-slate-400">{{ $activePoll->edition_label }}</span>
                    </div>

                    <h4 class="font-serif text-base font-bold text-slate-900 mb-2">
                        {{ $activePoll->question }}
                    </h4>

                    @if ($activePoll->description)
                        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                            {{ $activePoll->description }}
                        </p>
                    @endif

                    {{-- Form Voting Real ke Database --}}
                    <form action="{{ route('polls.vote', $activePoll) }}"
                          method="POST"
                          id="poll-form"
                          onsubmit="return submitRealPoll(event, this)"
                          class="space-y-2 {{ $hasVoted ? 'hidden' : '' }}">
                        @csrf
                        @foreach ($activePoll->options as $option)
                            <label class="flex items-center gap-2.5 p-2.5 rounded bg-slate-50 hover:bg-slate-100 border border-transparent hover:border-slate-200 cursor-pointer text-xs text-slate-800 transition">
                                <input type="radio"
                                       name="poll_option_id"
                                       required
                                       value="{{ $option->id }}"
                                       {{ $userPollVote?->poll_option_id === $option->id ? 'checked' : '' }}
                                       class="accent-red-600 shrink-0">
                                <span>{{ $option->label }}</span>
                            </label>
                        @endforeach

                        <button type="submit"
                                id="poll-submit-btn"
                                class="w-full mt-3 py-2.5 bg-black hover:bg-slate-800 text-white rounded text-xs font-bold uppercase tracking-wider transition cursor-pointer">
                            KIRIM SUARA ANDA
                        </button>

                        @if ($totalPollVotes > 0)
                            <button type="button"
                                    onclick="togglePollView(true)"
                                    class="w-full pt-1 text-center text-[11px] font-semibold text-slate-500 hover:text-red-600 transition cursor-pointer">
                                Lihat Hasil Sementara ({{ number_format($totalPollVotes) }} suara)
                            </button>
                        @endif
                    </form>

                    {{-- Hasil Real dari Database --}}
                    <div id="poll-result" class="{{ $hasVoted ? '' : 'hidden' }} space-y-3 pt-1">
                        <div id="poll-status-banner" class="p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded text-xs font-semibold flex items-center justify-between gap-1.5">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-emerald-600">check_circle</span>
                                <span id="poll-status-text">{{ $hasVoted ? 'Suara Anda telah tercatat di sistem.' : 'Hasil perolehan suara terkini.' }}</span>
                            </span>
                            <button type="button"
                                    onclick="togglePollView(false)"
                                    class="text-[11px] font-bold text-red-600 hover:underline shrink-0 cursor-pointer">
                                {{ $hasVoted ? 'Ubah Suara' : 'Ikut Memilih' }}
                            </button>
                        </div>

                        <div id="poll-bars-container" class="space-y-2.5">
                            @foreach ($activePoll->options as $idx => $option)
                                @php
                                    $pct = $option->percentage($totalPollVotes);
                                    $isUserChoice = $userPollVote?->poll_option_id === $option->id;
                                    $barColor = $idx === 0 ? 'bg-red-600' : ($idx === 1 ? 'bg-slate-800' : 'bg-slate-400');
                                @endphp
                                <div data-option-row="{{ $option->id }}">
                                    <div class="flex justify-between items-center text-xs text-slate-700 mb-1 gap-2">
                                        <span class="font-medium {{ $isUserChoice ? 'font-bold text-black' : '' }}">
                                            {{ $option->label }}
                                            @if ($isUserChoice)
                                                <span class="ml-1 inline-block px-1.5 py-0.2 bg-red-50 text-red-600 text-[10px] font-bold rounded">Pilihan Anda</span>
                                            @endif
                                        </span>
                                        <span class="font-bold shrink-0" data-option-stat="{{ $option->id }}">
                                            {{ $pct }}% <span class="text-[10px] font-normal text-slate-400">({{ $option->votes_count }})</span>
                                        </span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="{{ $barColor }} h-full transition-all duration-500"
                                             data-option-bar="{{ $option->id }}"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="pt-4 text-[11px] text-slate-400 flex items-center justify-between border-t border-slate-100 mt-4">
                    <span id="poll-total-counter">Total: <strong class="text-slate-700">{{ number_format($totalPollVotes) }}</strong> suara masuk</span>
                    <span>Batas: {{ $activePoll->deadline_label }}</span>
                </div>
            @endif
        </div>

        {{-- Widget 2: Indeks Berita & Arsip Redaksi (100% Real Archive Filter) --}}
        <div class="md:col-span-4 border border-slate-200 rounded-lg p-5 bg-white flex flex-col justify-between shadow-xs">
            @php
                $latestArchiveDate = $archiveDates->first()?->pub_date ?? '';
            @endphp
            <form action="{{ route('search') }}" method="GET" class="flex flex-col justify-between h-full">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-black flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                            INDEKS BERITA
                        </span>
                        <a href="{{ route('search') }}" class="text-[11px] font-bold text-red-600 hover:underline uppercase">
                            ARSIP REDAKSI
                        </a>
                    </div>
                    <p class="text-xs text-slate-500 mb-3">
                        Cari arsip pemberitaan terverifikasi Ruang Data berdasarkan tanggal dan rubrik:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-3">
                        <div>
                            <label for="indeks-date" class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Pilih Tanggal</label>
                            <input type="date"
                                   id="indeks-date"
                                   name="date"
                                   value="{{ $latestArchiveDate }}"
                                   class="w-full p-2 bg-slate-50 border border-slate-200 rounded text-xs text-slate-800 focus:outline-none focus:border-red-600">
                        </div>
                        <div>
                            <label for="indeks-kategori" class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Pilih Rubrik</label>
                            <select id="indeks-kategori"
                                    name="kategori"
                                    class="w-full p-2 bg-slate-50 border border-slate-200 rounded text-xs text-slate-800 focus:outline-none focus:border-red-600">
                                <option value="">Semua Rubrik</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Paling Dicari & Tanggal Arsip Aktif dari Database --}}
                    <div class="space-y-2.5">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1.5">Paling Dicari:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($categories as $cat)
                                    <a href="{{ route('search', ['kategori' => $cat->slug]) }}"
                                       class="px-2 py-0.5 bg-slate-100 hover:bg-red-50 hover:text-red-600 rounded text-[11px] font-medium text-slate-700 transition">
                                        #{{ $cat->name }}
                                    </a>
                                @endforeach
                                @foreach ($popularTags->take(3) as $tag)
                                    <a href="{{ route('search', ['q' => $tag->name]) }}"
                                       class="px-2 py-0.5 bg-slate-100 hover:bg-red-50 hover:text-red-600 rounded text-[11px] font-medium text-slate-700 transition">
                                        #{{ ucfirst($tag->name) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @if ($archiveDates->isNotEmpty())
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Arsip Tanggal Terbit:</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($archiveDates->take(3) as $arch)
                                        <a href="{{ route('search', ['date' => $arch->pub_date]) }}"
                                           class="px-2 py-0.5 border border-slate-200 hover:border-black hover:bg-black hover:text-white rounded text-[10px] font-semibold text-slate-600 transition">
                                            {{ \Carbon\Carbon::parse($arch->pub_date)->translatedFormat('d M Y') }} ({{ $arch->total }})
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <button type="submit"
                        class="w-full mt-4 py-2.5 bg-slate-100 hover:bg-black hover:text-white rounded text-xs font-bold uppercase tracking-wider transition flex items-center justify-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">search</span> BUKA INDEKS TANGGAL
                </button>
            </form>
        </div>

        {{-- Widget 3: E-Paper Hari Ini (100% Real Dynamic Broadsheet Reader & PDF Generator) --}}
        <div class="md:col-span-3 border border-slate-200 rounded-lg p-5 bg-white flex flex-col justify-between shadow-xs">
            @php
                $epaperEdition = $activeEpaper?->edition_number ?? ('EDISI #' . now()->format('ymd'));
                $epaperDate = ($activeEpaper?->edition_date ?? now())->translatedFormat('d M Y');
                $epaperCoverImg = $headlinePost?->featuredMedia?->url() ?? asset('images/hero-editorial.jpg');
                $epaperHeadline = $headlinePost?->title ?? ($activeEpaper?->title ?? 'Edisi Cetak Harian Ruang Data');
            @endphp
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-black flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">newspaper</span>
                        E-PAPER HARI INI
                    </span>
                    <span class="text-[11px] font-mono text-slate-400">{{ $epaperEdition }}</span>
                </div>

                {{-- Live Miniature Newspaper Front-Page Preview Using Real Database Headline --}}
                <a href="{{ route('epaper.show') }}"
                   class="block relative aspect-[4/3] rounded overflow-hidden bg-[#f9f7f1] border border-slate-300 group shadow-xs mb-3 p-2.5 flex flex-col justify-between">
                    {{-- Mini Newspaper Masthead --}}
                    <div class="border-b-2 border-black pb-1 flex items-center justify-between">
                        <span class="font-serif text-[11px] font-black tracking-tight text-black uppercase">RUANG DATA</span>
                        <span class="text-[8px] font-semibold text-slate-500 uppercase">{{ $epaperDate }}</span>
                    </div>

                    {{-- Mini Headline Image & Title --}}
                    <div class="relative flex-1 my-1.5 overflow-hidden rounded-[2px] bg-slate-900">
                        <img src="{{ $epaperCoverImg }}"
                             onerror="this.onerror=null;this.src='{{ asset('images/hero-editorial.jpg') }}';"
                             alt="{{ $epaperHeadline }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent flex items-end p-2">
                            <p class="font-serif text-[10px] font-bold text-white leading-tight line-clamp-2">
                                {{ $epaperHeadline }}
                            </p>
                        </div>
                    </div>

                    {{-- Mini Newspaper Columns Footer --}}
                    <div class="grid grid-cols-2 gap-1.5 pt-0.5 border-t border-slate-300 text-[8px] text-slate-600 font-serif leading-tight">
                        <span class="truncate">{{ $pickPosts->get(0)?->title ?? 'Sorotan Utama Redaksi' }}</span>
                        <span class="truncate text-right">{{ $pickPosts->get(1)?->title ?? 'Laporan Data Terkini' }}</span>
                    </div>

                    {{-- Hover Overlay --}}
                    <div class="absolute inset-0 bg-black/45 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <span class="px-3 py-1.5 bg-white text-slate-900 rounded text-xs font-bold uppercase shadow-md flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">visibility</span>
                            Baca Edisi Cetak
                        </span>
                    </div>
                </a>

                <p class="text-xs text-slate-500 leading-relaxed">
                    {{ $activeEpaper?->description ?? 'Format digital resolusi tinggi, gratis untuk seluruh pembaca Ruang Data.' }}
                </p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
                <a href="{{ route('epaper.download') }}"
                   class="block w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-bold uppercase tracking-wider text-center transition shadow-xs">
                    UNDUH PDF EDISI CETAK
                </a>
                <div class="flex items-center justify-between text-[11px] text-emerald-600 font-semibold">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">check_circle</span>
                        Akses terbuka &amp; gratis
                    </span>
                    <span class="text-slate-400 font-normal">{{ number_format($activeEpaper?->downloads_count ?? 0) }} unduhan</span>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function slideFeatured(direction) {
    const el = document.getElementById('featured-grid');
    if (!el) return;
    const card = el.querySelector('article');
    const scrollAmount = card ? (card.offsetWidth + 24) * direction : 320 * direction;

    if (direction > 0 && (el.scrollLeft + el.clientWidth >= el.scrollWidth - 10)) {
        el.scrollTo({ left: 0, behavior: 'smooth' });
    } else if (direction < 0 && el.scrollLeft <= 10) {
        el.scrollTo({ left: el.scrollWidth, behavior: 'smooth' });
    } else {
        el.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}

function togglePollView(showResults) {
    const form = document.getElementById('poll-form');
    const result = document.getElementById('poll-result');
    if (!form || !result) return;

    if (showResults) {
        form.classList.add('hidden');
        result.classList.remove('hidden');
    } else {
        result.classList.add('hidden');
        form.classList.remove('hidden');
    }
}

async function submitRealPoll(event, form) {
    event.preventDefault();
    const btn = document.getElementById('poll-submit-btn');
    const selected = form.querySelector('input[name="poll_option_id"]:checked');
    if (!selected) return false;

    const originalText = btn ? btn.textContent : 'KIRIM SUARA ANDA';
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'MENYIMPAN SUARA...';
    }

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || ''
            },
            body: JSON.stringify({
                poll_option_id: parseInt(selected.value, 10)
            })
        });

        if (!response.ok) {
            form.submit();
            return false;
        }

        const data = await response.json();

        const container = document.getElementById('poll-bars-container');
        if (container && Array.isArray(data.options)) {
            const colors = ['bg-red-600', 'bg-slate-800', 'bg-slate-400', 'bg-amber-600', 'bg-emerald-600'];
            container.innerHTML = data.options.map((opt, idx) => {
                const isChoice = opt.id === data.selected_option_id;
                const badge = isChoice
                    ? '<span class="ml-1 inline-block px-1.5 py-0.2 bg-red-50 text-red-600 text-[10px] font-bold rounded">Pilihan Anda</span>'
                    : '';
                const barColor = colors[idx % colors.length];
                return `
                    <div>
                        <div class="flex justify-between items-center text-xs text-slate-700 mb-1 gap-2">
                            <span class="${isChoice ? 'font-bold text-black' : 'font-medium'}">${opt.label} ${badge}</span>
                            <span class="font-bold shrink-0">${opt.percentage}% <span class="text-[10px] font-normal text-slate-400">(${opt.votes_count})</span></span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="${barColor} h-full transition-all duration-500" style="width: ${opt.percentage}%"></div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        const totalCounter = document.getElementById('poll-total-counter');
        if (totalCounter) {
            totalCounter.innerHTML = `Total: <strong class="text-slate-700">${data.total_votes}</strong> suara masuk`;
        }

        const statusText = document.getElementById('poll-status-text');
        if (statusText) {
            statusText.textContent = 'Suara Anda telah tercatat di database.';
        }

        togglePollView(true);
    } catch (err) {
        form.submit();
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    return false;
}
</script>

@endsection