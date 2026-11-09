<!DOCTYPE html>
{{-- File: resources/views/layouts/newsroom.blade.php --}}
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Newsroom Studio') &middot; Portal Berita Digital</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-slate-100 font-sans text-slate-900 antialiased">

    <a href="#konten-newsroom"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-4 focus:py-2">
        Lompat ke konten utama
    </a>

    <div class="flex min-h-full flex-col">
        <header class="border-b border-slate-200 bg-white sticky top-0 z-30 shadow-xs">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
                <div class="flex items-center gap-6">
                    <a href="{{ route('newsroom.dashboard') }}" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Ruang Data" class="h-8 w-8 object-contain">
                        <span class="font-black tracking-tight text-slate-900">
                            RUANG<span class="text-cyan-600">DATA</span> <span class="text-[11px] font-bold text-slate-400 ml-0.5">STUDIO</span>
                        </span>
                    </a>

                    <nav aria-label="Navigasi newsroom" class="hidden items-center gap-1 text-xs font-bold lg:flex">
                        <a href="{{ route('newsroom.dashboard') }}"
                           class="rounded-lg px-2.5 py-1.5 {{ request()->routeIs('newsroom.dashboard') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                            Dasbor
                        </a>
                        <a href="{{ route('newsroom.posts.index') }}"
                           class="rounded-lg px-2.5 py-1.5 {{ request()->routeIs('newsroom.posts.*') && ! request()->routeIs('newsroom.posts.create') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                            Naskah
                        </a>
                        @if (auth()->user()?->hasAnyRole(['super-admin', 'pemred']))
                            <a href="{{ route('newsroom.curation.index') }}"
                               class="rounded-lg px-2.5 py-1.5 {{ request()->routeIs('newsroom.curation.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                                Kurasi
                            </a>
                            <a href="{{ route('newsroom.breaking-news.index') }}"
                               class="rounded-lg px-2.5 py-1.5 {{ request()->routeIs('newsroom.breaking-news.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                                Breaking
                            </a>
                            <a href="{{ route('newsroom.categories.index') }}"
                               class="rounded-lg px-2.5 py-1.5 {{ request()->routeIs('newsroom.categories.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                                Rubrik
                            </a>
                            <a href="{{ route('newsroom.interactive.index') }}"
                               class="rounded-lg px-2.5 py-1.5 {{ request()->routeIs('newsroom.interactive.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                                Survei &amp; E-Paper
                            </a>
                        @endif
                        @if (auth()->user()?->hasRole('super-admin'))
                            <a href="{{ route('newsroom.users.index') }}"
                               class="rounded-lg px-2.5 py-1.5 {{ request()->routeIs('newsroom.users.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition">
                                Kru
                            </a>
                        @endif
                    </nav>
                </div>

                <div class="flex items-center gap-2.5 text-xs">
                    <a href="{{ route('newsroom.posts.create') }}"
                       class="rounded-lg bg-rose-600 px-3 py-1.5 text-white font-bold hover:bg-rose-700 transition flex items-center gap-1 shadow-xs">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tulis Berita
                    </a>
                    <span class="hidden text-slate-600 sm:inline font-semibold">{{ auth()->user()?->name }}</span>

                    @foreach (auth()->user()?->getRoleNames() ?? [] as $roleName)
                        <span class="rounded bg-rose-50 border border-rose-200 px-2 py-0.5 text-[11px] font-bold text-rose-700 uppercase">
                            {{ $roleName }}
                        </span>
                    @endforeach

                    <a href="{{ route('home') }}" target="_blank" class="rounded border border-slate-300 px-2 py-1 text-slate-700 hover:bg-slate-50 transition flex items-center gap-1 font-medium">
                        <span>Situs</span>
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded bg-slate-100 px-2 py-1 font-semibold text-slate-600 hover:bg-slate-200 transition">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div role="status" class="border-b border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-900">
                <div class="mx-auto max-w-7xl flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div role="alert" class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-900">
                <div class="mx-auto max-w-7xl">{{ session('warning') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="border-b border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-900">
                <div class="mx-auto max-w-7xl">{{ session('error') }}</div>
            </div>
        @endif

        <main id="konten-newsroom" class="mx-auto w-full max-w-7xl flex-1 px-4 py-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
