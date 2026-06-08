<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    private function own($id): Team
    {
        $t = Team::findOrFail($id);
        abort_unless($t->user_id === Auth::id(), 403);
        return $t;
    }

    public function index()
    {
        $items = Team::where('user_id', Auth::id())->orderByDesc('updated_at')->paginate(24);
        return view('client.community.teams', compact('items'));
    }

    public function create()
    {
        return view('client.community.team-form', ['item' => new Team(), 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['user_id'] = Auth::id();
        $data['photo'] = $this->upload($request);
        Team::create($data);
        return redirect()->route('teams.index')->with('success', 'Tạo nhóm dịch thành công!');
    }

    public function edit($id)
    {
        return view('client.community.team-form', ['item' => $this->own($id), 'mode' => 'edit']);
    }

    public function update(Request $request, $id)
    {
        $item = $this->own($id);
        $data = $this->validateData($request);
        if ($photo = $this->upload($request)) $data['photo'] = $photo;
        $item->update($data);
        return redirect()->route('teams.index')->with('success', 'Cập nhật nhóm dịch!');
    }

    public function destroy($id)
    {
        $this->own($id)->delete();
        return redirect()->route('teams.index')->with('success', 'Đã xoá nhóm dịch.');
    }

    private function validateData(Request $r): array
    {
        return $r->validate([
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'site'          => ['nullable', 'string', 'max:255'],
            'donation_text' => ['nullable', 'string', 'max:255'],
            'donation_url'  => ['nullable', 'string', 'max:255'],
            'photo'         => ['nullable', 'image', 'max:4096'],
        ], [], ['name' => 'tên nhóm']);
    }

    private function upload(Request $r): ?string
    {
        if ($r->hasFile('photo')) {
            $f = $r->file('photo');
            $n = time() . '-' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $f->getClientOriginalName());
            $f->move(public_path('images/teams'), $n);
            return '/images/teams/' . $n;
        }
        return $r->filled('photo_url') ? $r->input('photo_url') : null;
    }
}
