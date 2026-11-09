@extends('layouts.newsroom')

{{-- File: resources/views/newsroom/two-factor/challenge.blade.php --}}
@section('title', 'Verifikasi Dua Langkah')

@section('content')
    <div class="mx-auto max-w-md">
        <h1 class="mb-2 text-2xl font-bold tracking-tight">Verifikasi Dua Langkah</h1>
        <p class="mb-6 text-sm text-slate-600">
            Masukkan kode dari aplikasi authenticator Anda, atau salah satu kode pemulihan
            bila Anda kehilangan akses ke perangkat tersebut.
        </p>

        @if ($errors->any())
            <div role="alert" class="mb-4 rounded border border-rose-200 bg-rose-50 p-3 text-sm text-rose-900">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('newsroom.two-factor.verify') }}" class="space-y-4">
            @csrf

            <div>
                <label for="code" class="mb-1 block text-sm font-medium">Kode verifikasi</label>
                <input id="code" name="code" type="text" autocomplete="one-time-code" required autofocus
                       placeholder="123456 atau kode-pemulihan"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
            </div>

            <button type="submit"
                    class="w-full rounded bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="text-sm text-slate-600 underline hover:text-slate-900">
                Keluar dari akun ini
            </button>
        </form>
    </div>
@endsection
