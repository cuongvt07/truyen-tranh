<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Controller;
use App\Models\FaqSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = $this->getAllSettings();
        return view('admin.faq.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'allow_guest_view' => 'nullable|boolean',
            'enable_comments' => 'nullable|boolean',
            'articles_per_page' => 'nullable|integer|min:5|max:100',
            'comments_per_page' => 'nullable|integer|min:5|max:100',
        ]);

        foreach ($validated as $key => $value) {
            FaqSetting::set($key, $value ?? false);
        }

        return redirect()->route('admin.faq.settings.index')
            ->with('success', 'FAQ settings updated successfully.');
    }

    // Helper methods
    private function getAllSettings(): array
    {
        $defaults = [
            'allow_guest_view' => true,
            'enable_comments' => true,
            'articles_per_page' => 20,
            'comments_per_page' => 50,
        ];

        $saved = FaqSetting::getAllSettings();

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
