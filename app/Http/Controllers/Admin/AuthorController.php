<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Author\StoreAuthorRequest;
use App\Http\Requests\Admin\Author\UpdateAuthorRequest;
use App\Models\Author;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Author::query()->withCount('articles');
        
        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }
        
        // Sorting
        $sort = $request->get('sort', 'name');
        switch ($sort) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'articles_count':
                $query->orderByDesc('articles_count');
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

        $authors = $query->paginate(30)->withQueryString();
        
        return view('admin.authors.index', [
            'authors' => $authors,
            'filters' => $request->only(['search', 'sort'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $author = new Author();
        return view('admin.authors.create', ['author' => $author]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuthorRequest $request): RedirectResponse
    {
        $request->validated();
        $author = Author::create($request->all());
        return redirect()->route('admin.authors.index')
            ->with('success', __('messages.flash.author.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Author $author)
    {
        return view('admin.authors.edit', ['author' => $author]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuthorRequest $request, Author $author)
    {
        $request->validated();
        $author->update($request->all());
        return redirect()->route('admin.authors.index')
            ->with('success', __('messages.flash.author.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
        $author->delete();
        return redirect()->route('admin.authors.index')
            ->with('success', __('messages.flash.author.deleted'));
    }
}
