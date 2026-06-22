<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    use HandlesImageUploads;

    private function own($id): Team
    {
        $t = Team::findOrFail($id);
        abort_unless($t->isLeader(Auth::id()), 403);
        return $t;
    }

    public function index()
    {
        $items = Team::where(function ($q) {
                $q->where('user_id', Auth::id())
                    ->orWhereHas('members', function ($m) {
                        $m->where('user_id', Auth::id())
                            ->where('status', 'approved')
                            ->where('role', 'leader');
                    });
            })
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
            'approvedMembers.user:id,name,username,avatar',
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

        $team->load('approvedMembers.user:id,name,username,avatar');

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

        $totalArticles  = \App\Models\Article::withoutGlobalScopes()->where('team_id', $team->id)->count();
        $totalChapters  = \App\Models\Chapter::query()
            ->join('articles', 'articles.id', '=', 'chapters.article_id')
            ->where('articles.team_id', $team->id)->count();

        $monthlyLikes = $this->teamLikesQuery($team->id)
            ->whereBetween('chapter_likes.created_at', [$monthStart, $monthEnd])
            ->count();

        $previousMonthlyLikes = $this->teamLikesQuery($team->id)
            ->whereBetween('chapter_likes.created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $monthlyCoupons = (int) $this->teamCouponsQuery($team->id)
            ->whereBetween('chapter_unlocks.created_at', [$monthStart, $monthEnd])
            ->sum('chapter_unlocks.credits_spent');

        $previousMonthlyCoupons = (int) $this->teamCouponsQuery($team->id)
            ->whereBetween('chapter_unlocks.created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('chapter_unlocks.credits_spent');

        $teamBalance = (int) $this->teamCouponsQuery($team->id)->sum('chapter_unlocks.credits_spent');

        $chartDays = collect(range(14, 0))->map(fn ($i) => now()->subDays($i)->startOfDay());
        $chartStart = $chartDays->first()->copy()->startOfDay();
        $chartEnd = $chartDays->last()->copy()->endOfDay();

        $likesByDay = $this->teamLikesQuery($team->id)
            ->whereBetween('chapter_likes.created_at', [$chartStart, $chartEnd])
            ->selectRaw('DATE(chapter_likes.created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $couponsByDay = $this->teamCouponsQuery($team->id)
            ->whereBetween('chapter_unlocks.created_at', [$chartStart, $chartEnd])
            ->selectRaw('DATE(chapter_unlocks.created_at) as day, SUM(chapter_unlocks.credits_spent) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $chartData = $chartDays->map(function ($day) use ($likesByDay, $couponsByDay) {
            $key = $day->format('Y-m-d');

            return [
                'label' => $day->format('d M'),
                'likes' => (int) ($likesByDay[$key] ?? 0),
                'coupons' => (int) ($couponsByDay[$key] ?? 0),
            ];
        });
        $chartMax = max(1, $chartData->max('likes'), $chartData->max('coupons'));

        $topLikedArticles = DB::table('articles')
            ->join('chapters', 'chapters.article_id', '=', 'articles.id')
            ->join('chapter_likes', 'chapter_likes.chapter_id', '=', 'chapters.id')
            ->where('articles.team_id', $team->id)
            ->whereBetween('chapter_likes.created_at', [$monthStart, $monthEnd])
            ->groupBy('articles.id', 'articles.title')
            ->select('articles.id', 'articles.title')
            ->selectRaw('COUNT(*) as total')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topCouponArticles = DB::table('articles')
            ->join('chapter_unlocks', 'chapter_unlocks.article_id', '=', 'articles.id')
            ->where('articles.team_id', $team->id)
            ->whereBetween('chapter_unlocks.created_at', [$monthStart, $monthEnd])
            ->groupBy('articles.id', 'articles.title')
            ->select('articles.id', 'articles.title')
            ->selectRaw('SUM(chapter_unlocks.credits_spent) as total')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $likesCompare = $this->compareMetric($monthlyLikes, $previousMonthlyLikes);
        $couponsCompare = $this->compareMetric($monthlyCoupons, $previousMonthlyCoupons);

        return view('client.community.team-dashboard', compact(
            'team',
            'totalArticles',
            'totalChapters',
            'monthlyLikes',
            'monthlyCoupons',
            'teamBalance',
            'chartData',
            'chartMax',
            'topLikedArticles',
            'topCouponArticles',
            'likesCompare',
            'couponsCompare'
        ));
    }

    /** Quản lý thành viên — chỉ leader */
    public function manageMembers($id)
    {
        $team = $this->own($id);
        $team->load(['members.user:id,name,username,avatar', 'members.requester:id,name,username']);
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
        $this->ensurePurchased();
        return view('client.community.team-form', ['item' => new Team(), 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $this->ensurePurchased();
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
            ->with('success', __('messages.community.flash_team_pending'));
    }

    public function edit($id)
    {
        $item = $this->own($id);
        $item->load([
            'members.user:id,name,username,avatar',
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
            return back()->with('error', __('messages.community.flash_owner_no_join'));
        }

        $exists = TeamMember::where('team_id', $team->id)->where('user_id', $uid)->first();
        if ($exists) {
            $msg = match($exists->status) {
                'approved' => __('messages.community.flash_already_member'),
                'pending'  => __('messages.community.flash_join_pending'),
                'rejected' => __('messages.community.flash_join_rejected'),
                default    => __('messages.community.flash_member_exists'),
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

        return back()->with('success', __('messages.community.flash_join_sent'));
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
            return back()->with('error', __('messages.community.flash_owner_no_add_self'));
        }

        $exists = TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->first();
        if ($exists) {
            $msg = match($exists->status) {
                'approved' => __('messages.community.flash_user_already_member'),
                'pending'  => __('messages.community.flash_user_pending'),
                'rejected' => __('messages.community.flash_user_rejected'),
                default    => __('messages.community.flash_user_exists'),
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

        return back()->with('success', __('messages.community.flash_member_request_sent', ['username' => $user->username]));
    }

    /** Trưởng nhóm huỷ yêu cầu đang pending hoặc xoá thành viên */
    public function removeMember(Request $request, $id, TeamMember $member)
    {
        $team = $this->own($id);
        abort_if($member->team_id !== $team->id, 404);
        abort_if($member->role === 'leader', 403, __('messages.community.flash_cannot_remove_leader'));

        $member->delete();
        return back()->with('success', __('messages.community.flash_member_removed'));
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
        ], [], ['name' => __('messages.community.team_name')]);
    }

    private function upload(Request $r): ?string
    {
        if ($r->hasFile('photo')) {
            return $this->storePublicImage($r->file('photo'), 'images/teams');
        }
        return $r->filled('photo_url') ? $r->input('photo_url') : null;
    }

    private function teamLikesQuery(int $teamId)
    {
        return DB::table('chapter_likes')
            ->join('chapters', 'chapters.id', '=', 'chapter_likes.chapter_id')
            ->join('articles', 'articles.id', '=', 'chapters.article_id')
            ->where('articles.team_id', $teamId);
    }

    private function teamCouponsQuery(int $teamId)
    {
        return DB::table('chapter_unlocks')
            ->join('articles', 'articles.id', '=', 'chapter_unlocks.article_id')
            ->where('articles.team_id', $teamId);
    }

    private function compareMetric(int $current, int $previous): string
    {
        if ($previous <= 0) {
            return $current > 0
                ? __('messages.community.compare_new_activity')
                : __('messages.community.compare_no_data');
        }

        $percent = (($current - $previous) / $previous) * 100;
        $prefix = $percent >= 0 ? '+' : '';

        return __('messages.community.compare_vs_previous', [
            'percent' => $prefix . number_format($percent, 1),
        ]);
    }
}
