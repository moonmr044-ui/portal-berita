{{-- File: resources/views/public/posts/show.blade.php --}}
@extends('layouts.public')

@section('title', $post->title . ' - Ruang Data')
@section('meta_description', $post->excerpt ?? Str::limit(strip_tags($post->body ?? ''), 160))
@section('og_image', $post->featuredMedia?->url())
@section('og_type', 'article')

@php
    $detailAmbientUrl = $post->featuredMedia?->url() ?? asset('images/hero-editorial.jpg');
@endphp
@section('ambient_image', $detailAmbientUrl)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    {{-- Breadcrumbs Navigation --}}
    <nav aria-label="Breadcrumb" class="mb-5 text-xs text-slate-400">
        <ol class="flex items-center gap-2 flex-wrap">
            <li><a href="{{ route('home') }}" class="hover:text-red-600 transition font-medium">HOME</a></li>
            <li>&rsaquo;</li>
            <li>
                <a href="{{ route('categories.show', $post->category->slug) }}"
                   class="font-bold text-red-600 uppercase hover:underline">
                    {{ $post->category->name }}
                </a>
            </li>
            <li>&rsaquo;</li>
            <li class="text-slate-500 truncate max-w-xs sm:max-w-md">{{ $post->title }}</li>
        </ol>
    </nav>

    <div class="w-full">

        {{-- AREA KONTEN UTAMA ARTIKEL (FULL WIDTH) --}}
        <main class="w-full">
            <article>
                {{-- Header Artikel --}}
                <header class="mb-6">
                    {{-- Category Badge --}}
                    <div class="mb-3">
                        <span class="inline-block px-3 py-1 bg-red-600 text-white font-bold text-[11px] uppercase tracking-widest rounded-xs shadow-xs"
                              style="background-color: {{ $post->category?->safeBadgeColor() ?? '#dc2626' }}">
                            {{ $post->category->name }}
                        </span>
                    </div>

                    {{-- Main Editorial Headline --}}
                    <h1 class="font-serif text-2xl sm:text-4xl lg:text-[44px] font-bold text-slate-900 leading-tight tracking-tight mb-4">
                        {{ $post->title }}
                    </h1>

                    {{-- Standfirst / Editorial Dek --}}
                    @if ($post->excerpt)
                        <div class="text-base sm:text-lg text-slate-700 font-serif italic border-l-4 border-red-600 pl-4 py-2.5 my-5 bg-slate-50 leading-relaxed">
                            {{ $post->excerpt }}
                        </div>
                    @endif

                    {{-- Byline & Metadata Bar --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 border-y border-slate-200 py-3.5 text-xs text-slate-500">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($post->authorDisplayName(), 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-slate-800 font-bold">
                                    Oleh {{ $post->authorDisplayName() }}
                                    @if ($post->editor)
                                        <span class="text-slate-400 font-normal">&bull; Editor: {{ $post->editor->name }}</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $post->published_at?->translatedFormat('l, d F Y') }} &bull; {{ $post->published_at?->format('H:i') }} WIB
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="flex items-center gap-1 text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded">
                                <span class="material-symbols-outlined text-[14px]">visibility</span>
                                {{ number_format($post->views_count) }} Pembaca
                            </span>

                            @auth
                                <button type="button"
                                        onclick="togglePostLike(this, '{{ route('posts.like', $post) }}')"
                                        data-like-btn
                                        class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded transition cursor-pointer {{ ($hasLiked ?? false) ? 'bg-[#e50914] text-white' : 'bg-slate-100 text-slate-700 hover:bg-red-50 hover:text-[#e50914]' }}">
                                    <span class="material-symbols-outlined text-[14px]" data-like-icon style="font-variation-settings: 'FILL' {{ ($hasLiked ?? false) ? 1 : 0 }}">favorite</span>
                                    <span data-like-count>{{ number_format($likesCount ?? 0) }}</span> Suka
                                </button>
                            @else
                                <button type="button"
                                        onclick="openAuthModal('login')"
                                        class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded bg-slate-100 text-slate-700 hover:bg-red-50 hover:text-[#e50914] transition cursor-pointer"
                                        title="Masuk untuk menyukai berita ini">
                                    <span class="material-symbols-outlined text-[14px]">favorite</span>
                                    <span data-like-count>{{ number_format($likesCount ?? 0) }}</span> Suka
                                </button>
                            @endauth

                            <a href="#komentar-pembaca"
                               class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                <span class="material-symbols-outlined text-[14px]">chat_bubble</span>
                                {{ number_format(isset($comments) ? $comments->count() : 0) }} Komentar
                            </a>
                        </div>
                    </div>

                    {{-- Share Buttons Bar --}}
                    <div class="mt-4 flex items-center gap-2 text-xs flex-wrap">
                        <span class="font-bold text-slate-400 uppercase text-[10px] tracking-wider mr-1">Bagikan:</span>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . url()->current()) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 rounded bg-[#25D366] text-white px-3 py-1 font-semibold text-xs hover:opacity-90 transition shadow-xs">
                            WhatsApp
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(url()->current()) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 rounded bg-black text-white px-3 py-1 font-semibold text-xs hover:bg-slate-800 transition shadow-xs">
                            &Xopf; (Twitter)
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 rounded bg-[#1877F2] text-white px-3 py-1 font-semibold text-xs hover:opacity-90 transition shadow-xs">
                            Facebook
                        </a>
                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan artikel berhasil disalin!');"
                                class="inline-flex items-center gap-1 rounded border border-slate-300 bg-white text-slate-700 px-3 py-1 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer">
                            <span class="material-symbols-outlined text-[14px]">link</span> Salin Tautan
                        </button>
                    </div>
                </header>

                {{-- Featured Image with Caption Box --}}
                @if ($post->featuredMedia)
                    <figure class="mb-8 overflow-hidden rounded shadow-sm bg-slate-900">
                        <img src="{{ $post->featuredMedia->url() }}"
                             alt="{{ $post->featuredMedia->altOrFallback($post->title) }}"
                             class="w-full aspect-video object-cover"
                             loading="eager">
                        @if ($post->featuredMedia->caption || $post->featuredMedia->credit)
                            <figcaption class="p-3 text-xs text-slate-300 bg-slate-900 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <span>{{ $post->featuredMedia->caption }}</span>
                                @if ($post->featuredMedia->credit)
                                    <span class="text-slate-400 font-mono text-[11px]">(Foto: {{ $post->featuredMedia->credit }})</span>
                                @endif
                            </figcaption>
                        @endif
                    </figure>
                @endif

                {{-- Body Konten Artikel --}}
                <div class="prose prose-slate lg:prose-lg max-w-none prose-headings:font-serif prose-headings:font-bold prose-headings:text-slate-900 prose-p:leading-relaxed prose-p:text-slate-700 prose-blockquote:border-l-4 prose-blockquote:border-red-600 prose-blockquote:font-serif prose-blockquote:italic prose-a:text-red-600 hover:prose-a:underline prose-editorial">
                    {!! $post->body !!}
                </div>

                {{-- Kotak Ralat / Koreksi Berita Resmi (Jika ada) --}}
                @if ($post->corrections && $post->corrections->isNotEmpty())
                    <div class="mt-8 rounded border-l-4 border-amber-500 bg-amber-50 p-4 text-xs text-amber-900">
                        <div class="flex items-center gap-1.5 font-bold uppercase mb-1 text-amber-800">
                            <span class="material-symbols-outlined text-[16px]">info</span>
                            Ralat / Koreksi Berita
                        </div>
                        @foreach ($post->corrections as $correction)
                            <p class="mt-1">
                                {{ $correction->summary }}
                                <span class="text-amber-700">({{ $correction->created_at?->toWib()->translatedFormat('d M Y, H:i') }} WIB)</span>
                            </p>
                        @endforeach
                    </div>
                @endif

                {{-- Tags Artikel --}}
                @if ($post->tags && $post->tags->isNotEmpty())
                    <div class="mt-8 pt-6 border-t border-slate-200">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">TAG TERKAIT:</h4>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('search', ['q' => $tag->name]) }}"
                                   class="rounded bg-slate-100 hover:bg-red-50 hover:text-red-600 px-3 py-1 text-xs font-semibold text-slate-700 transition">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Notice Hak Jawab & Dewan Pers --}}
                <div class="mt-8 rounded border border-slate-200 bg-slate-50 p-4 text-xs text-slate-500 leading-relaxed">
                    <p class="font-bold text-slate-800 mb-1">Pemberitahuan Kode Etik Jurnalistik &amp; Hak Jawab:</p>
                    <p>
                        Seluruh liputan Ruang Data tunduk pada Undang-Undang Pers Nomor 40 Tahun 1999 dan Pedoman Pemberitaan Media Siber. Pihak yang merasa memerlukan klarifikasi atau hak koreksi dapat menghubungi meja redaksi di <strong class="text-slate-700">redaksi@ruangdata.test</strong>.
                    </p>
                </div>

                {{-- ZONA APRESIASI (LIKE) & DISKUSI KOMENTAR PEMBACA --}}
                <section id="interaksi-artikel" class="mt-10 pt-8 border-t-2 border-black">
                    {{-- 1. Bar Tombol Like & Counter Diskusi --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50/70 p-5 mb-8">
                        <div>
                            <h3 class="font-serif text-base sm:text-lg font-bold text-slate-900">
                                Bagaimana pendapat Anda tentang laporan ini?
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Berikan apresiasi tanda suka atau sampaikan tanggapan Anda di ruang diskusi pembaca.
                            </p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            @auth
                                <button type="button"
                                        onclick="togglePostLike(this, '{{ route('posts.like', $post) }}')"
                                        data-like-btn
                                        class="inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-xs font-extrabold uppercase tracking-wider shadow-xs transition cursor-pointer {{ ($hasLiked ?? false) ? 'bg-[#e50914] text-white' : 'bg-white border border-slate-300 text-slate-800 hover:border-[#e50914] hover:text-[#e50914]' }}">
                                    <span class="material-symbols-outlined text-[18px]" data-like-icon style="font-variation-settings: 'FILL' {{ ($hasLiked ?? false) ? 1 : 0 }}">favorite</span>
                                    <span data-like-label>{{ ($hasLiked ?? false) ? 'Disukai' : 'Suka Berita Ini' }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-black/10" data-like-count>{{ number_format($likesCount ?? 0) }}</span>
                                </button>
                            @else
                                <button type="button"
                                        onclick="openAuthModal('login')"
                                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white hover:border-[#e50914] hover:text-[#e50914] px-5 py-2.5 text-xs font-extrabold uppercase tracking-wider text-slate-800 shadow-xs transition cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px] text-[#e50914]">favorite</span>
                                    <span>Suka Berita Ini</span>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-100 text-slate-700" data-like-count>{{ number_format($likesCount ?? 0) }}</span>
                                </button>
                            @endauth

                            <a href="#komentar-pembaca"
                               class="inline-flex items-center gap-2 rounded-lg bg-[#111317] hover:bg-slate-800 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition">
                                <span class="material-symbols-outlined text-[17px]">forum</span>
                                <span>{{ number_format(isset($comments) ? $comments->count() : 0) }} Komentar</span>
                            </a>
                        </div>
                    </div>

                    {{-- 2. Ruang Komentar Pembaca --}}
                    <div id="komentar-pembaca" class="scroll-mt-20">
                        <div class="flex items-center justify-between pb-3 mb-6 border-b border-slate-200">
                            <h3 class="text-base sm:text-lg font-bold uppercase tracking-wider text-black flex items-center gap-2">
                                KOMENTAR <span class="text-red-600">PEMBACA</span>
                                <span class="ml-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                                    {{ number_format(isset($comments) ? $comments->count() : 0) }}
                                </span>
                            </h3>
                            <span class="text-[11px] font-semibold text-slate-400">
                                Diskusi Publik Terverifikasi
                            </span>
                        </div>

                        {{-- Form Tulis Komentar (Jika Sudah Login vs Belum Login) --}}
                        @auth
                            <form action="{{ route('posts.comments.store', $post) }}" method="POST" class="mb-8 rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
                                @csrf
                                <div class="flex items-start gap-3.5">
                                    @if (auth()->user()->avatarUrl())
                                        <img src="{{ auth()->user()->avatarUrl() }}"
                                             alt="{{ auth()->user()->displayName() }}"
                                             class="h-11 w-11 rounded-full object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="h-11 w-11 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ auth()->user()->initials() }}
                                        </div>
                                    @endif

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-slate-900">{{ auth()->user()->displayName() }}</span>
                                                <span class="rounded bg-emerald-50 border border-emerald-200 px-2 py-0.2 text-[10px] font-bold text-emerald-700">
                                                    {{ auth()->user()->is_crew ? 'Kru Redaksi' : 'Pembaca Terdaftar' }}
                                                </span>
                                            </div>
                                            <button type="button"
                                                    onclick="openProfileModal()"
                                                    class="text-[11px] font-semibold text-red-600 hover:underline cursor-pointer">
                                                Ubah Foto Profil
                                            </button>
                                        </div>

                                        <textarea name="body"
                                                  rows="3"
                                                  required
                                                  minlength="2"
                                                  maxlength="1500"
                                                  placeholder="Tulis tanggapan, masukan, atau analisis Anda secara santun..."
                                                  class="w-full rounded-lg border border-slate-300 bg-slate-50/60 p-3 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:border-[#e50914] focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600/15 transition"></textarea>

                                        <div class="mt-3 flex items-center justify-between gap-3">
                                            <span class="text-[11px] text-slate-400">
                                                Komentar menggunakan nama dan foto profil akun Anda.
                                            </span>
                                            <button type="submit"
                                                    class="rounded-lg bg-[#e50914] hover:bg-red-700 px-5 py-2 text-xs font-extrabold uppercase tracking-wider text-white shadow-xs transition cursor-pointer">
                                                Kirim Komentar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="mb-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                                <span class="material-symbols-outlined text-[28px] text-slate-400 mb-1">lock_person</span>
                                <h4 class="font-serif text-base font-bold text-slate-900">
                                    Ingin Menyukai atau Mengomentari Berita Ini?
                                </h4>
                                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-4 leading-relaxed">
                                    Silakan masuk ke akun Anda atau daftar sebagai pembaca baru dengan foto profil asli agar diskusi tetap sehat dan tepercaya.
                                </p>
                                <div class="flex items-center justify-center gap-3">
                                    <button type="button"
                                            onclick="openAuthModal('login')"
                                            class="rounded-lg bg-[#111317] hover:bg-slate-800 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition cursor-pointer">
                                        Masuk Akun
                                    </button>
                                    <button type="button"
                                            onclick="openAuthModal('register')"
                                            class="rounded-lg bg-[#e50914] hover:bg-red-700 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition cursor-pointer">
                                        Daftar Pembaca Baru
                                    </button>
                                </div>
                            </div>
                        @endauth

                        {{-- Daftar Komentar yang Sudah Masuk --}}
                        <div class="space-y-4">
                            @forelse ($comments ?? [] as $comment)
                                <article class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5 shadow-2xs">
                                    <div class="flex items-start gap-3.5">
                                        @if ($comment->user?->avatarUrl())
                                            <img src="{{ $comment->user->avatarUrl() }}"
                                                 alt="{{ $comment->authorLabel() }}"
                                                 class="h-10 w-10 rounded-full object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                                {{ $comment->user?->initials() ?? 'P' }}
                                            </div>
                                        @endif

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h5 class="text-xs sm:text-sm font-bold text-slate-900">
                                                        {{ $comment->authorLabel() }}
                                                    </h5>
                                                    @if ($comment->user?->is_crew)
                                                        <span class="rounded bg-red-50 border border-red-200 px-2 py-0.2 text-[10px] font-bold text-red-700 uppercase">
                                                            Redaksi
                                                        </span>
                                                    @else
                                                        <span class="rounded bg-slate-100 px-2 py-0.2 text-[10px] font-semibold text-slate-600">
                                                            Pembaca
                                                        </span>
                                                    @endif
                                                    <span class="text-[11px] text-slate-400">
                                                        &bull; {{ $comment->created_at?->diffForHumans() }}
                                                    </span>
                                                </div>

                                                @auth
                                                    @if (auth()->id() === $comment->user_id || auth()->user()->is_crew)
                                                        <form action="{{ route('posts.comments.destroy', $comment) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Hapus komentar ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="text-[11px] font-bold text-rose-600 hover:underline cursor-pointer">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endauth
                                            </div>

                                            <p class="mt-2 text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                                                {{ $comment->body }}
                                            </p>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-lg border border-slate-100 bg-slate-50/60 py-8 text-center text-xs text-slate-400">
                                    Belum ada komentar pada berita ini. Jadilah pembaca pertama yang memberikan tanggapan!
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </article>
        </main>
    </div>

    <script>
    async function togglePostLike(btn, url) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (response.status === 401) {
                openAuthModal('login');
                return;
            }

            if (!response.ok) return;
            const data = await response.json();

            document.querySelectorAll('[data-like-count]').forEach(el => {
                el.textContent = data.likes_count;
            });

            document.querySelectorAll('[data-like-btn]').forEach(button => {
                const icon = button.querySelector('[data-like-icon]');
                const label = button.querySelector('[data-like-label]');
                if (data.liked) {
                    button.classList.add('bg-[#e50914]', 'text-white');
                    button.classList.remove('bg-slate-100', 'bg-white', 'text-slate-700', 'text-slate-800', 'border-slate-300');
                    if (icon) icon.style.fontVariationSettings = "'FILL' 1";
                    if (label) label.textContent = 'Disukai';
                } else {
                    button.classList.remove('bg-[#e50914]', 'text-white');
                    button.classList.add('bg-white', 'border', 'border-slate-300', 'text-slate-800');
                    if (icon) icon.style.fontVariationSettings = "'FILL' 0";
                    if (label) label.textContent = 'Suka Berita Ini';
                }
            });
        } catch (e) {}
    }
    </script>

    {{-- BERITA TERKAIT / FEATURED READS DI BAGIAN BAWAH --}}
    @if ($relatedPosts->isNotEmpty())
        <section class="mt-16 pt-10 border-t-2 border-black">
            <div class="flex items-center justify-between pb-3 mb-6 border-b border-slate-200">
                <h3 class="text-lg sm:text-xl font-bold uppercase tracking-wider text-black">
                    FEATURED <span class="text-red-600">NEWS</span>
                </h3>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Kanal {{ $post->category->name }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($relatedPosts as $relPost)
                    <article class="group flex flex-col justify-between">
                        <div>
                            <div class="relative aspect-[16/10] w-full overflow-hidden rounded bg-slate-100">
                                <a href="{{ route('posts.show', [$relPost->category->slug, $relPost->slug]) }}">
                                    <img src="{{ $relPost->featuredMedia?->url() ?? 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=600&q=80' }}"
                                         alt="{{ $relPost->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                         loading="lazy">
                                </a>
                                <span class="absolute bottom-2.5 left-2.5 px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider text-white shadow"
                                      style="background-color: {{ $relPost->category?->safeBadgeColor() ?? '#dc2626' }}">
                                    {{ $relPost->category->name }}
                                </span>
                            </div>

                            <h4 class="font-serif font-bold text-sm sm:text-base text-slate-900 group-hover:text-red-600 transition leading-snug line-clamp-2 mt-3">
                                <a href="{{ route('posts.show', [$relPost->category->slug, $relPost->slug]) }}">
                                    {{ $relPost->title }}
                                </a>
                            </h4>

                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mt-1">
                                {{ $relPost->excerpt }}
                            </p>
                        </div>

                        <div class="text-[11px] text-slate-400 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                            <span>{{ $relPost->published_at?->translatedFormat('d M Y') }}</span>
                            <span class="font-medium text-slate-500">5 min read</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection