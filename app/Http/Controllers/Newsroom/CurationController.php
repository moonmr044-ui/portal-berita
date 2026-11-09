<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsroom;

use App\Enums\EditorialPickSlot;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\EditorialPick;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurationController extends Controller
{
    public function index(): View
    {
        $publishedPosts = Post::where('status', PostStatus::PUBLISHED->value)
            ->latestPublished()
            ->withListRelations()
            ->take(30)
            ->get();

        $currentPicks = EditorialPick::all()->keyBy(fn ($p) => $p->slot->value);

        $slots = EditorialPickSlot::cases();

        return view('newsroom.curation.index', compact('publishedPosts', 'currentPicks', 'slots'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $slotsData = $request->input('slots', []);

        foreach (EditorialPickSlot::cases() as $slot) {
            $postId = isset($slotsData[$slot->value]) && $slotsData[$slot->value] !== ''
                ? (int) $slotsData[$slot->value]
                : null;

            EditorialPick::updateOrCreate(
                ['slot' => $slot->value],
                [
                    'post_id' => $postId,
                    'updated_by' => $user->id,
                ]
            );
        }

        return redirect()->route('newsroom.curation.index')
            ->with('status', 'Kurasi hero homepage (Headline & Pilihan Redaksi) berhasil diperbarui!');
    }
}
