<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $q = Tag::query()->withCount('articles');
        if ($s = trim((string) $request->get('q'))) {
            $q->where('name', 'like', "%$s%");
        }
        $items = $q->orderByDesc('articles_count')->orderBy('name')
                   ->paginate($request->get('per_page', 30))->withQueryString();
        $total = Tag::count();
        $allTags = Tag::orderBy('name')->get(['id', 'name']);
        return view('admin.tags.index', compact('items', 'total', 'allTags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:tags,name']], [], ['name' => 'tên tag']);
        Tag::create($data);
        return back()->with('success', 'Đã thêm tag.');
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:tags,name,' . $tag->id]], [], ['name' => 'tên tag']);
        $tag->update($data);
        return back()->with('success', 'Đã cập nhật tag.');
    }

    public function destroy(Tag $tag)
    {
        $tag->articles()->detach();
        $tag->delete();
        return back()->with('success', 'Đã xoá tag.');
    }

    /** Gộp nhiều tag vào 1 tag đích. */
    public function merge(Request $request)
    {
        $data = $request->validate([
            'target_id' => ['required', 'integer', 'exists:tags,id'],
            'source_ids' => ['required', 'array', 'min:1'],
            'source_ids.*' => ['integer', 'exists:tags,id'],
        ]);
        $target = Tag::findOrFail($data['target_id']);
        $sources = Tag::whereIn('id', $data['source_ids'])->where('id', '!=', $target->id)->get();

        foreach ($sources as $src) {
            $articleIds = $src->articles()->pluck('articles.id')->all();
            $target->articles()->syncWithoutDetaching($articleIds);
            $src->articles()->detach();
            $src->delete();
        }
        return back()->with('success', 'Đã gộp ' . $sources->count() . ' tag vào «' . $target->name . '».');
    }
}
