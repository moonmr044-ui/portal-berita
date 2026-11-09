{{-- File: resources/views/public/categories/show.blade.php --}}
@extends('layouts.public')

@section('title', 'Berita ' . $category->name . ' Terkini')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    {{-- Header Rubrik --}}
    <div class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <nav aria-label="Breadcrumb" class="mb-2 text-xs text-slate-500">
            <ol class="flex items-center gap-1.5">
                <li><a href="{{ route('home') }}" class="hover:text-rose-600 transition">Home</a></li>
                <li>&rsaquo;</li>
                <li class="font-bold text-slate-700 uppercase">Kategori</li>
            </ol>
        </nav>

        <div class="flex items-center gap-3">
            <span class="h-8 w-2 rounded-full badge-category" style="--badge-color: {{ $category->safeBadgeColor() }}"></span>
            <div>
                <h1 class="font-serif text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 uppercase">
                    {{ $category->name }}
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Kumpulan berita, liputan mendalam, dan analisis terkini seputar {{ strtolower($category->name) }}.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        {{-- Daftar Artikel Kategori (8 Kolom) --}}
        <div class="lg:col-span-8">
            <div class="space-y-4">
                @forelse ($posts as $post)
                    <article class="group flex flex-col sm:flex-row gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-xs hover:border-slate-300 hover:shadow-sm transition">
                        <div class="relative aspect-[4/3] w-full sm:w-48 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                            <img src="{{ $post->featuredMedia?->url() ?? 'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=600&q=80' }}"
                                 alt="{{ $post->title }}"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                 loading="lazy">
                        </div>

                        <div class="flex flex-1 flex-col justify-between">
                            <div>
                                <h2 class="font-serif text-base sm:text-lg font-bold text-slate-900 group-hover:text-[#bb0112] transition leading-snug">
                                    <a href="{{ route('posts.show', [$post->category->slug, $post->slug]) }}">
                                        {{ $post->title }}
                                    </a>
                                </h2>
                                <p class="mt-1.5 text-xs sm:text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                    {{ $post->excerpt }}
                                </p>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
                                <span>Reporter: <strong class="text-slate-600 font-medium">{{ $post->authorDisplayName() }}</strong></span>
                                <time datetime="{{ $post->published_at?->toISOString() }}">
                                    {{ $post->published_at?->diffForHumans() }}
                                </time>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
                        Belum ada berita di kategori {{ $category->name }} saat ini.
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        </div>

        {{-- Sidebar Terpopuler --}}
        <aside class="lg:col-span-4 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sticky top-6">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
                        <span class="h-3.5 w-1 inline-block" style="background-color: {{ $category->safeBadgeColor() }}"></span>
                        Terpopuler
                    </h3>
                    <span class="rounded px-2 py-0.5 text-[10px] font-bold" style="background-color: {{ $category->safeBadgeColor() }}1a; color: {{ $category->safeBadgeColor() }};">Top 5</span>
                </div>

                <div class="space-y-4">
                    @foreach ($popularPosts as $index => $popPost)
                        <article class="group flex items-start gap-3 pb-3 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-sm font-black text-white shadow-xs"
                                  style="background-color: {{ $category->safeBadgeColor() }};">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider" style="color: {{ $category->safeBadgeColor() }};">
                                    {{ $category->name }}
                                </span>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug group-hover:text-rose-600 transition">
                                    <a href="{{ route('posts.show', [$popPost->category->slug, $popPost->slug]) }}">
                                        {{ $popPost->title }}
                                    </a>
                                </h4>
                                <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
                                    <span>{{ number_format($popPost->views_count) }} views</span>
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
