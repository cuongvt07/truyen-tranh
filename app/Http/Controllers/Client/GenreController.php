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

        // Redirect to catalog with pre-selected genre
        return redirect()->route('catalog.index', ['genres' => [$genre->id]]);
    }
}
