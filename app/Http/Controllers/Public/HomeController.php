<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\EditorialPickSlot;
use App\Http\Controllers\Controller;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\EditorialPick;
use App\Models\Epaper;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        // 1. Breaking news aktif
        $breakingNews = BreakingNews::query()
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest('starts_at')
            ->take(5)
            ->get();

        // 2. Editorial Picks (Headline + 4 Sub-picks)
        $picks = EditorialPick::query()
            ->with(['post' => function ($q) {
                $q->withListRelations();
            }])
            ->get()
            ->keyBy(fn (EditorialPick $pick) => $pick->slot->value);

        $headlinePost = $picks->get(EditorialPickSlot::HEADLINE->value)?->post;
        $pickPosts = collect([
            $picks->get(EditorialPickSlot::PICK_1->value)?->post,
            $picks->get(EditorialPickSlot::PICK_2->value)?->post,
            $picks->get(EditorialPickSlot::PICK_3->value)?->post,
            $picks->get(EditorialPickSlot::PICK_4->value)?->post,
        ])->filter();

        // Fallback jika slot belum terisi
        $usedPostIds = collect([$headlinePost?->id])
            ->merge($pickPosts->pluck('id'))
            ->filter()
            ->values();

        if ($headlinePost === null) {
            $headlinePost = Post::published()
                ->latestPublished()
                ->withListRelations()
                ->whereNotIn('id', $usedPostIds)
                ->first();

            if ($headlinePost) {
                $usedPostIds->push($headlinePost->id);
            }
        }

        while ($pickPosts->count() < 4) {
            $fallback = Post::published()
                ->latestPublished()
                ->withListRelations()
                ->whereNotIn('id', $usedPostIds)
                ->first();

            if (! $fallback) {
                break;
            }

            $pickPosts->push($fallback);
            $usedPostIds->push($fallback->id);
        }

        // 3. Feed Berita Terkini (tampilkan berita terkini, jangan biarkan kosong bila artikel masih sedikit)
        $latestPostsQuery = Post::published()
            ->latestPublished()
            ->withListRelations();

        if (Post::published()->count() > 5) {
            $latestPostsQuery->whereNotIn('id', $usedPostIds);
        }

        $latestPosts = $latestPostsQuery->paginate(10);

        // 4. Berita Terpopuler (Top 5)
        $popularPosts = Post::published()
            ->withListRelations()
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        // 5. Banner Post (Laporan Khusus di tengah kolom kiri)
        $bannerPost = Post::published()
            ->latestPublished()
            ->withListRelations()
            ->whereNotIn('id', $usedPostIds)
            ->first() ?? $headlinePost;

        if ($bannerPost && $bannerPost->id !== $headlinePost?->id) {
            $usedPostIds->push($bannerPost->id);
        }

        // 6. Featured News untuk baris slider bawah
        $featuredPosts = Post::published()
            ->withListRelations()
            ->latestPublished()
            ->take(12)
            ->get();

        // 7. Latest updates untuk kolom kanan
        $latestUpdates = Post::published()
            ->withListRelations()
            ->latestPublished()
            ->take(7)
            ->get();

        // 8. Tag Populer (Topik Hangat)
        $popularTags = Tag::withCount('posts')
            ->orderByDesc('posts_count')
            ->take(8)
            ->get();

        if ($popularTags->isEmpty()) {
            $popularTags = Category::active()
                ->take(8)
                ->get()
                ->map(fn ($cat) => (object) ['name' => strtolower($cat->name), 'slug' => $cat->slug]);
        }

        // 9. Kategori untuk tab / visual
        $categories = Category::active()->ordered()->get();

        // 10. Survei Pembaca Aktif & Status Suara Pengunjung
        $activePoll = Poll::currentActive();
        $userPollVote = null;
        if ($activePoll) {
            $voterToken = PollVoteController::resolveVoterToken($request);
            $userPollVote = PollVote::query()
                ->where('poll_id', $activePoll->id)
                ->where('voter_token', $voterToken)
                ->first();
        }

        // 11. E-Paper Hari Ini Aktif
        $activeEpaper = Epaper::currentActive();

        // 12. Tanggal Arsip Berita Terbitan Aktual (untuk Indeks Berita)
        $archiveDates = Post::published()
            ->whereNotNull('published_at')
            ->selectRaw('DATE(published_at) as pub_date, COUNT(*) as total')
            ->groupBy('pub_date')
            ->orderByDesc('pub_date')
            ->take(10)
            ->get();

        return view('public.home', compact(
            'breakingNews',
            'headlinePost',
            'pickPosts',
            'bannerPost',
            'latestPosts',
            'latestUpdates',
            'featuredPosts',
            'popularPosts',
            'popularTags',
            'categories',
            'activePoll',
            'userPollVote',
            'activeEpaper',
            'archiveDates'
        ));
    }
}
