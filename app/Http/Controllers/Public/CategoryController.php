<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        if ($category->shouldRedirect()) {
            $target = Category::find($category->redirect_to_category_id);
            if ($target) {
                return redirect()->route('categories.show', $target->slug, 301);
            }
        }

        if (! $category->is_active) {
            abort(404);
        }

        $posts = Post::published()
            ->latestPublished()
            ->withListRelations()
            ->where('category_id', $category->id)
            ->paginate(12);

        $popularPosts = Post::published()
            ->withListRelations()
            ->where('category_id', $category->id)
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        if ($popularPosts->isEmpty()) {
            $popularPosts = Post::published()
                ->withListRelations()
                ->orderByDesc('views_count')
                ->take(5)
                ->get();
        }

        return view('public.categories.show', compact('category', 'posts', 'popularPosts'));
    }
}
