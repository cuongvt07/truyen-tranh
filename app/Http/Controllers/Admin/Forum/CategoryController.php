<?php

namespace App\Http\Controllers\Admin\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = ForumCategory::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.forum.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.forum.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:forum_categories,slug',
            'title_en' => 'required|string|max:255',
            'title_vi' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'section_label_en' => 'nullable|string|max:120',
            'section_label_vi' => 'nullable|string|max:120',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['icon'] = $validated['icon'] ?? 'fa-comments';

        ForumCategory::create($validated);

        return redirect()->route('admin.forum.categories.index')
            ->with('success', 'Forum category created successfully.');
    }

    public function edit(ForumCategory $category)
    {
        return view('admin.forum.categories.edit', compact('category'));
    }

    public function update(Request $request, ForumCategory $category)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:forum_categories,slug,' . $category->id,
            'title_en' => 'required|string|max:255',
            'title_vi' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'section_label_en' => 'nullable|string|max:120',
            'section_label_vi' => 'nullable|string|max:120',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);

        return redirect()->route('admin.forum.categories.index')
            ->with('success', 'Forum category updated successfully.');
    }

    public function destroy(ForumCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.forum.categories.index')
            ->with('success', 'Forum category deleted successfully.');
    }
}
