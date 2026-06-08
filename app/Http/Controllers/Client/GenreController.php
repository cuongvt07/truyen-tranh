<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function show(Genre $genre)
    {
        $articles = $genre->articles()->paginate();
        return view('client.genres.show', [
            'genre'    => $genre,
            'articles' => $articles,
        ]);
    }
}
