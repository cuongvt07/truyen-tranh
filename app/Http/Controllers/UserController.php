<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Article;
use App\Models\BannedUser;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use function PHPUnit\Framework\returnCallback;

class UserController extends Controller
{
    public function show($id = null)
    {
        if (is_null($id)) {
            $user = Auth::user();
        } else {
            if ($id == Auth::id()) {
                return redirect()->route('users.show', null);
            }
            $user = User::query()->find($id);
            if (is_null($user)) {
                return redirect()->route('users.show', null);
            }
        }

        $isMine = $user->id === Auth::id();

        // Tab "List": danh sách truyện đã thêm vào list, gom theo trạng thái.
        // Xem hồ sơ người khác chỉ thấy bookmark công khai (is_public).
        $bookmarksQuery = \App\Models\Bookmark::with('article')
            ->where('user_id', $user->id);
        if (! $isMine) {
            $bookmarksQuery->where('is_public', true);
        }
        $bookmarks = $bookmarksQuery->orderByDesc('updated_at')->get()
            ->filter(fn ($b) => $b->article !== null)
            ->values();

        // "Continue (N)": chương đọc gần nhất theo lịch sử; fallback chương mới nhất.
        $continueMap = $isMine
            ? \App\Models\ReadingHistory::where('user_id', $user->id)
                ->whereIn('article_id', $bookmarks->pluck('article_id'))
                ->orderByDesc('read_at')
                ->get()
                ->unique('article_id')
                ->mapWithKeys(fn ($h) => [$h->article_id => $h->chapter_number])
            : collect();

        return view('client.users.list', [
            'user' => $user,
            'bookmarks' => $bookmarks,
            'continueMap' => $continueMap,
            'isMine' => $isMine,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->validated();
        $user = $request->user();
        // BẢO MẬT: KHÔNG dùng $request->all() — 'role' và 'points' nằm trong $fillable,
        // nếu fill thẳng thì user thường chỉ cần POST thêm role=1 (ADMIN) hoặc points=999999
        // là leo quyền / tự cộng credit, bỏ qua toàn bộ kiểm tra ở JS/UI.
        // Chỉ nhận đúng các field hồ sơ được phép sửa.
        $validatedData = $request->only([
            'name', 'username', 'email', 'gender', 'date_of_birth', 'description',
        ]);
        // Lưu ảnh trong try/catch: nếu ghi đĩa lỗi (quyền, hết chỗ, định dạng lạ) thì
        // BÁO LỖI THÂN THIỆN thay vì để bung ra trang 500.
        try {
            if ($request->hasFile('avatar')) {
                $image = $request->file('avatar');
                $ext = $image->extension() ?: $image->getClientOriginalExtension() ?: 'jpg';
                $path = $image->storeAs('images/users', $user->id . '.' . $ext, 'public');
                // Thêm ?v=timestamp để trình duyệt không hiện ảnh cũ trong cache (cùng tên file).
                $validatedData['avatar'] = '/storage/' . $path . '?v=' . now()->timestamp;
            }
            if ($request->hasFile('background')) {
                $bg = $request->file('background');
                $ext = $bg->extension() ?: $bg->getClientOriginalExtension() ?: 'jpg';
                $path = $bg->storeAs('images/users', $user->id . '-bg.' . $ext, 'public');
                $validatedData['background'] = '/storage/' . $path . '?v=' . now()->timestamp;
            }
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('users.change_info')
                ->withErrors(['avatar' => __('messages.account.upload_failed')])
                ->withInput();
        }
        $request->user()->fill($validatedData);
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()->route('users.change_info')
            ->with('status', __('messages.flash.profile_updated'));
    }

    public function changeInfo(Request $request): View
    {
        return view('client.users.change-info', [
            'user' => $request->user(),
        ]);
    }

    public function showPostedArticles(User $user): View
    {
        $articles = $user->articles()->orderByDesc('updated_at')->paginate();
        return view('client.users.posted-articles', [
            'articles' => $articles,
            'user' => $user,
        ]);
    }

    public function showBookmarks(User $user): View
    {
        $isMine = Auth::id() === $user->id;
        $bookmarks = $user->bookmarks()
            ->when(!$isMine, fn ($query) => $query->where('is_public', true))
            ->whereHas(lcfirst(class_basename(Article::class)))
            ->orderByDesc('updated_at')
            ->paginate();
        return view('client.users.bookmarks', [
            'bookmarks' => $bookmarks,
            'user' => $user,
        ]);
    }

    public function showComments(User $user): View
    {
        $comments = $user->comments()
            ->whereHas(lcfirst(class_basename(Article::class)))
            ->orderByDesc('created_at')->paginate();
        return view('client.users.comments', [
            'comments' => $comments,
            'user' => $user,
        ]);
    }

    public function changePassword(Request $request): View
    {
        return view('client.users.change-password', [
            'user' => $request->user(),
        ]);
    }

    public function notifications(User $user): View
    {
        $this->authorizePrivateProfile($user);
        $isMine = true;
        $notifications = $user->notifications()->paginate(20);
        // Xem tab của chính mình -> đánh dấu đã đọc (cập nhật badge chuông).
        if ($isMine) {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }
        return view('client.users.notifications', compact('user', 'notifications', 'isMine'));
    }

    public function collections(User $user): View
    {
        $isMine = \Illuminate\Support\Facades\Auth::id() === $user->id;
        $collections = \App\Models\Collection::where('user_id', $user->id)
            ->when(!$isMine, fn ($q) => $q->where('is_private', false))
            ->withCount('articles')->orderByDesc('updated_at')->get();
        return view('client.users.collections', compact('user', 'collections', 'isMine'));
    }

    public function teams(User $user): View
    {
        $isMine = \Illuminate\Support\Facades\Auth::id() === $user->id;

        // Team mà user là leader (dựa vào TeamMember, không phụ thuộc user_id trên bảng teams)
        $ownedTeams = \App\Models\Team::whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('role', 'leader')
                  ->where('status', 'approved');
            })
            ->withCount('approvedMembers')
            ->orderByDesc('updated_at')
            ->get();

        // Team mà user là approved member (không phải leader)
        $memberTeams = \App\Models\Team::whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'approved')
                  ->where('role', '!=', 'leader');
            })
            ->with(['members' => function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'approved');
            }])
            ->withCount('approvedMembers')
            ->orderByDesc('updated_at')
            ->get();

        return view('client.users.teams', compact('user', 'ownedTeams', 'memberTeams', 'isMine'));
    }

    public function favourites(User $user): View
    {
        return view('client.users.favourites', ['user' => $user]);
    }

    public function achievements(User $user): View
    {
        // Sync trước khi render để mở khoá achievements mới
        if (Auth::id() === $user->id) {
            AchievementService::sync($user);
        }
        $achievementData = AchievementService::forUser($user);

        return view('client.users.achievements', array_merge(['user' => $user], $achievementData));
    }

    public function suggestions(User $user): View
    {
        return view('client.users.suggestions', ['user' => $user]);
    }

    public function banlist(User $user): View
    {
        $this->authorizePrivateProfile($user);
        return view('client.users.banlist', ['user' => $user]);
    }

    public function readingHistory(User $user): View
    {
        $this->authorizePrivateProfile($user);
        $history = \App\Models\ReadingHistory::where('user_id', $user->id)
            ->with(['article', 'chapter'])
            ->orderByDesc('read_at')
            ->get()
            ->unique('article_id')
            ->values();

        return view('client.users.reading-history', compact('user', 'history'));
    }

    public function transactions(User $user): View
    {
        $this->authorizePrivateProfile($user);
        $deposits = \App\Models\Deposit::where('user_id', $user->id)
            ->orderByDesc('created_at')->paginate(15);
        return view('client.users.transactions', [
            'user' => $user,
            'deposits' => $deposits,
        ]);
    }

    public function handleBanned(Request $request)
    {
        $bannedUser = $request->user()->banned;
        if (!$bannedUser) {
            return redirect()->route('home.index');
        }
        return view('client.users.banned', [
            'bannedUser' => $bannedUser,
        ]);
    }

    private function authorizePrivateProfile(User $user): void
    {
        abort_unless(Auth::check() && Auth::id() === $user->id, 403);
    }
}
