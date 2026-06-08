<?php

namespace App\Http\Requests\Admin\Article;

use App\Models\Article;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends ArticleBaseRequest
{
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                Rule::unique(Article::class),
            ],
            'similar_article_ids' => ['nullable', 'array'],
            'similar_article_ids.*' => ['integer', 'exists:articles,id'],
            'translation_request_article_ids' => ['nullable', 'array'],
            'translation_request_article_ids.*' => ['integer', 'exists:articles,id'],
            'related_genre_ids' => ['nullable', 'array'],
            'related_genre_ids.*' => ['integer', 'exists:genres,id'],
            'view' => ['nullable', 'integer', 'min:0'],
            'credit_start_chapter' => ['nullable', 'integer', 'min:1'],
            'credit_per_chapter' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
