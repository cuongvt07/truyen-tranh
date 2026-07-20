<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Character;

class CharacterController extends Controller
{
    public function show(Character $character)
    {
        $articles = $character->articles()
            ->with(['authors', 'genres', 'slug'])
            ->withCount('chapters')
            ->orderByDesc('articles.updated_at')
            ->paginate(24);

        return view('client.community.character-show', compact('character', 'articles'));
    }
}
