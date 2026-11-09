@extends('layouts.newsroom')

{{-- File: resources/views/newsroom/two-factor/recovery-codes.blade.php --}}
@section('title', 'Kode Pemulihan')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-2 text-2xl font-bold tracking-tight">Kode Pemulihan</h1>

        <div class="mb-6 rounded border border-rose-200 bg-rose-50 p-3 text-sm text-rose-900">
            <strong>Simpan sekarang.</strong> Kode ini hanya ditampilkan satu kali dan setiap kode
            hanya dapat dipakai sekali. Gunakan jika Anda kehilangan akses ke aplikasi authenticator.
        </div>

        <ul class="mb-6 grid grid-cols-1 gap-2 rounded border border-slate-200 bg-white p-4 font-mono text-sm sm:grid-cols-2">
            @foreach ($codes as $code)
                <li class="rounded bg-slate-50 px-3 py-2 tracking-wider">{{ $code }}</li>
            @endforeach
        </ul>

        <a href="{{ route('newsroom.dashboard') }}"
           class="inline-block rounded bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
            Saya sudah menyimpannya, lanjut ke dasbor
        </a>
    </div>
@endsection
