<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    private function own($id): Collection
    {
        $c = Collection::findOrFail($id);
        abort_unless($c->user_id === Auth::id(), 403);
        return $c;
    }

    public function index()
    {
        $items = Collection::where('user_id', Auth::id())
            ->withCount('articles')->orderByDesc('updated_at')->paginate(24);
        return view('client.community.collections', compact('items'));
    }

    public function create()
    {
        return view('client.community.collection-form', [
            'item' => new Collection(), 'mode' => 'create',
            'articles' => Article::orderBy('title')->limit(200)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['user_id'] = Auth::id();
        $collection = Collection::create($data);
        $collection->articles()->sync($request->input('books', []));
        return redirect()->route('collections.index')->with('success', 'Tạo bộ sưu tập thành công!');
    }

    public function edit($id)
    {
        return view('client.community.collection-form', [
            'item' => $this->own($id), 'mode' => 'edit',
            'articles' => Article::orderBy('title')->limit(200)->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = $this->own($id);
        $item->update($this->validateData($request));
        $item->articles()->sync($request->input('books', []));
        return redirect()->route('collections.index')->with('success', 'Cập nhật bộ sưu tập!');
    }

    public function destroy($id)
    {
        $item = $this->own($id);
        $item->articles()->detach();
        $item->delete();
        return redirect()->route('collections.index')->with('success', 'Đã xoá bộ sưu tập.');
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
