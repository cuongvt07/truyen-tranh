<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    use HandlesImageUploads;

    public function index(Request $request)
    {
        $q = Team::query()->with('user:id,username')
            ->withCount(['approvedMembers', 'pendingMembers', 'articles']);

        if ($s = trim((string) $request->get('q'))) {
            $q->where('name', 'like', "%$s%")
              ->orWhere('description', 'like', "%$s%");
        }

        $sort = $request->get('sort', 'id_desc');
        match($sort) {
            'name'   => $q->orderBy('name'),
            'id_asc' => $q->orderBy('id'),
            default  => $q->orderByDesc('id'),
        };

        $items        = $q->paginate(30)->withQueryString();
        $total        = Team::count();
        $pendingCount = TeamMember::where('status', 'pending')->count();

        return view('admin.teams.index', compact('items', 'total', 'pendingCount'));
    }

    /** Danh sách yêu cầu thêm thành viên đang chờ duyệt */
    public function pendingRequests()
    {
        $members = TeamMember::with(['team', 'user:id,name,username,email', 'requester:id,name,username'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.teams.pending', compact('members'));
    }

    /** Trang quản lý thành viên của 1 nhóm */
    public function members(Team $team)
    {
        $team->load([
            'user:id,name,username',
            'members.user:id,name,username,email',
            'members.requester:id,name,username',
            'members.approver:id,name,username',
        ]);

        return view('admin.teams.members', compact('team'));
    }

    /** Admin thêm thành viên trực tiếp (không cần duyệt) */
    public function addMember(Request $request, Team $team)
    {
        $data = $request->validate([
            'username' => 'required|string|exists:users,username',
            'role'     => 'required|in:leader,member',
        ]);

        $user = User::where('username', $data['username'])->firstOrFail();

        if (TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User này đã có trong nhóm.');
        }

        TeamMember::create([
            'team_id'     => $team->id,
            'user_id'     => $user->id,
            'role'        => $data['role'],
            'status'      => 'approved',
            'requested_by'=> Auth::id(),
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Đã thêm @{$user->username} vào nhóm.");
    }

    /** Admin duyệt yêu cầu thêm thành viên */
    public function approveMember(Team $team, TeamMember $member)
    {
        abort_if($member->team_id !== $team->id, 404);

        $member->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Đã duyệt thành viên.');
    }

    /** Admin từ chối yêu cầu */
    public function rejectMember(Request $request, Team $team, TeamMember $member)
    {
        abort_if($member->team_id !== $team->id, 404);

        $member->update([
            'status' => 'rejected',
            'note'   => $request->input('note'),
        ]);

        return back()->with('success', 'Đã từ chối yêu cầu.');
    }

    /** Admin xoá thành viên khỏi nhóm */
    public function removeMember(Team $team, TeamMember $member)
    {
        abort_if($member->team_id !== $team->id, 404);
        $member->delete();

        return back()->with('success', 'Đã xoá thành viên khỏi nhóm.');
    }

    public function create()
    {
        return view('admin.teams.form', ['item' => new Team(), 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['photo'] = $this->upload($request);
        $team = Team::create($data);

        // Tự thêm creator là leader nếu có user_id
        if ($team->user_id) {
            TeamMember::create([
                'team_id'     => $team->id,
                'user_id'     => $team->user_id,
                'role'        => 'leader',
                'status'      => 'approved',
                'requested_by'=> Auth::id(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        }

        return redirect()->route('admin.teams.index')->with('success', __('messages.flash.team.created'));
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
        return redirect()->route('admin.teams.index')->with('success', __('messages.flash.team.updated'));
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return redirect()->route('admin.teams.index')->with('success', __('messages.flash.team.deleted'));
    }

    private function validateData(Request $r): array
    {
        return $r->validate([
            'user_id'       => ['nullable', 'integer', 'exists:users,id'],
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
