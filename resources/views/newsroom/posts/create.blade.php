{{-- File: resources/views/newsroom/posts/create.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Tulis Berita Baru')

@section('content')

    <div class="mb-6">
        <a href="{{ route('newsroom.posts.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition flex items-center gap-1 mb-2">
            &larr; Kembali ke Manajemen Naskah
        </a>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Studio Penulisan Berita</h1>
        <p class="text-xs text-slate-500">
            Tulis liputan berita baru sesuai Kode Etik Jurnalistik dan kaidah PUEBI.
        </p>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
            <div class="font-bold mb-1">Periksa kembali isian formulir:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('newsroom.posts.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Kolom Kiri: Input Utama (8 Kolom) --}}
            <div class="lg:col-span-8 space-y-5">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                    <div>
                        <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Judul Berita <span class="text-rose-600">*</span>
                        </label>
                        <input type="text"
                               id="title"
                               name="title"
                               value="{{ old('title') }}"
                               required
                               placeholder="Tuliskan judul berita yang lugas, akurat, dan memikat..."
                               class="w-full rounded-lg border border-slate-300 p-3 text-base font-bold text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
                    </div>

                    <div>
                        <label for="excerpt" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Ringkasan / Lead Paragraf (Excerpt)
                        </label>
                        <textarea id="excerpt"
                                  name="excerpt"
                                  rows="2"
                                  placeholder="Tuliskan 1-2 kalimat ringkasan inti berita (lead)..."
                                  class="w-full rounded-lg border border-slate-300 p-3 text-xs leading-relaxed text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">{{ old('excerpt') }}</textarea>
                    </div>

                    <div>
                        <label for="body" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Isi Naskah Berita (Body) <span class="text-rose-600">*</span>
                        </label>
                        <textarea id="body"
                                  name="body"
                                  rows="14"
                                  required
                                  placeholder="Ketikkan naskah berita lengkap di sini (format HTML didukung: <p>, <strong>, <h2>, dll)..."
                                  class="w-full rounded-lg border border-slate-300 p-4 font-sans text-sm leading-relaxed text-slate-900 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">{{ old('body') }}</textarea>
                    </div>
                </div>

                {{-- Media Gambar --}}
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                        Foto Utama Artikel (Featured Media)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Opsi 1: Upload File Langsung --}}
                        <div class="rounded-lg border-2 border-dashed border-slate-200 p-4 hover:border-rose-400 transition bg-slate-50/50">
                            <label for="image_file" class="block text-xs font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Upload File dari Komputer (PNG, JPG, WebP)
                            </label>
                            <input type="file"
                                   id="image_file"
                                   name="image_file"
                                   accept="image/*"
                                   onchange="previewUploadedImage(event)"
                                   class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer">
                            <p class="text-[10px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                                <svg class="h-3 w-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Otomatis dikonversi ke WebP kualitas tinggi (hemat 85% & super tajam).
                            </p>
                            
                            <div id="file_preview_container" class="mt-3 hidden">
                                <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Pratinjau Foto Terpilih:</span>
                                <img id="file_preview_img" src="#" alt="Preview" class="h-32 w-auto object-cover rounded-lg border border-slate-200 shadow-xs">
                            </div>
                        </div>

                        {{-- Opsi 2: Link URL Eksternal --}}
                        <div class="rounded-lg border border-slate-200 p-4 bg-white">
                            <label for="image_url" class="block text-xs font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Atau Gunakan Link URL Gambar (HTTPS)
                            </label>
                            <input type="url"
                                   id="image_url"
                                   name="image_url"
                                   value="{{ old('image_url') }}"
                                   placeholder="https://images.unsplash.com/..."
                                   class="w-full rounded-lg border border-slate-300 p-2 text-xs text-slate-800 focus:border-rose-500 focus:outline-none">
                            <p class="text-[10px] text-slate-400 mt-1">Untuk gambar dari Unsplash atau sumber berita luar.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="image_caption" class="block text-xs font-bold text-slate-700 mb-1">
                                Takarir / Caption Foto
                            </label>
                            <input type="text"
                                   id="image_caption"
                                   name="image_caption"
                                   value="{{ old('image_caption') }}"
                                   placeholder="Contoh: Suasana pelantikan menteri di istana negara."
                                   class="w-full rounded-lg border border-slate-300 p-2 text-xs text-slate-800 focus:border-rose-500 focus:outline-none">
                        </div>
                        <div>
                            <label for="image_credit" class="block text-xs font-bold text-slate-700 mb-1">
                                Kredit Fotografer / Sumber
                            </label>
                            <input type="text"
                                   id="image_credit"
                                   name="image_credit"
                                   value="{{ old('image_credit', 'Dok. Redaksi') }}"
                                   placeholder="Contoh: Antara Foto / Budi"
                                   class="w-full rounded-lg border border-slate-300 p-2 text-xs text-slate-800 focus:border-rose-500 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Pengaturan & Tombol Aksi (4 Kolom) --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Panel Publikasi & Aksi --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-3">
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                        Aksi Pengajuan Naskah
                    </h2>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        Tentukan status pengajuan naskah berita ini:
                    </p>

                    <div class="space-y-2 pt-2">
                        @if (auth()->user()?->hasRole('super-admin'))
                            <button type="submit"
                                    name="action"
                                    value="publish"
                                    class="w-full rounded-xl bg-emerald-600 py-3 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition flex items-center justify-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Terbitkan Langsung ke Publik
                            </button>
                            <button type="submit"
                                    name="action"
                                    value="draft"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Simpan sebagai Draf
                            </button>
                        @else
                            @if (auth()->user()?->hasAnyRole(['pemred', 'desk-editor']))
                                <button type="submit"
                                        name="action"
                                        value="publish"
                                        class="w-full rounded-xl bg-emerald-600 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition flex items-center justify-center gap-1.5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Terbitkan Langsung ke Publik
                                </button>
                            @endif

                            <button type="submit"
                                    name="action"
                                    value="submit_review"
                                    class="w-full rounded-xl bg-rose-600 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-rose-700 transition flex items-center justify-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Kirim untuk Review Editor
                            </button>

                            <button type="submit"
                                    name="action"
                                    value="draft"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Simpan sebagai Draf Pribadi
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Penulis / Wartawan --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-3">
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                        Penulis / Wartawan
                    </h2>

                    <div>
                        <label for="author_name" class="block text-xs text-slate-500 mb-1">
                            Nama Wartawan / Kontributor:
                        </label>
                        <input type="text"
                               id="author_name"
                               name="author_name"
                               value="{{ old('author_name') }}"
                               placeholder="{{ auth()->user()->displayName() }} (Kosongkan jika sendiri)"
                               class="w-full rounded-lg border border-slate-300 p-2 text-xs font-semibold text-slate-800 focus:border-rose-500 focus:outline-none">
                        <p class="text-[10px] text-slate-400 mt-1">
                            Bisa diisi nama teman wartawan, kontributor, atau biarkan kosong untuk memakai nama akun login.
                        </p>
                    </div>
                </div>

                {{-- Rubrik Kategori --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-3">
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                        Rubrik Kategori <span class="text-rose-600">*</span>
                    </h2>

                    <div>
                        <select name="category_id"
                                required
                                class="w-full rounded-lg border border-slate-300 p-2 text-xs font-semibold text-slate-800 focus:border-rose-500 focus:outline-none">
                            <option value="">-- Pilih Rubrik Berita --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tag Berita --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-3">
                    <h2 class="text-xs font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                        Tag Topik Hangat
                    </h2>

                    <div>
                        <label for="tags" class="block text-xs text-slate-500 mb-1">
                            Pisahkan dengan tanda koma (,)
                        </label>
                        <input type="text"
                               id="tags"
                               name="tags"
                               value="{{ old('tags') }}"
                               placeholder="Contoh: IKN, Presiden, Anggaran 2026"
                               class="w-full rounded-lg border border-slate-300 p-2 text-xs text-slate-800 focus:border-rose-500 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('head')
<script>
    function previewUploadedImage(event) {
        const file = event.target.files && event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('file_preview_img');
                const container = document.getElementById('file_preview_container');
                if (img && container) {
                    img.src = e.target.result;
                    container.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
