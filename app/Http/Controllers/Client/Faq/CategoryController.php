<?php

namespace App\Http\Controllers\Client\Faq;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use App\Models\FaqArticle;

class CategoryController extends Controller
{
    /**
     * Display FAQ index with all categories
     */
    public function index()
    {
        $categories = FaqCategory::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->withCount(['articles' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return view('client.faq.index', compact('categories'));
    }

    /**
     * Display articles in a specific FAQ category
     */
    public function show(string $faqCategory)
    {
        $category = FaqCategory::where('slug', $faqCategory)
            ->where('is_active', true)
            ->firstOrFail();

        $articles = FaqArticle::where('category_id', $category->id)
            ->where('is_active', true)
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20);

        // Get all categories for sidebar
        $allCategories = FaqCategory::active()
            ->orderBy('sort_order')
            ->withCount(['articles' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return view('client.faq.category', compact('category', 'articles', 'allCategories'));
    }
}
