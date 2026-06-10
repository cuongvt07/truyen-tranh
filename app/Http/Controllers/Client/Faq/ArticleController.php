<?php

namespace App\Http\Controllers\Client\Faq;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use App\Models\FaqArticle;

class ArticleController extends Controller
{
    /**
     * Display a specific FAQ article
     */
    public function show(string $faqCategory, string $faqArticle)
    {
        $category = FaqCategory::where('slug', $faqCategory)
            ->where('is_active', true)
            ->firstOrFail();

        $article = FaqArticle::where('category_id', $category->id)
            ->where('slug', $faqArticle)
            ->where('is_active', true)
            ->firstOrFail();

        $article->incrementViewCount();

        // Get all categories for sidebar navigation
        $allCategories = FaqCategory::active()
            ->orderBy('sort_order')
            ->with(['articles' => function ($query) {
                $query->where('is_active', true)
                    ->orderByDesc('is_pinned')
                    ->orderBy('sort_order');
            }])
            ->get();

        return view('client.faq.article', compact('category', 'article', 'allCategories'));
    }
}
