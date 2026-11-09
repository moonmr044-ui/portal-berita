<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsroom;

use App\Http\Controllers\Controller;
use App\Models\Epaper;
use App\Models\Poll;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InteractiveDeskController extends Controller
{
    public function index(): View
    {
        Poll::currentActive();
        Epaper::currentActive();

        $polls = Poll::query()->with('options')->latest('id')->get();
        $epapers = Epaper::query()->orderByDesc('edition_date')->orderByDesc('id')->get();

        return view('newsroom.interactive.index', compact('polls', 'epapers'));
    }

    public function storePoll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:600'],
            'edition_label' => ['required', 'string', 'max:100'],
            'deadline_label' => ['required', 'string', 'max:100'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*' => ['required', 'string', 'max:250'],
        ]);

        DB::transaction(function () use ($validated): void {
            Poll::query()->update(['is_active' => false]);

            $poll = Poll::query()->create([
                'question' => $validated['question'],
                'description' => $validated['description'] ?? null,
                'edition_label' => $validated['edition_label'],
                'deadline_label' => $validated['deadline_label'],
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            foreach (array_values($validated['options']) as $idx => $label) {
                $poll->options()->create([
                    'label' => $label,
                    'votes_count' => 0,
                    'sort_order' => $idx + 1,
                ]);
            }
        });

        return redirect()->route('newsroom.interactive.index')
            ->with('status', 'Survei Pembaca baru berhasil diterbitkan dan diaktifkan di halaman utama!');
    }

    public function activatePoll(Poll $poll): RedirectResponse
    {
        DB::transaction(function () use ($poll): void {
            Poll::query()->update(['is_active' => false]);
            $poll->update(['is_active' => true]);
        });

        return redirect()->route('newsroom.interactive.index')
            ->with('status', 'Survei Pembaca berhasil dijadikan survei aktif di beranda.');
    }

    public function destroyPoll(Poll $poll): RedirectResponse
    {
        $poll->delete();

        return redirect()->route('newsroom.interactive.index')
            ->with('status', 'Survei Pembaca berhasil dihapus.');
    }

    public function storeEpaper(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'edition_number' => ['required', 'string', 'max:60'],
            'edition_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:600'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $filePath = null;
        if ($request->hasFile('pdf_file')) {
            $filePath = $request->file('pdf_file')->store('epapers', 'public');
        }

        DB::transaction(function () use ($validated, $filePath): void {
            Epaper::query()->update(['is_active' => false]);

            Epaper::query()->create([
                'edition_number' => $validated['edition_number'],
                'edition_date' => $validated['edition_date'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? 'Format digital resolusi tinggi, gratis untuk seluruh pembaca Ruang Data.',
                'file_path' => $filePath,
                'downloads_count' => 0,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route('newsroom.interactive.index')
            ->with('status', 'Edisi E-Paper baru berhasil diterbitkan dan diaktifkan di beranda!');
    }

    public function activateEpaper(Epaper $epaper): RedirectResponse
    {
        DB::transaction(function () use ($epaper): void {
            Epaper::query()->update(['is_active' => false]);
            $epaper->update(['is_active' => true]);
        });

        return redirect()->route('newsroom.interactive.index')
            ->with('status', 'Edisi E-Paper berhasil dijadikan edisi aktif.');
    }

    public function destroyEpaper(Epaper $epaper): RedirectResponse
    {
        if ($epaper->file_path) {
            Storage::disk('public')->delete($epaper->file_path);
        }
        $epaper->delete();

        return redirect()->route('newsroom.interactive.index')
            ->with('status', 'Edisi E-Paper berhasil dihapus.');
    }
}
