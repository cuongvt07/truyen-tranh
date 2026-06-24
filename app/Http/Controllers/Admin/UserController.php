<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreBanUserRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateBanUserRequest;
use App\Http\Requests\Admin\User\UpdateRoleUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\BannedUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = $this->filter($request, User::query());

        return $this->renderUserView($request, $users, 'Tất cả tài khoản');
    }

    public function showAdmins(Request $request)
    {
        $admins = $this->filter($request, User::getAdmins());

        return $this->renderUserView($request, $admins, 'Tài khoản quản trị viên');
    }

    public function showPosters(Request $request)
    {
        $posters = $this->filter($request, User::getPosters());

        return $this->renderUserView($request, $posters, 'Tài khoản người đăng bài');
    }

    public function showBanneds(Request $request)
    {
        $banneds = $this->filter($request, User::getBanneds());

        return $this->renderUserView($request, $banneds, 'Tài khoản bị cấm');
    }

    private function renderUserView(Request $request, $users, string $title)
    {
        return view('admin.users.index', [
            'users' => $users,
            'title' => $title,
            'createUserRoute' => route('admin.users.create'),
            'filters' => $request->only(['search', 'role', 'ban_status', 'verified', 'sort']),
        ]);
    }

    public function filter(Request $request, $users)
    {
        $users->with(['banned.admin'])
            ->withCount(['articles', 'comments', 'bookmarks', 'chapterUnlocks']);

        if ($request->filled('search')) {
            $searchText = trim($request->input('search'));
            $users->where(function ($query) use ($searchText) {
                $query->where('username', 'like', '%' . $searchText . '%')
                    ->orWhere('name', 'like', '%' . $searchText . '%')
                    ->orWhere('email', 'like', '%' . $searchText . '%');
            });
        }

        $roleValues = array_map(static function (UserRole $role) {
            return $role->value;
        }, UserRole::cases());
        if ($request->filled('role') && in_array((int) $request->role, $roleValues, true)) {
            $users->where('role', (int) $request->role);
        }

        if ($request->filled('ban_status')) {
            match ($request->ban_status) {
                'banned' => $users->whereHas('banned'),
                'active' => $users->whereDoesntHave('banned'),
                default => null,
            };
        }

        if ($request->filled('verified')) {
            match ($request->verified) {
                'yes' => $users->whereNotNull('email_verified_at'),
                'no' => $users->whereNull('email_verified_at'),
                default => null,
            };
        }

        match ($request->get('sort', 'id_desc')) {
            'username' => $users->orderBy('username'),
            'name' => $users->orderBy('name'),
            'points' => $users->orderByDesc('points'),
            'articles_count' => $users->orderByDesc('articles_count'),
            'comments_count' => $users->orderByDesc('comments_count'),
            'id_asc' => $users->orderBy('id'),
            default => $users->orderByDesc('id'),
        };

        return $users->paginate(30)->withQueryString();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $user = new User();
        $user->username = $data['username'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->role = $data['role'];
        $user->avatar = '/images/users/default.jpg';
        $user->description = 'Chưa có mô tả';

        if (isset($data['address'])) {
            $user->address = $data['address'];
        }

        if (isset($data['date_of_birth'])) {
            $user->date_of_birth = $data['date_of_birth'];
        }

        if (isset($data['gender'])) {
            $user->gender = $data['gender'];
        }

        $user->save();

        // Credit khởi tạo (nếu admin nhập) -> ghi ledger thay vì set thẳng.
        $initPoints = (int) ($data['points'] ?? 0);
        if ($initPoints > 0) {
            \App\Services\CreditService::adjust($user->id, $initPoints, 'admin_adjust', [
                'admin_id'    => \Illuminate\Support\Facades\Auth::id(),
                'description' => 'Khởi tạo khi tạo tài khoản',
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('messages.flash.user.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $user->username = $data['username'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }
        $user->role = $data['role'];
        $oldPoints = (int) $user->points;
        $user->save();

        // Chỉnh credit tay -> ghi ledger phần chênh lệch (admin_adjust).
        if (isset($data['points'])) {
            $delta = (int) $data['points'] - $oldPoints;
            if ($delta !== 0) {
                \App\Services\CreditService::adjust($user->id, $delta, 'admin_adjust', [
                    'admin_id' => \Illuminate\Support\Facades\Auth::id(),
                ]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('messages.flash.user.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    public function editRole(User $user)
    {
        return view('admin.users.edit-role', ['user' => $user]);
    }

    public function updateRole(UpdateRoleUserRequest $request, User $user)
    {
        $data = $request->validated();
        $user->role = $data['role'];
        $user->save();

        return redirect()->route('admin.users.index')->with('success', __('messages.flash.user.role_updated'));
    }

    public function createBan(User $user)
    {
        return view('admin.users.create-ban', ['user' => $user]);
    }

    public function storeBan(StoreBanUserRequest $request, User $user)
    {
        $data = $request->validated();
        $bannedUser = new BannedUser();
        $bannedUser->user_id = $user->id;
        $bannedUser->admin_id = Auth::user()->getAuthIdentifier();
        $bannedUser->reason = $data['reason'];
        $bannedUser->expired_at = isset($data['ban_days']) ? now()->addDays((int) $data['ban_days']) : null;
        $bannedUser->save();

        // Force the banned user to refresh their login state.
        $user->setShouldReLogin(true);

        return redirect()->route('admin.users.index')
            ->with('success', __('messages.flash.user.banned'));
    }

    public function editBan(User $user)
    {
        return view('admin.users.edit-ban', ['user' => $user]);
    }

    public function updateBan(UpdateBanUserRequest $request, User $user)
    {
        $data = $request->validated();
        $bannedUser = $user->banned;
        $bannedUser->reason = $data['reason'];
        if (array_key_exists('ban_days', $data)) {
            $bannedUser->expired_at = $data['ban_days'] ? now()->addDays((int) $data['ban_days']) : null;
        }
        $bannedUser->save();

        // Force the banned user to refresh their login state.
        $user->setShouldReLogin(true);

        return redirect()->route('admin.users.banned')
            ->with('success', __('messages.flash.user.ban_updated'));
    }

    public function unban(User $user)
    {
        $bannedUser = $user->banned;
        $bannedUser->delete();

        // Force the unbanned user to refresh their login state.
        $user->setShouldReLogin(true);

        return redirect()->route('admin.users.banned')
            ->with('success', __('messages.flash.user.unbanned'));
    }
}
