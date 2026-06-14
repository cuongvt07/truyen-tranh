<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CharacterController extends Controller
{
    use HandlesImageUploads;

    private function own($id): Character
    {
        $c = Character::findOrFail($id);
        abort_unless($c->user_id === Auth::id(), 403);
        return $c;
    }

    public function index()
    {
        $items = Character::where('user_id', Auth::id())->orderByDesc('updated_at')->paginate(24);
        return view('client.community.characters', compact('items'));
    }

    public function create()
    {
        return view('client.community.character-form', ['item' => new Character(), 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['user_id'] = Auth::id();
        $data['photo'] = $this->upload($request);
        // 'type' là cột NOT NULL (default 0): ép int + default 0 để tránh null -> lỗi DB.
        $data['type'] = (int) ($data['type'] ?? 0);
        Character::create($data);
        return redirect()->route('characters.index')->with('success', 'Thêm nhân vật thành công!');
    }

    public function edit($id)
    {
        return view('client.community.character-form', ['item' => $this->own($id), 'mode' => 'edit']);
    }

    public function update(Request $request, $id)
    {
        $item = $this->own($id);
        $data = $this->validateData($request);
        if ($photo = $this->upload($request)) $data['photo'] = $photo;
        // Chỉ đụng 'type' khi có gửi lên; ép int để không bao giờ ghi null (cột NOT NULL).
        if (array_key_exists('type', $data)) $data['type'] = (int) ($data['type'] ?? 0);
        $item->update($data);
        return redirect()->route('characters.index')->with('success', 'Cập nhật nhân vật!');
    }

    public function destroy($id)
    {
        $this->own($id)->delete();
        return redirect()->route('characters.index')->with('success', 'Đã xoá nhân vật.');
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
            return $this->storePublicImage($r->file('photo'), 'images/characters');
        }
        return $r->filled('photo_url') ? $r->input('photo_url') : null;
    }
}
