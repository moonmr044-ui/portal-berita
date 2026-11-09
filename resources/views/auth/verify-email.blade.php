@extends('layouts.public')

{{-- File: resources/views/auth/verify-email.blade.php --}}
@section('title', 'Verifikasi Email')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="mb-2 text-2xl font-bold tracking-tight">Verifikasi Email Anda</h1>

        <p class="mb-6 text-sm text-slate-600">
            Kami telah mengirim tautan verifikasi ke
            <strong>{{ auth()->user()?->email }}</strong>.
            Buka tautan tersebut untuk mengaktifkan fitur komentar, reaksi, dan bookmark.
        </p>

        @if (session('status'))
            <div role="status" class="mb-4 rounded border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="rounded bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Kirim ulang tautan verifikasi
            </button>
        </form>

        <p class="mt-6 text-xs text-slate-500">
            Di lingkungan pengembangan, email tertangkap oleh Mailpit di
            <a href="http://localhost:8025" class="underline">http://localhost:8025</a>.
        </p>
    </div>
@endsection
