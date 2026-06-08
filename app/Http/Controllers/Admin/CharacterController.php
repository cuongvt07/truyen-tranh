<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    public function index(Request $request)
    {
        $q = Character::query()->with('user:id,username')->withCount('articles');
        if ($s = trim((string) $request->get('q'))) {
            $q->where('name', 'like', "%$s%");
        }
        if ($request->filled('type')) {
            $q->where('type', (int) $request->get('type'));
        }
        $items = $q->orderByDesc('id')->paginate($request->get('per_page', 15))->withQueryString();
        $total = Character::count();
        return view('admin.characters.index', compact('items', 'total'));
    }

    public function create()
    {
        return view('admin.characters.form', ['item' => new Character(), 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['photo'] = $this->upload($request);
        Character::create($data);
        return redirect()->route('admin.characters.index')->with('success', 'Đã thêm nhân vật!');
    }

    public function edit(Character $character)
    {
        return view('admin.characters.form', ['item' => $character, 'mode' => 'edit']);
    }

    public function update(Request $request, Character $character)
    {
        $data = $this->validateData($request);
        if ($photo = $this->upload($request)) $data['photo'] = $photo;
        $character->update($data);
        return redirect()->route('admin.characters.index')->with('success', 'Đã cập nhật nhân vật!');
    }

    public function destroy(Character $character)
    {
        $character->articles()->detach();
        $character->delete();
        return redirect()->route('admin.characters.index')->with('success', 'Đã xoá nhân vật.');
    }

    private function validateData(Request $r): array
    {
        return $r->validate([
            'name'        => ['required', 'string', 'max:255'],
            'type'        => ['nullable', 'integer', 'in:0,1,2'],
            'description' => ['nullable', 'string'],
            'photo'       => ['nullable', 'image', 'max:4096'],
        ], [], ['name' => 'tên nhân vật']);
    }

    private function upload(Request $r): ?string
    {
        if ($r->hasFile('photo')) {
            $f = $r->file('photo');
            $n = time() . '-' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $f->getClientOriginalName());
            $f->move(public_path('images/characters'), $n);
            return '/images/characters/' . $n;
        }
        return $r->filled('photo_url') ? $r->input('photo_url') : null;
    }
}
