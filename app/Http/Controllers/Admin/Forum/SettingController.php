<?php

namespace App\Http\Controllers\Admin\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = $this->getAllSettings();
        return view('admin.forum.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'auto_approve_posts' => 'nullable|boolean',
            'allow_guest_view' => 'nullable|boolean',
            'posts_per_page' => 'nullable|integer|min:5|max:100',
            'comments_per_page' => 'nullable|integer|min:5|max:100',
        ]);

        foreach ($validated as $key => $value) {
            ForumSetting::set($key, $value ?? false);
        }

        return redirect()->route('admin.forum.settings.index')
            ->with('success', 'Forum settings updated successfully.');
    }

    // Helper methods
    private function getAllSettings(): array
    {
        $defaults = [
            'auto_approve_posts' => false,
            'allow_guest_view' => true,
            'posts_per_page' => 20,
            'comments_per_page' => 50,
        ];

        $saved = ForumSetting::all();

        foreach ($saved as $key => $value) {
            // Convert string booleans to actual booleans
            if ($value === '1' || $value === 'true') {
                $saved[$key] = true;
            } elseif ($value === '0' || $value === 'false') {
                $saved[$key] = false;
            }
        }

        return array_merge($defaults, $saved);
    }
}
