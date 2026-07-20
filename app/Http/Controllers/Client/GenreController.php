<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function show(Request $request, Genre $genre)
    {
        if ($request->route()->originalParameter('genre') !== $genre->getRouteKey()) {
            return redirect()->route('genres.show', $genre, 301);
        }

        $articles = $genre->articles()
            ->with(['authors', 'genres', 'slug'])
            ->withCount('chapters')
            ->orderByRaw(
                '(select max(coalesce(chapters.published_at, chapters.created_at)) from chapters '
                . 'where chapters.article_id = articles.id '
                . 'and (chapters.published_at is null or chapters.published_at <= ?)) desc',
                [now()]
            )
            ->orderByDesc('articles.updated_at')
            ->paginate(30);

        return view('client.genres.show', [
            'genre' => $genre,
            'genres' => Genre::orderBy('name')->get(),
            'articles' => $articles,
        ]);
    }
}
