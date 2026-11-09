<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsroom;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('posts')
            ->orderBy('sort_order')
            ->get();

        return view('newsroom.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('newsroom.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'badge_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $slug = Str::slug($validated['name']);

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'badge_color' => $validated['badge_color'],
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('newsroom.categories.index')
            ->with('status', "Rubrik '{$validated['name']}' berhasil ditambahkan ke navbar!");
    }

    public function edit(Category $category): View
    {
        return view('newsroom.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'badge_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'badge_color' => $validated['badge_color'],
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('newsroom.categories.index')
            ->with('status', "Rubrik '{$category->name}' berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->posts()->count() > 0) {
            return back()->with('error', 'Rubrik tidak dapat dihapus karena masih memuat artikel berita.');
        }

        $category->delete();

        return redirect()->route('newsroom.categories.index')
            ->with('status', 'Rubrik berhasil dihapus.');
    }
}
