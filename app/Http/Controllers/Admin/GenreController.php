<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Genre\StoreGenreRequest;
use App\Http\Requests\Admin\Genre\UpdateGenreRequest;
use App\Models\Genre;
use App\Models\Slug;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Genre::query();
        
        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Sorting
        $sort = $request->get('sort', 'name');
        switch ($sort) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'id_desc':
                $query->orderByDesc('id');
                break;
            case 'id_asc':
                $query->orderBy('id');
                break;
            default:
                $query->orderBy('name');
        }

        $genres = $query->paginate(30)->withQueryString();
        
        return view('admin.genres.index', [
            'genres' => $genres,
            'filters' => $request->only(['search', 'sort'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genre  = new Genre();
        return view('admin.genres.create', ['genre' => $genre]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGenreRequest $request)
    {
        $request->validated();
        $genre = Genre::create($request->all());
        Slug::ensureFor($genre, 'genre', $request->input('slug') ?: $genre->name);
        return redirect()->route('admin.genres.index')->with('success', __('messages.flash.genre.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Genre $genre)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Genre $genre)
    {
        $genre->load('slug');
        return view('admin.genres.edit', ['genre' => $genre]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $request->validated();
        $genre->update($request->all());
        Slug::ensureFor($genre, 'genre', $request->input('slug') ?: $genre->name);
        return redirect()->route('admin.genres.index')->with('success', __('messages.flash.genre.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        $genre->delete();
        return redirect()->route('admin.genres.index')->with('success', __('messages.flash.genre.deleted'));
    }
}
