<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\UserAchievement;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function index()
    {
        $achievements = Achievement::orderBy('sort_order')
            ->withCount('users')
            ->get();

        return view('admin.achievements.index', compact('achievements'));
    }

    public function update(Request $request, Achievement $achievement)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'name_en'         => 'nullable|string|max:100',
            'description'     => 'nullable|string|max:500',
            'description_en'  => 'nullable|string|max:500',
            'target'          => 'required|integer|min:1',
            'reward_credits'  => 'required|integer|min:0',
            'sort_order'      => 'required|integer|min:0',
        ]);

        $achievement->update($data);

        return back()->with('success', 'Đã cập nhật thành tích.');
    }

    public function destroy(Achievement $achievement)
    {
        $achievement->delete();
        return back()->with('success', 'Đã xoá thành tích.');
    }

    public function users(Achievement $achievement)
    {
        $users = UserAchievement::where('achievement_id', $achievement->id)
            ->with('user:id,name,username,email')
            ->orderByDesc('unlocked_at')
            ->paginate(30);

        return view('admin.achievements.users', compact('achievement', 'users'));
    }
}
