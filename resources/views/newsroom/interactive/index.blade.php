{{-- File: resources/views/newsroom/interactive/index.blade.php --}}
@extends('layouts.newsroom')

@section('title', 'Manajemen Survei Pembaca & E-Paper Cetak')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-xl font-black text-slate-900">Meja Interaktif: Survei Pembaca &amp; E-Paper Harian</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola pertanyaan survei opini pembaca dan penerbitan E-Paper PDF edisi cetak secara langsung.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('epaper.show') }}" target="_blank"
               class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                Lihat Halaman E-Paper
            </a>
            <a href="{{ route('epaper.download') }}"
               class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700 transition">
                Unduh PDF Aktif
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- KOLOM KIRI: MANAJEMEN SURVEI PEMBACA (7 COLS) --}}
        <div class="lg:col-span-7 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 mb-4">
                    Terbitkan Survei Pembaca Baru
                </h2>

                <form action="{{ route('newsroom.interactive.polls.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pertanyaan Utama Survei</label>
                        <input type="text" name="question" required
                               placeholder="Contoh: Apakah Anda setuju dengan pembatasan kendaraan pribadi di pusat kota?"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Konteks (Opsional)</label>
                        <textarea name="description" rows="2"
                                  placeholder="Penjelasan singkat latar belakang kebijakan atau topik..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Label Edisi</label>
                            <input type="text" name="edition_label" value="Edisi Pekan Ini" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Batas Waktu</label>
                            <input type="text" name="deadline_label" value="Akhir pekan" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Pilihan Jawaban Pembaca (3 Opsi)</label>
                        <input type="text" name="options[]" required placeholder="Opsi 1: Sangat Setuju (...)"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                        <input type="text" name="options[]" required placeholder="Opsi 2: Setuju Bersyarat (...)"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                        <input type="text" name="options[]" required placeholder="Opsi 3: Tidak Setuju (...)"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-slate-900 hover:bg-rose-600 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition cursor-pointer">
                        Terbitkan &amp; Aktifkan Survei di Beranda
                    </button>
                </form>
            </div>

            {{-- Daftar Riwayat Survei & Hasil Real-Time --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 mb-4">
                    Daftar Survei &amp; Perolehan Suara Real-Time
                </h3>

                <div class="space-y-4">
                    @foreach ($polls as $poll)
                        @php
                            $total = $poll->totalVotes();
                        @endphp
                        <div class="rounded-lg border {{ $poll->is_active ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200 bg-slate-50' }} p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        @if ($poll->is_active)
                                            <span class="rounded bg-emerald-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">AKTIF DI BERANDA</span>
                                        @else
                                            <span class="rounded bg-slate-200 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600">ARSIP</span>
                                        @endif
                                        <span class="text-[11px] text-slate-500 font-semibold">{{ $poll->edition_label }} &bull; Total {{ number_format($total) }} Suara</span>
                                    </div>
                                    <h4 class="font-bold text-sm text-slate-900">{{ $poll->question }}</h4>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if (! $poll->is_active)
                                        <form action="{{ route('newsroom.interactive.polls.activate', $poll) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="rounded bg-slate-900 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-600 transition cursor-pointer">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                    @if ($polls->count() > 1)
                                        <form action="{{ route('newsroom.interactive.polls.destroy', $poll) }}" method="POST" onsubmit="return confirm('Hapus survei ini beserta seluruh data suaranya?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded bg-rose-100 px-2.5 py-1 text-[11px] font-bold text-rose-700 hover:bg-rose-200 transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 space-y-2">
                                @foreach ($poll->options as $opt)
                                    @php $pct = $opt->percentage($total); @endphp
                                    <div>
                                        <div class="flex justify-between text-xs text-slate-700 mb-0.5">
                                            <span>{{ $opt->label }}</span>
                                            <span class="font-bold">{{ $pct }}% ({{ $opt->votes_count }} suara)</span>
                                        </div>
                                        <div class="h-1.5 w-full rounded bg-slate-200 overflow-hidden">
                                            <div class="h-full bg-rose-600" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: MANAJEMEN E-PAPER CETAK (5 COLS) --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 mb-4">
                    Terbitkan Edisi E-Paper Baru
                </h2>

                <form action="{{ route('newsroom.interactive.epapers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Edisi</label>
                            <input type="text" name="edition_number" value="EDISI #{{ now()->format('ymd') }}" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Terbit</label>
                            <input type="date" name="edition_date" value="{{ now()->toDateString() }}" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Sorotan Edisi</label>
                        <input type="text" name="title" required
                               placeholder="Contoh: Laporan Utama Karhutla & Transformasi Digital"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Ringkas</label>
                        <textarea name="description" rows="2"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">Format digital resolusi tinggi, gratis untuk seluruh pembaca Ruang Data.</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Unggah File PDF Kustom (Opsional)</label>
                        <input type="file" name="pdf_file" accept="application/pdf"
                               class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs">
                        <p class="mt-1 text-[11px] text-slate-500">
                            *Bila dikosongkan, sistem otomatis men-generate dokumen PDF Koran Cetak langsung dari berita-berita terbitan di database.
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-rose-600 hover:bg-rose-700 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition cursor-pointer">
                        Simpan &amp; Terbitkan E-Paper
                    </button>
                </form>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 mb-4">
                    Daftar Edisi E-Paper
                </h3>

                <div class="space-y-3">
                    @foreach ($epapers as $ep)
                        <div class="rounded-lg border {{ $ep->is_active ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200 bg-slate-50' }} p-3.5">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1">
                                        @if ($ep->is_active)
                                            <span class="rounded bg-emerald-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">EDISI AKTIF</span>
                                        @endif
                                        <span class="text-xs font-mono font-bold text-slate-700">{{ $ep->edition_number }}</span>
                                        <span class="text-[11px] text-slate-500">&bull; {{ $ep->edition_date->translatedFormat('d M Y') }}</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $ep->title }}</h4>
                                    <p class="text-[11px] text-slate-500 mt-1">
                                        Mode: <strong>{{ $ep->hasCustomPdf() ? 'PDF Unggahan Redaksi' : 'Otomatis dari Berita Database' }}</strong>
                                        &bull; {{ number_format($ep->downloads_count) }} unduhan
                                    </p>
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <a href="{{ route('epaper.download', ['edisi' => $ep->id]) }}"
                                       class="rounded border border-slate-300 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100">
                                        PDF
                                    </a>
                                    @if (! $ep->is_active)
                                        <form action="{{ route('newsroom.interactive.epapers.activate', $ep) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="rounded bg-slate-900 px-2 py-1 text-[11px] font-bold text-white hover:bg-emerald-600 cursor-pointer">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                    @if ($epapers->count() > 1)
                                        <form action="{{ route('newsroom.interactive.epapers.destroy', $ep) }}" method="POST" onsubmit="return confirm('Hapus edisi E-Paper ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded bg-rose-100 px-2 py-1 text-[11px] font-bold text-rose-700 hover:bg-rose-200 cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
