<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsroom;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = Auth::user();

        // 1. Statistik Nyata Redaksi & Interaksi Pembaca
        $stats = [
            'total_posts' => Post::count(),
            'published' => Post::where('status', PostStatus::PUBLISHED->value)->count(),
            'takedown' => Post::where('status', PostStatus::TAKEDOWN->value)->count(),
            'total_views' => (int) Post::sum('views_count'),
            'total_comments' => (int) \App\Models\Comment::count(),
            'total_likes' => (int) \App\Models\Reaction::count(),
        ];

        // 2. Daftar artikel terbaru yang sedang aktif
        $query = Post::query()->withListRelations()->latest();

        // Jika reporter, tampilkan artikel buatannya
        if ($user->hasRole('reporter')) {
            $query->where('author_id', $user->id);
        }

        $recentPosts = $query->take(8)->get();

        // 3. Rubrik yang ditugaskan
        $assignedCategories = $user->assignedCategories()
            ->orderBy('sort_order')
            ->get();

        return view('newsroom.dashboard', compact('stats', 'recentPosts', 'assignedCategories', 'user'));
    }
}
