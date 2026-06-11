<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    use HandlesImageUploads;

    public function index(Request $request)
    {
        $q = Team::query()->with('user:id,username');
        
        // Search
        if ($s = trim((string) $request->get('q'))) {
            $q->where('name', 'like', "%$s%")
              ->orWhere('description', 'like', "%$s%");
        }
        
        // Sorting
        $sort = $request->get('sort', 'id_desc');
        match($sort) {
            'name' => $q->orderBy('name'),
            'id_asc' => $q->orderBy('id'),
            default => $q->orderByDesc('id'),
        };
        
        $items = $q->paginate(30)->withQueryString();
        $total = Team::count();
        
        return view('admin.teams.index', compact('items', 'total'));
    }

    public function create()
    {
        return view('admin.teams.form', ['item' => new Team(), 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['photo'] = $this->upload($request);
        Team::create($data);
        return redirect()->route('admin.teams.index')->with('success', 'Đã tạo nhóm dịch!');
    }

    public function edit(Team $team)
    {
        return view('admin.teams.form', ['item' => $team, 'mode' => 'edit']);
    }

    public function update(Request $request, Team $team)
    {
        $data = $this->validateData($request);
        if ($photo = $this->upload($request)) {
            $data['photo'] = $photo;
        } elseif ($request->input('photo_remove') === '1') {
            $data['photo'] = null;
        }
        $team->update($data);
        return redirect()->route('admin.teams.index')->with('success', 'Đã cập nhật nhóm!');
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return redirect()->route('admin.teams.index')->with('success', 'Đã xoá nhóm.');
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
            return $this->storePublicImage($r->file('photo'), 'images/teams');
        }
        return $r->filled('photo_url') ? $r->input('photo_url') : null;
    }
}
