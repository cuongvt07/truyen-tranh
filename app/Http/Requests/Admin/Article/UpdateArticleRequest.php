<?php

namespace App\Http\Requests\Admin\Article;

use App\Models\Article;
use App\Models\Team;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends ArticleBaseRequest
{
    public function rules(): array
    {
        $article = $this->route('article');
        $id = $article instanceof Article ? $article->id : $article;
        $slugId = $article instanceof Article ? optional($article->slug)->id : null;

        return [
            'title' => [
                'required',
                Rule::unique(Article::class)->ignore($id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('slugs', 'slug')
                    ->where(fn ($query) => $query->where('type', 'article'))
                    ->ignore($slugId),
            ],
            'similar_article_ids' => ['nullable', 'array'],
            'similar_article_ids.*' => ['integer', 'exists:articles,id'],
            'translation_request_article_ids' => ['nullable', 'array'],
            'translation_request_article_ids.*' => ['integer', 'exists:articles,id'],
            'related_genre_ids' => ['nullable', 'array'],
            'related_genre_ids.*' => ['integer', 'exists:genres,id'],
            'characters' => ['nullable', 'array'],
            'characters.*' => ['integer', 'exists:characters,id'],
            'view' => ['nullable', 'integer', 'min:0'],
            'credit_start_chapter' => ['nullable', 'integer', 'min:1'],
            'credit_per_chapter' => ['nullable', 'integer', 'min:0'],
            'team_id' => [
                'nullable',
                'integer',
                Rule::exists('teams', 'id')->where(fn ($query) => $query->where('status', Team::STATUS_APPROVED)),
            ],
        ];
    }
}
