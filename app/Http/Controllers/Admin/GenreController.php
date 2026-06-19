<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Requests\Admin\Genre\StoreGenreRequest;
use App\Http\Requests\Admin\Genre\UpdateGenreRequest;
use App\Models\Genre;
use App\Models\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GenreController extends Controller
{
    use HandlesImageUploads;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Genre::query()->with('slug')->withCount('articles');
        
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
        $data = $request->safe()->only(['name', 'description']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storePublicImage($request->file('cover_image'), 'images/genres');
        }

        $genre = Genre::create($data);
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
        $data = $request->safe()->only(['name', 'description']);
        $oldImage = $genre->cover_image;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storePublicImage($request->file('cover_image'), 'images/genres');
        } elseif ($request->boolean('cover_image_remove')) {
            $data['cover_image'] = null;
        }

        $genre->update($data);

        if (($request->hasFile('cover_image') || $request->boolean('cover_image_remove')) && $oldImage !== $genre->cover_image) {
            $this->deleteStoredImage($oldImage);
        }

        Slug::ensureFor($genre, 'genre', $request->input('slug') ?: $genre->name);
        return redirect()->route('admin.genres.index')->with('success', __('messages.flash.genre.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        $image = $genre->cover_image;
        $genre->delete();
        $this->deleteStoredImage($image);

        return redirect()->route('admin.genres.index')->with('success', __('messages.flash.genre.deleted'));
    }

    private function deleteStoredImage(?string $url): void
    {
        if ($url && str_starts_with($url, '/storage/')) {
            Storage::disk('public')->delete(substr($url, strlen('/storage/')));
        }
    }
}
