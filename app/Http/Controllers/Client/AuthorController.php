<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Author;

class AuthorController extends Controller
{
    public function show(Author $author)
    {
        $articlesQuery = $author->articles()
            ->with(['authors', 'genres', 'slug'])
            ->withCount('chapters')
            ->orderByDesc('articles.updated_at');

        $articles = (clone $articlesQuery)->paginate(12);

        return view('client.authors.show', [
            'author' => $author,
            'articles' => $articles,
            'stats' => [
                'books' => (clone $articlesQuery)->count(),
                'views' => (clone $articlesQuery)->sum('view'),
                'rating' => (float) ((clone $articlesQuery)->avg('rating') ?? 0),
            ],
        ]);
    }
}
