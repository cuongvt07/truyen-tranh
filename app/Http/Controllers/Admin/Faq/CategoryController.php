<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = FaqCategory::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.faq.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.faq.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:faq_categories,slug',
            'title_en' => 'required|string|max:255',
            'title_vi' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['icon'] = $validated['icon'] ?? 'fa-circle-question';

        FaqCategory::create($validated);

        return redirect()->route('admin.faq.categories.index')
            ->with('success', 'FAQ category created successfully.');
    }

    public function edit(FaqCategory $category)
    {
        return view('admin.faq.categories.edit', compact('category'));
    }

    public function update(Request $request, FaqCategory $category)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:faq_categories,slug,' . $category->id,
            'title_en' => 'required|string|max:255',
            'title_vi' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);

        return redirect()->route('admin.faq.categories.index')
            ->with('success', 'FAQ category updated successfully.');
    }

    public function destroy(FaqCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.faq.categories.index')
            ->with('success', 'FAQ category deleted successfully.');
    }
}
