<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $q = Collection::query()->with('user:id,username')->withCount('articles');
        if ($s = trim((string) $request->get('q'))) {
            $q->where('name', 'like', "%$s%");
        }
        $items = $q->orderByDesc('id')->paginate($request->get('per_page', 15))->withQueryString();
        $total = Collection::count();
        return view('admin.collections.index', compact('items', 'total'));
    }

    public function create()
    {
        return view('admin.collections.form', [
            'item' => new Collection(), 'mode' => 'create',
            'articles' => Article::orderBy('title')->limit(300)->get(['id', 'title']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $c = Collection::create($data);
        $c->articles()->sync($request->input('books', []));
        return redirect()->route('admin.collections.index')->with('success', 'Đã tạo bộ sưu tập!');
    }

    public function edit(Collection $collection)
    {
        return view('admin.collections.form', [
            'item' => $collection, 'mode' => 'edit',
            'articles' => Article::orderBy('title')->limit(300)->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Collection $collection)
    {
        $collection->update($this->validateData($request));
        $collection->articles()->sync($request->input('books', []));
        return redirect()->route('admin.collections.index')->with('success', 'Đã cập nhật bộ sưu tập!');
    }

    public function destroy(Collection $collection)
    {
        $collection->articles()->detach();
        $collection->delete();
        return redirect()->route('admin.collections.index')->with('success', 'Đã xoá bộ sưu tập.');
    }

    private function validateData(Request $r): array
    {
        return $r->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_private'  => ['nullable', 'boolean'],
            'books'       => ['nullable', 'array'],
            'books.*'     => ['integer', 'exists:articles,id'],
        ], [], ['name' => 'tên bộ sưu tập']);
    }
}
