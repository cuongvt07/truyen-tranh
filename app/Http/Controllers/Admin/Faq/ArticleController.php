<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Controller;
use App\Models\FaqArticle;
use App\Models\FaqCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = FaqArticle::with('category')->orderBy('sort_order')->orderBy('created_at', 'desc');

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $articles = $query->paginate(30);
        $categories = FaqCategory::orderBy('sort_order')->get();

        return view('admin.faq.articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $categories = FaqCategory::orderBy('sort_order')->get();
        return view('admin.faq.articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:faq_categories,id',
            'slug' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'title_vi' => 'nullable|string|max:255',
            'content_en' => 'required|string',
            'content_vi' => 'nullable|string',
            'is_pinned' => 'nullable|boolean',
            'comments_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Check unique slug within category
        $exists = FaqArticle::where('category_id', $validated['category_id'])
            ->where('slug', $validated['slug'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['slug' => 'Slug must be unique within the category.'])->withInput();
        }

        $validated['is_pinned'] = $request->has('is_pinned');
        $validated['comments_enabled'] = $request->has('comments_enabled');
        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        FaqArticle::create($validated);

        return redirect()->route('admin.faq.articles.index')
            ->with('success', 'FAQ article created successfully.');
    }

    public function edit(FaqArticle $article)
    {
        $categories = FaqCategory::orderBy('sort_order')->get();
        return view('admin.faq.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, FaqArticle $article)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:faq_categories,id',
            'slug' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'title_vi' => 'nullable|string|max:255',
            'content_en' => 'required|string',
            'content_vi' => 'nullable|string',
            'is_pinned' => 'nullable|boolean',
            'comments_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Check unique slug within category (excluding current article)
        $exists = FaqArticle::where('category_id', $validated['category_id'])
            ->where('slug', $validated['slug'])
            ->where('id', '!=', $article->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['slug' => 'Slug must be unique within the category.'])->withInput();
        }

        $validated['is_pinned'] = $request->has('is_pinned');
        $validated['comments_enabled'] = $request->has('comments_enabled');
        $validated['is_active'] = $request->has('is_active');

        $article->update($validated);

        return redirect()->route('admin.faq.articles.index')
            ->with('success', 'FAQ article updated successfully.');
    }

    public function destroy(FaqArticle $article)
    {
        $article->delete();
        return redirect()->route('admin.faq.articles.index')
            ->with('success', 'FAQ article deleted successfully.');
    }
}
