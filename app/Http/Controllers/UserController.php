<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Article;
use App\Models\BannedUser;
use App\Models\User;
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
        return view('client.users.general', [
            'user' => $user,
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
        $validatedData = $request->all();
        if($request->hasfile('avatar')) {
            $image = $request->file('avatar');
            $imageName = $user->id . '.' . $image->extension();
            $path = $image->storeAs('images/users', $imageName, 'public');
            $validatedData['avatar'] = '/storage/' . $path;
        }
        if($request->hasfile('background')) {
            $bg = $request->file('background');
            $bgName = $user->id . '-bg.' . $bg->extension();
            $path = $bg->storeAs('images/users', $bgName, 'public');
            $validatedData['background'] = '/storage/' . $path;
        }
        $request->user()->fill($validatedData);
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()->route('users.change_info')
            ->with('status', 'Cập nhật thông tin tài khoản thành công!');
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
        $bookmarks = $user->bookmarks()
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
        return view('client.users.notifications', ['user' => $user]);
    }

    public function collections(User $user): View
    {
        $isMine = \Illuminate\Support\Facades\Auth::id() === $user->id;
        $collections = \App\Models\Collection::where('user_id', $user->id)
            ->when(!$isMine, fn ($q) => $q->where('is_private', false))
            ->withCount('articles')->orderByDesc('updated_at')->get();
        return view('client.users.collections', compact('user', 'collections'));
    }

    public function teams(User $user): View
    {
        $teams = \App\Models\Team::where('user_id', $user->id)->orderByDesc('updated_at')->get();
        return view('client.users.teams', compact('user', 'teams'));
    }

    public function favourites(User $user): View
    {
        return view('client.users.favourites', ['user' => $user]);
    }

    public function achievements(User $user): View
    {
        return view('client.users.achievements', ['user' => $user]);
    }

    public function suggestions(User $user): View
    {
        return view('client.users.suggestions', ['user' => $user]);
    }

    public function banlist(User $user): View
    {
        return view('client.users.banlist', ['user' => $user]);
    }

    public function readingHistory(User $user): View
    {
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
}
