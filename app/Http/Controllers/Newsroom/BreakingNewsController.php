<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsroom;

use App\Http\Controllers\Controller;
use App\Models\BreakingNews;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BreakingNewsController extends Controller
{
    public function index(): View
    {
        $items = BreakingNews::latest('starts_at')->paginate(15);

        return view('newsroom.breaking-news.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:48'],
        ]);

        BreakingNews::create([
            'title' => $validated['title'],
            'url' => $validated['url'] ?? null,
            'starts_at' => now(),
            'ends_at' => now()->addHours((int) $validated['duration_hours']),
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('newsroom.breaking-news.index')
            ->with('status', 'Breaking News baru berhasil disiarkan di ticker atas!');
    }

    public function toggle(BreakingNews $breakingNews): RedirectResponse
    {
        $breakingNews->is_active = ! $breakingNews->is_active;
        $breakingNews->save();

        return redirect()->route('newsroom.breaking-news.index')
            ->with('status', 'Status siaran breaking news berhasil diperbarui.');
    }

    public function destroy(BreakingNews $breakingNews): RedirectResponse
    {
        $breakingNews->delete();

        return redirect()->route('newsroom.breaking-news.index')
            ->with('status', 'Item breaking news berhasil dihapus.');
    }
}
