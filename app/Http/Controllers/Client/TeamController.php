<?php

namespace App\Http\Controllers\Client;

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

    private function own($id): Team
    {
        $t = Team::findOrFail($id);
        abort_unless($t->user_id === Auth::id(), 403);
        return $t;
    }

    public function index()
    {
        $items = Team::where('user_id', Auth::id())
            ->withCount('approvedMembers')
            ->orderByDesc('updated_at')
            ->paginate(24);
        return view('client.community.teams', compact('items'));
    }

    /** Trang công khai của nhóm */
    public function show(Team $team)
    {
        $team->load([
            'user:id,name,username',
            'approvedMembers.user:id,name,username,photo',
        ]);

        $articles = \App\Models\Article::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->select(['id', 'title', 'cover_image', 'team_id', 'updated_at'])
            ->with(['slug', 'chapters' => fn ($q) => $q->orderByDesc('number')->limit(1)])
            ->orderByDesc('updated_at')
            ->paginate(24);

        $totalChapters = \App\Models\Chapter::query()
            ->join('articles', 'articles.id', '=', 'chapters.article_id')
            ->where('articles.team_id', $team->id)
            ->count();

        $isMember = Auth::check() && $team->hasMember(Auth::id());
        $isLeader = Auth::check() && $team->isLeader(Auth::id());

        return view('client.community.team-show', compact('team', 'articles', 'totalChapters', 'isMember', 'isLeader'));
    }

    /** Dashboard nhóm — chỉ leader/admin nhóm */
    public function dashboard(Team $team)
    {
        abort_unless(Auth::check() && ($team->isLeader(Auth::id()) || $team->hasMember(Auth::id())), 403);

        $team->load('approvedMembers.user:id,name,username,photo');

        $totalArticles  = \App\Models\Article::withoutGlobalScopes()->where('team_id', $team->id)->count();
        $totalChapters  = \App\Models\Chapter::query()
            ->join('articles', 'articles.id', '=', 'chapters.article_id')
            ->where('articles.team_id', $team->id)->count();

        return view('client.community.team-dashboard', compact('team', 'totalArticles', 'totalChapters'));
    }

    /** Quản lý thành viên — chỉ leader */
    public function manageMembers($id)
    {
        $team = $this->own($id);
        $team->load(['members.user:id,name,username,photo', 'members.requester:id,name,username']);
        return view('client.community.team-members', compact('team'));
    }

    /** Leader cập nhật role thành viên */
    public function updateMember(Request $request, $id, TeamMember $member)
    {
        $team = $this->own($id);
        abort_if($member->team_id !== $team->id, 404);
        abort_if($member->role === 'leader', 403);

        $data = $request->validate(['role' => 'required|in:admin,editor,member']);
        $member->update($data);

        return back()->with('success', 'Đã cập nhật vai trò.');
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
        $data['status'] = Team::STATUS_PENDING;
        $team = Team::create($data);

        // Tự thêm creator là leader
        TeamMember::create([
            'team_id'     => $team->id,
            'user_id'     => Auth::id(),
            'role'        => 'leader',
            'status'      => 'approved',
            'requested_by'=> Auth::id(),
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('teams.show', $team->id)
            ->with('success', 'The team is under review by administrators');
    }

    public function edit($id)
    {
        $item = $this->own($id);
        $item->load([
            'members.user:id,name,username,photo',
            'members.requester:id,name,username',
        ]);
        return view('client.community.team-form', ['item' => $item, 'mode' => 'edit']);
    }

    public function update(Request $request, $id)
    {
        $item = $this->own($id);
        $data = $this->validateData($request);
        if ($photo = $this->upload($request)) $data['photo'] = $photo;
        $item->update($data);
        return redirect()->route('teams.index')->with('success', __('messages.flash.team.updated'));
    }

    public function destroy($id)
    {
        $this->own($id)->delete();
        return redirect()->route('teams.index')->with('success', __('messages.flash.team.deleted'));
    }

    /** User tự xin gia nhập nhóm từ trang public → chờ admin duyệt */
    public function joinRequest(Team $team)
    {
        $uid = Auth::id();

        if ($team->user_id === $uid) {
            return back()->with('error', 'Bạn là trưởng nhóm, không cần gia nhập.');
        }

        $exists = TeamMember::where('team_id', $team->id)->where('user_id', $uid)->first();
        if ($exists) {
            $msg = match($exists->status) {
                'approved' => 'Bạn đã là thành viên của nhóm này.',
                'pending'  => 'Yêu cầu của bạn đang chờ admin duyệt.',
                'rejected' => 'Yêu cầu trước đó đã bị từ chối.',
                default    => 'Bạn đã tồn tại trong nhóm.',
            };
            return back()->with('error', $msg);
        }

        TeamMember::create([
            'team_id'     => $team->id,
            'user_id'     => $uid,
            'role'        => 'member',
            'status'      => 'pending',
            'requested_by'=> $uid,
        ]);

        return back()->with('success', 'Đã gửi yêu cầu gia nhập. Admin sẽ xem xét trong thời gian sớm nhất.');
    }

    /** Trưởng nhóm gửi yêu cầu thêm thành viên → chờ admin duyệt */
    public function requestMember(Request $request, $id)
    {
        $team = $this->own($id);

        $data = $request->validate([
            'username' => 'required|string|exists:users,username',
        ]);

        $user = User::where('username', $data['username'])->firstOrFail();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Bạn là trưởng nhóm, không cần thêm chính mình.');
        }

        $exists = TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->first();
        if ($exists) {
            $msg = match($exists->status) {
                'approved' => 'User này đã là thành viên của nhóm.',
                'pending'  => 'Yêu cầu cho user này đang chờ admin duyệt.',
                'rejected' => 'Yêu cầu trước đó đã bị từ chối. Vui lòng liên hệ admin.',
                default    => 'User này đã tồn tại trong nhóm.',
            };
            return back()->with('error', $msg);
        }

        TeamMember::create([
            'team_id'     => $team->id,
            'user_id'     => $user->id,
            'role'        => 'member',
            'status'      => 'pending',
            'requested_by'=> Auth::id(),
        ]);

        return back()->with('success', "Đã gửi yêu cầu thêm @{$user->username}. Admin sẽ xem xét và duyệt trong thời gian sớm nhất.");
    }

    /** Trưởng nhóm huỷ yêu cầu đang pending hoặc xoá thành viên */
    public function removeMember(Request $request, $id, TeamMember $member)
    {
        $team = $this->own($id);
        abort_if($member->team_id !== $team->id, 404);
        abort_if($member->role === 'leader', 403, 'Không thể xoá trưởng nhóm.');

        $member->delete();
        return back()->with('success', 'Đã xoá thành viên khỏi nhóm.');
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
