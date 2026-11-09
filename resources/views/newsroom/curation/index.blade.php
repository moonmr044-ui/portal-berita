{{-- File: resources/views/newsroom/curation/index.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Kurasi Hero Homepage')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Kurasi Hero Homepage</h1>
        <p class="text-xs text-slate-500 mt-0.5">
            Tentukan artikel sorotan utama yang tampil di bagian paling atas halaman depan web (1 Headline + 4 Pilihan Redaksi).
        </p>
    </div>

    <form method="POST" action="{{ route('newsroom.curation.update') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Slot Headline Utama (6 Kolom) --}}
            <div class="lg:col-span-6 space-y-4">
                <div class="rounded-xl border-2 border-rose-300 bg-white p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-rose-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-rose-600 text-white font-black text-xs">1</span>
                            <h2 class="text-sm font-black uppercase tracking-wider text-rose-700">Headline Utama (16:9 Besar)</h2>
                        </div>
                        <span class="rounded bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800">Sorotan Utama</span>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        Artikel ini akan tampil paling besar di sebelah kiri homepage pembaca dengan foto 16:9.
                    </p>

                    <div>
                        <label for="slot_headline" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Pilih Artikel Headline:
                        </label>
                        <select id="slot_headline"
                                name="slots[headline]"
                                class="w-full rounded-lg border border-slate-300 p-2.5 text-xs font-semibold text-slate-800 focus:border-rose-500 focus:outline-none">
                            <option value="">-- Fallback otomatis ke artikel terbaru --</option>
                            @foreach ($publishedPosts as $post)
                                <option value="{{ $post->id }}"
                                    {{ (isset($currentPicks['headline']) && $currentPicks['headline']->post_id === $post->id) ? 'selected' : '' }}>
                                    [{{ $post->category?->name }}] {{ $post->title }} ({{ $post->published_at?->diffForHumans() }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- 4 Editorial Picks (6 Kolom) --}}
            <div class="lg:col-span-6 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">4 Pilihan Redaksi (Grid 2x2)</h2>
                        <span class="text-[10px] text-slate-400 font-bold uppercase">Sisi Kanan Hero</span>
                    </div>

                    @foreach (['pick_1' => 'Pilihan Redaksi 1', 'pick_2' => 'Pilihan Redaksi 2', 'pick_3' => 'Pilihan Redaksi 3', 'pick_4' => 'Pilihan Redaksi 4'] as $slotKey => $slotLabel)
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                {{ $slotLabel }}:
                            </label>
                            <select name="slots[{{ $slotKey }}]"
                                    class="w-full rounded-lg border border-slate-300 p-2 text-xs text-slate-800 focus:border-rose-500 focus:outline-none">
                                <option value="">-- Fallback otomatis --</option>
                                @foreach ($publishedPosts as $post)
                                    <option value="{{ $post->id }}"
                                        {{ (isset($currentPicks[$slotKey]) && $currentPicks[$slotKey]->post_id === $post->id) ? 'selected' : '' }}>
                                        [{{ $post->category?->name }}] {{ $post->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('newsroom.dashboard') }}"
               class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="rounded-xl bg-rose-600 px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition">
                Simpan & Pasang di Homepage
            </button>
        </div>
    </form>

@endsection
