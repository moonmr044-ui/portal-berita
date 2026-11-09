{{-- File: resources/views/newsroom/categories/edit.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Sunting Rubrik: ' . $category->name)

@section('content')

    <div class="mb-6 max-w-xl mx-auto">
        <a href="{{ route('newsroom.categories.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition flex items-center gap-1 mb-2">
            &larr; Kembali ke Rubrik Kategori
        </a>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sunting Rubrik Berita</h1>
        <p class="text-xs text-slate-500">
            Perbarui nama rubrik, warna penanda badge, dan nomor urut tampil.
        </p>
    </div>

    <div class="max-w-xl mx-auto rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        @if (isset($errors) && $errors->any())
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('newsroom.categories.update', $category->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Rubrik</label>
                <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required
                       class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 space-y-3">
                <label class="block text-xs font-bold uppercase text-slate-700">Pilihan Warna Badge Rubrik</label>
                
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                    <input type="color" id="badge_picker" value="{{ old('badge_color', $category->badge_color) }}" 
                           class="h-10 w-14 rounded-lg cursor-pointer border border-slate-300 p-0.5 bg-white">
                    <input type="text" id="badge_color" name="badge_color" value="{{ old('badge_color', $category->badge_color) }}" required
                           class="w-32 rounded-lg border border-slate-300 p-2 text-xs font-mono font-bold text-slate-900 focus:border-rose-500 focus:outline-none bg-white">
                    
                    {{-- Live Preview --}}
                    <div class="flex items-center gap-2 ml-auto">
                        <span class="text-[11px] font-bold text-slate-400 uppercase">Live Preview:</span>
                        <span id="badge_preview" class="rounded px-3 py-1 text-xs font-black text-white uppercase tracking-wider shadow-xs" style="background-color: {{ old('badge_color', $category->badge_color) }}">
                            {{ old('name', $category->name) }}
                        </span>
                    </div>
                </div>

                {{-- Palet Pilihan Cepat --}}
                <div class="pt-1 border-t border-slate-200/60">
                    <p class="text-[11px] text-slate-500 mb-2 font-medium">Atau klik salah satu palet warna populer di bawah:</p>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <button type="button" data-color="#e11d48" style="background-color: #e11d48 !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Merah Marun"></button>
                        <button type="button" data-color="#2563eb" style="background-color: #2563eb !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Biru"></button>
                        <button type="button" data-color="#10b981" style="background-color: #10b981 !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Hijau"></button>
                        <button type="button" data-color="#0d9488" style="background-color: #0d9488 !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Teal / Toska"></button>
                        <button type="button" data-color="#f59e0b" style="background-color: #f59e0b !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Kuning Emas"></button>
                        <button type="button" data-color="#f97316" style="background-color: #f97316 !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Oranye"></button>
                        <button type="button" data-color="#8b5cf6" style="background-color: #8b5cf6 !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Ungu"></button>
                        <button type="button" data-color="#ec4899" style="background-color: #ec4899 !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Pink"></button>
                        <button type="button" data-color="#334155" style="background-color: #334155 !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Slate Abu-abu"></button>
                        <button type="button" data-color="#0284c7" style="background-color: #0284c7 !important;" class="palette-btn h-7 w-7 rounded-full border-2 border-white shadow-xs hover:scale-115 transition" title="Biru Langit"></button>
                    </div>
                </div>
            </div>

            <div>
                <label for="sort_order" class="block text-xs font-bold uppercase text-slate-700 mb-1">Urutan Tampil di Navbar (1, 2, 3...)</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="1" required
                       class="w-full rounded-lg border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-rose-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                <label for="is_active" class="text-xs font-bold text-slate-700">Tampilkan di Menu Navigasi</label>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('newsroom.categories.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700 shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const picker = document.getElementById('badge_picker');
            const colorInput = document.getElementById('badge_color');
            const preview = document.getElementById('badge_preview');
            const nameInput = document.getElementById('name');
            const buttons = document.querySelectorAll('.palette-btn');

            function applyColor(hex) {
                if (!hex) return;
                if (!hex.startsWith('#') && hex.length === 6) {
                    hex = '#' + hex;
                }
                colorInput.value = hex;
                if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
                    picker.value = hex;
                }
                if (preview) {
                    preview.style.backgroundColor = hex;
                }
            }

            if (picker) {
                picker.addEventListener('input', function (e) {
                    applyColor(e.target.value);
                });
                picker.addEventListener('change', function (e) {
                    applyColor(e.target.value);
                });
            }

            if (colorInput) {
                colorInput.addEventListener('input', function (e) {
                    applyColor(e.target.value);
                });
            }

            if (nameInput && preview) {
                nameInput.addEventListener('input', function (e) {
                    preview.innerText = e.target.value || 'PREVIEW';
                });
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const c = btn.getAttribute('data-color');
                    applyColor(c);
                });
            });
        });
    </script>

@endsection
