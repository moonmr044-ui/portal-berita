<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));
        $date = trim((string) $request->input('date', ''));
        $category = trim((string) $request->input('kategori', ''));
        $sort = trim((string) $request->input('sort', 'terbaru'));

        $postsQuery = Post::published()->withListRelations();

        if ($query !== '') {
            $postsQuery->where(function ($q) use ($query): void {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%")
                    ->orWhereHas('category', function ($cq) use ($query): void {
                        $cq->where('name', 'like', "%{$query}%")
                            ->orWhere('slug', 'like', "%{$query}%");
                    })
                    ->orWhereHas('tags', function ($tq) use ($query): void {
                        $tq->where('name', 'like', "%{$query}%")
                            ->orWhere('slug', 'like', "%{$query}%");
                    });
            });
        }

        if ($date !== '') {
            $postsQuery->whereDate('published_at', $date);
        }

        if ($category !== '') {
            $postsQuery->whereHas('category', function ($cq) use ($category): void {
                $cq->where('slug', $category)->orWhere('name', $category);
            });
        }

        if ($sort === 'terpopuler') {
            $postsQuery->orderByDesc('views_count')->latestPublished();
        } elseif ($sort === 'terlama') {
            $postsQuery->orderBy('published_at')->orderBy('id');
        } else {
            $postsQuery->latestPublished();
        }

        $posts = $postsQuery->paginate(10)->withQueryString();

        $popularPosts = Post::published()
            ->withListRelations()
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        $categories = Category::active()->ordered()->get();

        $archiveDates = Post::published()
            ->whereNotNull('published_at')
            ->selectRaw('DATE(published_at) as pub_date, COUNT(*) as total')
            ->groupBy('pub_date')
            ->orderByDesc('pub_date')
            ->take(12)
            ->get();

        $fallbackPosts = collect();
        if ($posts->isEmpty() && ($date !== '' || $query !== '' || $category !== '')) {
            $fallbackPosts = Post::published()
                ->latestPublished()
                ->withListRelations()
                ->take(6)
                ->get();
        }

        return view('public.search', compact(
            'query',
            'date',
            'category',
            'sort',
            'posts',
            'popularPosts',
            'categories',
            'archiveDates',
            'fallbackPosts'
        ));
    }
}
