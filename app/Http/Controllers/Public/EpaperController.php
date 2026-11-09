<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\EditorialPickSlot;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EditorialPick;
use App\Models\Epaper;
use App\Models\Post;
use App\Services\EpaperPdfGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class EpaperController extends Controller
{
    public function show(Request $request): View
    {
        $epaper = $request->filled('edisi')
            ? Epaper::query()->findOrFail((int) $request->input('edisi'))
            : Epaper::currentActive();

        $editions = Epaper::query()
            ->where('is_active', true)
            ->orderByDesc('edition_date')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        $picks = EditorialPick::query()
            ->with(['post' => fn ($q) => $q->withListRelations()])
            ->get()
            ->keyBy(fn (EditorialPick $pick) => $pick->slot->value);

        $headlinePost = $picks->get(EditorialPickSlot::HEADLINE->value)?->post
            ?? Post::published()->latestPublished()->withListRelations()->first();

        $pickPosts = collect([
            $picks->get(EditorialPickSlot::PICK_1->value)?->post,
            $picks->get(EditorialPickSlot::PICK_2->value)?->post,
            $picks->get(EditorialPickSlot::PICK_3->value)?->post,
            $picks->get(EditorialPickSlot::PICK_4->value)?->post,
        ])->filter()->values();

        $posts = Post::published()
            ->latestPublished()
            ->withListRelations()
            ->take(12)
            ->get();

        $categories = Category::active()->ordered()->get();

        return view('public.epaper.show', compact(
            'epaper',
            'editions',
            'headlinePost',
            'pickPosts',
            'posts',
            'categories'
        ));
    }

    public function download(Request $request, EpaperPdfGenerator $generator): Response
    {
        $epaper = $request->filled('edisi')
            ? Epaper::query()->find((int) $request->input('edisi'))
            : Epaper::currentActive();

        if ($epaper) {
            $epaper->increment('downloads_count');
        }

        $dateSlug = ($epaper?->edition_date ?? now())->format('Y-m-d');
        $filename = "RuangData-Edisi-Cetak-{$dateSlug}.pdf";
        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';

        if ($epaper && $epaper->hasCustomPdf()) {
            $content = Storage::disk('public')->get((string) $epaper->file_path);
            if ($content !== null) {
                return response($content, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                    'Cache-Control' => 'no-store, no-cache, must-revalidate',
                ]);
            }
        }

        $pdfBinary = $generator->generate($epaper);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Content-Length' => (string) strlen($pdfBinary),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
