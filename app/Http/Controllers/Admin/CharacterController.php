<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Character;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    use HandlesImageUploads;

    public function index(Request $request)
    {
        $query = Character::query()->with('user:id,username')->withCount('articles');
        
        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', (int) $request->type);
        }
        
        // Sorting
        $sort = $request->get('sort', 'id_desc');
        switch ($sort) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'articles_count':
                $query->orderByDesc('articles_count');
                break;
            case 'id_asc':
                $query->orderBy('id');
                break;
            case 'id_desc':
            default:
                $query->orderByDesc('id');
        }

        $items = $query->paginate(30)->withQueryString();

        return view('admin.characters.index', [
            'items' => $items,
            'filters' => $request->only(['search', 'type', 'sort'])
        ]);
    }

    public function create()
    {
        return view('admin.characters.form', [
            'item' => new Character(),
            'mode' => 'create',
            'articleOptions' => Article::orderBy('title')->get(['id', 'title']),
            'selectedArticleIds' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $articleIds = $data['articles'] ?? [];
        unset($data['articles']);
        $data['photo'] = $this->upload($request);
        $character = Character::create($data);
        $character->articles()->sync($articleIds);
        return redirect()->route('admin.characters.index')->with('success', __('messages.flash.character.created'));
    }

    public function edit(Character $character)
    {
        return view('admin.characters.form', [
            'item' => $character,
            'mode' => 'edit',
            'articleOptions' => Article::orderBy('title')->get(['id', 'title']),
            'selectedArticleIds' => $character->articles()->pluck('articles.id')->all(),
        ]);
    }

    public function update(Request $request, Character $character)
    {
        $data = $this->validateData($request);
        $articleIds = $data['articles'] ?? [];
        unset($data['articles']);
        if ($photo = $this->upload($request)) {
            $data['photo'] = $photo;
        } elseif ($request->input('photo_remove') === '1') {
            $data['photo'] = null;
        }
        $character->update($data);
        $character->articles()->sync($articleIds);
        return redirect()->route('admin.characters.index')->with('success', __('messages.flash.character.updated'));
    }

    public function destroy(Character $character)
    {
        $character->articles()->detach();
        $character->delete();
        return redirect()->route('admin.characters.index')->with('success', __('messages.flash.character.deleted'));
    }

    private function validateData(Request $r): array
    {
        return $r->validate([
            'name'        => ['required', 'string', 'max:255'],
            'type'        => ['nullable', 'integer', 'in:0,1,2'],
            'description' => ['nullable', 'string'],
            'photo'       => ['nullable', 'image', 'max:4096'],
            'articles'    => ['nullable', 'array'],
            'articles.*'  => ['integer', 'exists:articles,id'],
        ], [], ['name' => 'tên nhân vật']);
    }

    private function upload(Request $r): ?string
    {
        if ($r->hasFile('photo')) {
            return $this->storePublicImage($r->file('photo'), 'images/characters');
        }
        return $r->filled('photo_url') ? $r->input('photo_url') : null;
    }
}
