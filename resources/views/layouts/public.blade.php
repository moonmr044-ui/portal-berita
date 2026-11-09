<!DOCTYPE html>
{{-- File: resources/views/layouts/public.blade.php --}}
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Ruang Data') - Independent Journalism &amp; Data Perspectives</title>
    <meta name="description" content="@yield('meta_description', 'Ruang Data: Portal berita data dan jurnalisme independen terkini, menyajikan investigasi, analisis mendalam, sains, teknologi, dan kebijakan publik.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    @hasSection('noindex')
        <meta name="robots" content="noindex, nofollow">
    @endif

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('title', 'Ruang Data')">
    <meta property="og:description" content="@yield('meta_description', 'Ruang Data: Portal berita data dan jurnalisme independen terpercaya.')">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif

    {{-- Editorial Classic Fonts: Playfair Display + Inter --}}
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                        masthead: ['Cinzel', 'Playfair Display', 'serif'],
                    },
                    colors: {
                        brand: {
                            red: '#e50914',
                            crimson: '#dc2626',
                            dark: '#0f172a',
                            muted: '#64748b'
                        }
                    }
                }
            }
        };
    </script>

    <style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #dc2626;
        }
        .prose-editorial p:first-of-type::first-letter {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            line-height: 1;
            float: left;
            margin-right: 0.75rem;
            margin-top: 0.15rem;
            font-weight: 700;
            color: #0f172a;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-white font-sans text-slate-800 antialiased selection:bg-red-600 selection:text-white">

    {{-- UNIFIED TOP EDITORIAL WRAPPER (AMBIENT BLURRED BACKDROP + UTILITY BAR + MASTHEAD + NAVBAR + FRAMED HERO) --}}
    <div class="relative w-full overflow-hidden bg-[#141518]">
        {{-- Ambient Blurred Background Layer (Identical to The Times of Global Reference) --}}
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <img src="@yield('ambient_image', asset('images/hero-editorial.jpg'))"
                 onerror="this.onerror=null;this.src='{{ asset('images/hero-editorial.jpg') }}';"
                 alt=""
                 aria-hidden="true"
                 class="w-full h-full object-cover object-center scale-110 blur-[14px] brightness-[0.38] contrast-105 saturate-110">
            <div class="absolute inset-0 bg-gradient-to-b from-black/65 via-black/30 to-black/55"></div>
        </div>

        {{-- 1. TOP UTILITY BAR --}}
        <div class="relative z-10 bg-black/35 border-b border-white/10 text-[10.5px] sm:text-[11px] text-white/80">
            <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-2 flex items-center justify-between gap-2 whitespace-nowrap">
                {{-- Left: Date & Weather --}}
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 overflow-hidden">
                    <span class="inline-flex items-center gap-1.5 text-white/85 shrink-0">
                        <svg class="w-3.5 h-3.5 text-[#e50914] shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10z"/>
                        </svg>
                        <span>{{ now()->translatedFormat('l, d M Y') }}</span>
                    </span>
                    <span class="text-white/20">|</span>
                    <span class="inline-flex items-center gap-1 text-white/80 truncate">
                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6.76 4.84l-1.8-1.79-1.41 1.41 1.79 1.79 1.42-1.41zM4 10.5H1v2h3v-2zm9-9.95h-2V3.5h2V.55zm7.45 3.91l-1.41-1.41-1.79 1.79 1.41 1.41 1.79-1.79zm-3.21 13.7l1.79 1.8 1.41-1.41-1.8-1.79-1.4 1.4zM20 10.5v2h3v-2h-3zm-8-5c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6z"/>
                        </svg>
                        <span class="sm:hidden">Jakarta 29°C</span>
                        <span class="hidden sm:inline">Jakarta 29°C / Surabaya 31°C</span>
                    </span>
                </div>

                {{-- Right: Social SVG Icons, Search, Account --}}
                <div class="flex items-center gap-2.5 sm:gap-3.5 shrink-0">
                    <div class="hidden md:flex items-center gap-3 text-white/75">
                        <a href="#" class="hover:text-white transition" title="Facebook" aria-label="Facebook">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.326-.043-1.557-.14-2.857-.14C11.928 2 10 3.657 10 6.7v2.8H7v4h3V22h4v-8.5z"/></svg>
                        </a>
                        <a href="#" class="hover:text-white transition" title="Instagram" aria-label="Instagram">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="hover:text-white transition" title="YouTube" aria-label="YouTube">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    </div>

                    <span class="text-white/20 hidden md:inline">|</span>

                    {{-- Search Icon --}}
                    <a href="{{ route('search') }}" class="text-white/80 hover:text-white transition flex items-center" title="Cari Berita (Tekan '/')">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                        </svg>
                    </a>

                    {{-- Auth / Crew Controls --}}
                    @auth
                        @if (auth()->user()->is_crew)
                            <a href="{{ route('newsroom.dashboard') }}"
                               class="px-2 py-0.5 bg-[#e50914] hover:bg-red-700 text-white rounded-[2px] text-[9.5px] font-bold uppercase tracking-wider transition">
                                Newsroom
                            </a>
                        @endif
                        <button type="button"
                                onclick="openProfileModal()"
                                class="inline-flex items-center gap-1.5 text-white/90 hover:text-white transition cursor-pointer"
                                title="Profil Saya">
                            @if (auth()->user()->avatarUrl())
                                <img src="{{ auth()->user()->avatarUrl() }}"
                                     alt="{{ auth()->user()->displayName() }}"
                                     class="w-5 h-5 rounded-full object-cover border border-white/40">
                            @else
                                <span class="w-5 h-5 rounded-full bg-[#e50914] text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ auth()->user()->initials() }}
                                </span>
                            @endif
                            <span class="hidden sm:inline font-medium max-w-[120px] truncate">{{ auth()->user()->displayName() }}</span>
                        </button>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-white/60 hover:text-white transition cursor-pointer" title="Keluar">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                           onclick="event.preventDefault(); openAuthModal('login');"
                           class="inline-flex items-center gap-1 text-white/85 hover:text-white transition cursor-pointer"
                           title="Masuk / Daftar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path stroke-linecap="round" d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/>
                            </svg>
                            <span class="hidden sm:inline">Masuk</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- 2. MASTHEAD & NAVIGATION BAR (OVER AMBIENT BLURRED PHOTO) --}}
        <header class="relative z-10">
            {{-- Masthead Title --}}
            <div class="border-b border-white/10 py-4 sm:py-7 text-center px-3 sm:px-4">
                <a href="{{ route('home') }}" class="inline-block group max-w-full">
                    <h1 class="font-serif text-[28px] sm:text-5xl md:text-[54px] font-bold tracking-[0.11em] sm:tracking-[0.15em] text-white uppercase leading-none drop-shadow-sm group-hover:text-white/90 transition">
                        RUANG DATA
                    </h1>
                    <p class="font-sans text-[8px] sm:text-[10.5px] tracking-[0.16em] sm:tracking-[0.32em] text-white/65 font-medium uppercase mt-2 sm:mt-2.5 truncate">
                        INDEPENDENT JOURNALISM &bull; DATA PERSPECTIVES
                    </p>
                </a>
            </div>

            {{-- Responsive Navigation Links (Scrollable from left on mobile, centered on desktop) --}}
            <nav class="max-w-7xl mx-auto overflow-x-auto no-scrollbar">
                <div class="flex items-center justify-start md:justify-center gap-5 sm:gap-8 w-max min-w-full px-4 sm:px-6 lg:px-8 py-3 sm:py-3.5 text-[11px] sm:text-xs font-bold uppercase tracking-[0.1em] sm:tracking-[0.12em] whitespace-nowrap">
                    <a href="{{ route('home') }}"
                       class="transition-colors shrink-0 {{ request()->routeIs('home') ? 'text-[#ef233c]' : 'text-white/90 hover:text-[#ef233c]' }}">
                        HOME
                    </a>
                    @foreach ($navCategories ?? [] as $navCat)
                        @php
                            $isActive = request()->is('kategori/'.$navCat->slug) || request()->is($navCat->slug.'*');
                        @endphp
                        <a href="{{ route('categories.show', $navCat->slug) }}"
                           class="transition-colors shrink-0 {{ $isActive ? 'text-[#ef233c]' : 'text-white/90 hover:text-[#ef233c]' }}">
                            {{ $navCat->name }}
                        </a>
                    @endforeach
                </div>
            </nav>
        </header>

        {{-- 3. FRAMED HERO SLOT (RENDERS INSIDE AMBIENT BACKDROP ON HOMEPAGE) --}}
        @hasSection('hero')
            <div class="relative z-10">
                @yield('hero')
            </div>
        @endif
    </div>

    {{-- MAIN WHITE PAGE CONTENT --}}
    @if (session('status'))
        <div class="bg-emerald-50 border-b border-emerald-200 px-4 py-2.5 text-xs font-semibold text-emerald-900">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-2">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600">check_circle</span>
                    {{ session('status') }}
                </span>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 font-bold cursor-pointer">&times;</button>
            </div>
        </div>
    @endif
    <main id="konten-utama" class="min-h-screen bg-white">
        @yield('content')
    </main>

    {{-- EDITORIAL FOOTER (THE TIMES OF GLOBAL STYLE) --}}
    <footer class="bg-[#0b0c0e] text-slate-300 pt-12 pb-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-10 pb-12 border-b border-white/10">
                {{-- Column 1: ABOUT US --}}
                <div class="lg:col-span-3 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-white">ABOUT US</h4>
                    <a href="{{ route('home') }}" class="block font-serif text-xl sm:text-2xl font-black text-white tracking-wider">
                        RUANG DATA
                    </a>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Portal berita independen menyajikan berita tepercaya, akurat, dan mendalam dengan basis analisis data di seluruh dunia.
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-red-600 text-white flex items-center justify-center transition text-xs font-bold">f</a>
                        <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-red-600 text-white flex items-center justify-center transition text-xs font-bold">&Xopf;</a>
                        <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-red-600 text-white flex items-center justify-center transition text-xs font-bold">in</a>
                        <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-red-600 text-white flex items-center justify-center transition text-xs font-bold">&#9654;</a>
                    </div>
                </div>

                {{-- Column 2: NEWS CATEGORIES --}}
                <div class="lg:col-span-3 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-white">NEWS CATEGORIES</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        @foreach ($navCategories ?? [] as $cat)
                            <li>
                                <a href="{{ route('categories.show', $cat->slug) }}" class="hover:text-white transition">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                        <li><a href="{{ route('search') }}" class="hover:text-white transition">Indeks &amp; Arsip Berita</a></li>
                    </ul>
                </div>

                {{-- Column 3: PEDOMAN & REDAKSI --}}
                <div class="lg:col-span-3 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-white">PEDOMAN &amp; REDAKSI</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#" class="hover:text-white transition">Pedoman Pemberitaan Media Siber</a></li>
                        <li><a href="#" class="hover:text-white transition">Kode Etik Jurnalistik (KEJ)</a></li>
                        <li><a href="#" class="hover:text-white transition">Hak Jawab &amp; Mekanisme Ralat</a></li>
                        <li><a href="#" class="hover:text-white transition">Standar Perlindungan Wartawan</a></li>
                        <li><span class="text-slate-500">Redaksi:</span> <span class="text-slate-300">redaksi@ruangdata.test</span></li>
                    </ul>
                </div>

                {{-- Column 4: NEWSLETTER --}}
                <div class="lg:col-span-3 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-white">NEWSLETTER</h4>
                    <p class="font-serif text-sm font-bold text-white tracking-wide">
                        SUBSCRIBE TO OUR NEWSLETTER
                    </p>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Mendapatkan berita harian terbaru and be STAY UPDATED.
                    </p>
                    <form onsubmit="event.preventDefault(); alert('Terima kasih telah berlangganan newsletter Ruang Data!');" class="space-y-2 pt-1">
                        <input type="email" required placeholder="Enter your email address"
                               class="w-full bg-[#16181d] border border-white/10 rounded px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-red-600 transition">
                        <button type="submit"
                                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider py-2.5 rounded transition shadow-md cursor-pointer">
                            SUBSCRIBE
                        </button>
                    </form>
                </div>
            </div>

            {{-- BOTTOM BAR --}}
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-500">
                <p>&copy; {{ now()->year }} Ruang Data. All rights reserved.</p>
                <div class="flex items-center gap-4 flex-wrap">
                    <a href="#" class="hover:text-slate-300 transition">Terms of Service</a>
                    <a href="#" class="hover:text-slate-300 transition">Privacy Policy</a>
                    <a href="#" class="hover:text-slate-300 transition">Cookie Settings</a>
                    <a href="#" class="hover:text-slate-300 transition">Contact Us</a>
                    <a href="#" class="hover:text-slate-300 transition">Advertise with Us</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- GLOBAL AUTH POPUP MODAL (MASUK & DAFTAR AKUN PEMBACA) --}}
    @guest
        @php
            $isAuthPage = request()->routeIs('login', 'register');
            $hasAuthErrors = isset($errors) && $errors->any();
            $initialAuthTab = (request()->routeIs('register') || old('name')) ? 'register' : 'login';
            $shouldAutoOpenModal = $isAuthPage || $hasAuthErrors;
        @endphp
        <div id="auth-popup-modal"
             role="dialog"
             aria-modal="true"
             aria-labelledby="auth-modal-title"
             class="fixed inset-0 z-[100] {{ $shouldAutoOpenModal ? 'flex' : 'hidden' }} items-center justify-center p-4 sm:p-6 bg-black/75 backdrop-blur-xs overflow-y-auto"
             onclick="if (event.target === this) closeAuthModal();">

            <div class="relative w-full max-w-[430px] rounded-2xl bg-white shadow-[0_28px_80px_rgba(0,0,0,0.65)] overflow-hidden border border-white/15 my-auto animate-fadeIn">

                {{-- Top Editorial Masthead Header inside Popup --}}
                <div class="relative bg-[#111317] text-white px-6 pt-6 pb-5 text-center border-b border-white/10">
                    <div class="absolute inset-x-0 top-0 h-1 bg-[#e50914]"></div>

                    <button type="button"
                            onclick="closeAuthModal()"
                            aria-label="Tutup popup"
                            class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full bg-white/10 hover:bg-[#e50914] text-white/80 hover:text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <span class="inline-block px-2.5 py-0.5 bg-[#e50914]/20 border border-[#e50914]/40 text-red-400 text-[9.5px] font-extrabold uppercase tracking-[0.18em] rounded-full mb-2">
                        PORTAL AKSES RESMI
                    </span>
                    <h2 id="auth-modal-title" class="font-serif text-2xl font-bold tracking-[0.14em] text-white uppercase leading-none">
                        RUANG DATA
                    </h2>
                    <p class="text-[10px] tracking-[0.2em] text-white/60 uppercase mt-1.5">
                        INDEPENDENT JOURNALISM &bull; DATA PERSPECTIVES
                    </p>
                </div>

                {{-- Tab Switcher: Masuk | Daftar Akun Pembaca --}}
                <div class="grid grid-cols-2 border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider">
                    <button type="button"
                            id="tab-btn-login"
                            onclick="switchAuthTab('login')"
                            class="py-3.5 text-center border-b-2 transition cursor-pointer {{ $initialAuthTab === 'login' ? 'border-[#e50914] text-black bg-white' : 'border-transparent text-slate-400 hover:text-slate-700' }}">
                        Masuk
                    </button>
                    <button type="button"
                            id="tab-btn-register"
                            onclick="switchAuthTab('register')"
                            class="py-3.5 text-center border-b-2 transition cursor-pointer {{ $initialAuthTab === 'register' ? 'border-[#e50914] text-black bg-white' : 'border-transparent text-slate-400 hover:text-slate-700' }}">
                        Daftar Akun Pembaca
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6">
                    @if ($hasAuthErrors)
                        <div role="alert" class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-900">
                            <div class="font-bold text-rose-700 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px]">error</span>
                                Mohon periksa kembali:
                            </div>
                            <ul class="list-inside list-disc space-y-0.5 text-rose-800">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- PANEL 1: FORM MASUK --}}
                    <div id="auth-panel-login" class="{{ $initialAuthTab === 'login' ? '' : 'hidden' }}">
                        <p class="mb-4 text-xs text-slate-500 leading-relaxed">
                            Masuk ke akun pembaca atau dapur redaksi. Kru redaksi wajib menyelesaikan verifikasi OTP dua langkah.
                        </p>

                        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label for="modal-login-email" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                                    Alamat Email
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined text-[17px] text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">mail</span>
                                    <input id="modal-login-email"
                                           name="email"
                                           type="email"
                                           required
                                           autocomplete="username"
                                           value="{{ old('email') }}"
                                           placeholder="nama@email.com"
                                           class="w-full rounded-lg border border-slate-300 bg-slate-50/70 pl-9 pr-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:border-[#e50914] focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600/15 transition">
                                </div>
                            </div>

                            <div>
                                <label for="modal-login-password" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                                    Kata Sandi
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined text-[17px] text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">lock</span>
                                    <input id="modal-login-password"
                                           name="password"
                                           type="password"
                                           required
                                           autocomplete="current-password"
                                           placeholder="Masukkan kata sandi"
                                           class="w-full rounded-lg border border-slate-300 bg-slate-50/70 pl-9 pr-10 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:border-[#e50914] focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600/15 transition">
                                    <button type="button"
                                            onclick="togglePasswordField('modal-login-password', this)"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 cursor-pointer p-1"
                                            title="Tampilkan/Sembunyikan Sandi">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-0.5">
                                <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                                    <input type="checkbox" name="remember" value="1" class="h-3.5 w-3.5 rounded border-slate-300 accent-[#e50914]">
                                    <span>Ingat saya di perangkat ini</span>
                                </label>
                            </div>

                            <button type="submit"
                                    class="w-full rounded-lg bg-[#111317] hover:bg-[#e50914] px-4 py-3 text-xs font-extrabold uppercase tracking-widest text-white shadow-md transition duration-200 cursor-pointer">
                                Masuk Sekarang
                            </button>
                        </form>

                        <div class="mt-5 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                            Belum punya akun pembaca?
                            <button type="button"
                                    onclick="switchAuthTab('register')"
                                    class="font-bold text-[#e50914] hover:underline ml-1 cursor-pointer">
                                Daftar di sini
                            </button>
                        </div>
                    </div>

                    {{-- PANEL 2: FORM DAFTAR AKUN PEMBACA --}}
                    <div id="auth-panel-register" class="{{ $initialAuthTab === 'register' ? '' : 'hidden' }}">
                        <p class="mb-3.5 text-xs text-slate-500 leading-relaxed">
                            Buat akun pembaca gratis untuk menyukai berita &amp; berkomentar. Wajib menyertakan foto profil asli agar diskusi tetap kredibel.
                        </p>

                        <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf

                            {{-- Upload Foto Profil Pembaca dengan Preview Bulat --}}
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 flex items-center gap-3.5">
                                <div class="relative h-14 w-14 rounded-full bg-slate-900 text-white flex items-center justify-center overflow-hidden shrink-0 border-2 border-white shadow-sm">
                                    <img id="register-avatar-preview" src="" alt="" class="hidden h-full w-full object-cover">
                                    <span id="register-avatar-placeholder" class="material-symbols-outlined text-[24px] text-white/80">add_a_photo</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="modal-register-avatar" class="block text-[11px] font-bold uppercase tracking-wider text-slate-800 mb-1">
                                        Foto Profil Pembaca <span class="text-[#e50914]">*</span>
                                    </label>
                                    <input id="modal-register-avatar"
                                           name="avatar"
                                           type="file"
                                           required
                                           accept="image/jpeg,image/png,image/webp"
                                           onchange="previewRegisterAvatar(this)"
                                           class="block w-full text-[11px] text-slate-600 file:mr-2.5 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-bold file:bg-black file:text-white hover:file:bg-[#e50914] file:cursor-pointer cursor-pointer">
                                    <p class="mt-0.5 text-[10px] text-slate-400">Format JPG, PNG, atau WebP (Maks. 4 MB)</p>
                                </div>
                            </div>

                            <div>
                                <label for="modal-register-name" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                                    Nama Lengkap
                                </label>
                                <input id="modal-register-name"
                                       name="name"
                                       type="text"
                                       required
                                       autocomplete="name"
                                       value="{{ old('name') }}"
                                       placeholder="Nama lengkap Anda"
                                       class="w-full rounded-lg border border-slate-300 bg-slate-50/70 px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-[#e50914] focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600/15 transition">
                            </div>

                            <div>
                                <label for="modal-register-email" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                                    Alamat Email
                                </label>
                                <input id="modal-register-email"
                                       name="email"
                                       type="email"
                                       required
                                       autocomplete="username"
                                       value="{{ old('email') }}"
                                       placeholder="nama@email.com"
                                       class="w-full rounded-lg border border-slate-300 bg-slate-50/70 px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-[#e50914] focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600/15 transition">
                            </div>

                            <div>
                                <label for="modal-register-password" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                                    Kata Sandi
                                </label>
                                <input id="modal-register-password"
                                       name="password"
                                       type="password"
                                       required
                                       autocomplete="new-password"
                                       placeholder="Minimal 8 karakter"
                                       class="w-full rounded-lg border border-slate-300 bg-slate-50/70 px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-[#e50914] focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600/15 transition">
                            </div>

                            <div>
                                <label for="modal-register-password-confirm" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-700">
                                    Ulangi Kata Sandi
                                </label>
                                <input id="modal-register-password-confirm"
                                       name="password_confirmation"
                                       type="password"
                                       required
                                       autocomplete="new-password"
                                       placeholder="Ketik ulang kata sandi"
                                       class="w-full rounded-lg border border-slate-300 bg-slate-50/70 px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-[#e50914] focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600/15 transition">
                            </div>

                            <label class="flex items-start gap-2 text-xs text-slate-600 pt-1 cursor-pointer">
                                <input type="checkbox" name="terms" value="1" required class="mt-0.5 h-3.5 w-3.5 rounded border-slate-300 accent-[#e50914]">
                                <span>
                                    Saya menyetujui <span class="underline font-semibold text-slate-800">syarat ketentuan</span> &amp; <span class="underline font-semibold text-slate-800">pedoman komunitas</span> Ruang Data.
                                </span>
                            </label>

                            <button type="submit"
                                    class="w-full rounded-lg bg-[#e50914] hover:bg-red-700 px-4 py-3 text-xs font-extrabold uppercase tracking-widest text-white shadow-md transition duration-200 cursor-pointer">
                                Daftar Akun Pembaca
                            </button>
                        </form>

                        <div class="mt-5 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                            Sudah punya akun?
                            <button type="button"
                                    onclick="switchAuthTab('login')"
                                    class="font-bold text-slate-900 hover:text-[#e50914] hover:underline ml-1 cursor-pointer">
                                Masuk di sini
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- POPUP MODAL PROFIL PENGGUNA (UBAH FOTO PROFIL & NAMA) --}}
        <div id="profile-popup-modal"
             role="dialog"
             aria-modal="true"
             class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/75 backdrop-blur-xs overflow-y-auto"
             onclick="if (event.target === this) closeProfileModal();">
            <div class="relative w-full max-w-[400px] rounded-2xl bg-white shadow-2xl overflow-hidden border border-white/15 my-auto">
                <div class="relative bg-[#111317] text-white px-6 pt-5 pb-4 text-center border-b border-white/10">
                    <div class="absolute inset-x-0 top-0 h-1 bg-[#e50914]"></div>
                    <button type="button"
                            onclick="closeProfileModal()"
                            class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/10 hover:bg-[#e50914] text-white flex items-center justify-center transition cursor-pointer">
                        &times;
                    </button>
                    <h3 class="font-serif text-lg font-bold tracking-wider uppercase">Profil Pembaca</h3>
                    <p class="text-[11px] text-white/60">{{ auth()->user()->email }}</p>
                </div>

                <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div class="flex flex-col items-center gap-2">
                        @if (auth()->user()->avatarUrl())
                            <img src="{{ auth()->user()->avatarUrl() }}"
                                 alt="{{ auth()->user()->displayName() }}"
                                 class="w-20 h-20 rounded-full object-cover border-2 border-slate-200 shadow-sm">
                        @else
                            <div class="w-20 h-20 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-2xl">
                                {{ auth()->user()->initials() }}
                            </div>
                        @endif
                        <label class="text-xs font-bold text-[#e50914] hover:underline cursor-pointer">
                            <span>Ganti Foto Profil</span>
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="this.form.requestSubmit()">
                        </label>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase text-slate-700 mb-1">Nama Tampilan</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" required
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-xs text-slate-900 focus:border-[#e50914] focus:outline-none">
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-[#111317] hover:bg-[#e50914] py-2.5 text-xs font-bold uppercase tracking-wider text-white transition cursor-pointer">
                        Simpan Profil
                    </button>
                </form>
            </div>
        </div>
    @endguest

    {{-- Keyboard shortcut & Popup Modal Controller --}}
    <script>
        function openAuthModal(tab) {
            const modal = document.getElementById('auth-popup-modal');
            if (!modal) {
                window.location.href = tab === 'register' ? "{{ route('register') }}" : "{{ route('login') }}";
                return;
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            switchAuthTab(tab || 'login');
            setTimeout(() => {
                const firstInput = modal.querySelector(tab === 'register' ? '#modal-register-name' : '#modal-login-email');
                if (firstInput) firstInput.focus();
            }, 60);
        }

        function closeAuthModal() {
            const modal = document.getElementById('auth-popup-modal');
            if (!modal) return;
            @if (request()->routeIs('login', 'register'))
                window.location.href = "{{ route('home') }}";
            @else
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            @endif
        }

        function switchAuthTab(tab) {
            const loginPanel = document.getElementById('auth-panel-login');
            const registerPanel = document.getElementById('auth-panel-register');
            const loginBtn = document.getElementById('tab-btn-login');
            const registerBtn = document.getElementById('tab-btn-register');
            if (!loginPanel || !registerPanel) return;

            if (tab === 'register') {
                loginPanel.classList.add('hidden');
                registerPanel.classList.remove('hidden');
                registerBtn?.classList.add('border-[#e50914]', 'text-black', 'bg-white');
                registerBtn?.classList.remove('border-transparent', 'text-slate-400');
                loginBtn?.classList.remove('border-[#e50914]', 'text-black', 'bg-white');
                loginBtn?.classList.add('border-transparent', 'text-slate-400');
            } else {
                registerPanel.classList.add('hidden');
                loginPanel.classList.remove('hidden');
                loginBtn?.classList.add('border-[#e50914]', 'text-black', 'bg-white');
                loginBtn?.classList.remove('border-transparent', 'text-slate-400');
                registerBtn?.classList.remove('border-[#e50914]', 'text-black', 'bg-white');
                registerBtn?.classList.add('border-transparent', 'text-slate-400');
            }
        }

        function togglePasswordField(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = btn.querySelector('.material-symbols-outlined');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                if (icon) icon.textContent = 'visibility';
            }
        }

        function previewRegisterAvatar(input) {
            const preview = document.getElementById('register-avatar-preview');
            const placeholder = document.getElementById('register-avatar-placeholder');
            if (input.files && input.files[0] && preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder?.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openProfileModal() {
            const modal = document.getElementById('profile-popup-modal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeProfileModal() {
            const modal = document.getElementById('profile-popup-modal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('auth-popup-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeAuthModal();
                }
            }
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                window.location.href = "{{ route('search') }}";
            }
        });
    </script>
</body>
</html>