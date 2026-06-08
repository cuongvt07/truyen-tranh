<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::orderBy('priority')->orderBy('id')->get();
        return view('admin.ads.index', compact('ads'));
    }

    public function create()
    {
        $ad = new Ad([
            'display_mode'     => 'banner',
            'placement'        => 'top',
            'pages'            => ['all'],
            'frequency'        => 'once_session',
            'frequency_value'  => 1,
            'after_click'      => 'none',
            'cooldown_seconds' => 0,
            'chapter_start'    => 2,
            'chapter_interval' => 1,
            'hide_for_vip'     => true,
            'is_active'        => true,
        ]);
        return view('admin.ads.form', compact('ad'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data = $this->handleImage($request, $data);
        Ad::create($data);

        return redirect()->route('admin.ads.index')->with('success', 'Đã thêm quảng cáo.');
    }

    public function edit(Ad $ad)
    {
        return view('admin.ads.form', compact('ad'));
    }

    public function update(Request $request, Ad $ad)
    {
        $data = $this->validateData($request);
        $data = $this->handleImage($request, $data);
        $ad->update($data);

        return redirect()->route('admin.ads.index')->with('success', 'Đã cập nhật quảng cáo.');
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();
        return redirect()->route('admin.ads.index')->with('success', 'Đã xoá quảng cáo.');
    }

    /** Bật/tắt nhanh từ danh sách */
    public function toggle(Ad $ad)
    {
        $ad->update(['is_active' => ! $ad->is_active]);
        return redirect()->route('admin.ads.index')->with('success', 'Đã đổi trạng thái quảng cáo.');
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:160',
            'image_url'        => 'nullable|string|max:500',
            'image_file'       => 'nullable|image|max:4096',
            'link'             => 'nullable|string|max:500',
            'display_mode'     => 'required|in:' . implode(',', array_keys(Ad::MODES)),
            'placement'        => 'nullable|in:' . implode(',', array_keys(Ad::PLACEMENTS)),
            'pages'            => 'nullable|array',
            'pages.*'          => 'in:' . implode(',', array_keys(Ad::PAGES)),
            'frequency'        => 'required|in:' . implode(',', array_keys(Ad::FREQUENCIES)),
            'frequency_value'  => 'nullable|integer|min:1|max:9999',
            'delay_seconds'    => 'nullable|integer|min:0|max:600',
            'after_click'      => 'required|in:' . implode(',', array_keys(Ad::AFTER_CLICKS)),
            'cooldown_seconds' => 'nullable|integer|min:0|max:86400',
            'chapter_start'    => 'nullable|integer|min:1|max:9999',
            'chapter_interval' => 'nullable|integer|min:1|max:9999',
            'priority'         => 'nullable|integer|min:0|max:9999',
            'start_at'         => 'nullable|date',
            'end_at'           => 'nullable|date|after_or_equal:start_at',
        ]);

        return [
            'name'             => $validated['name'],
            'image_url'        => $validated['image_url'] ?? null,
            'link'             => $validated['link'] ?? null,
            'display_mode'     => $validated['display_mode'],
            'placement'        => $validated['display_mode'] === 'banner' ? ($validated['placement'] ?? 'top') : null,
            'pages'            => $validated['pages'] ?? ['all'],
            'frequency'        => $validated['frequency'],
            'frequency_value'  => $validated['frequency_value'] ?? 1,
            'delay_seconds'    => $validated['delay_seconds'] ?? 0,
            'after_click'      => $validated['after_click'],
            'cooldown_seconds' => $validated['cooldown_seconds'] ?? 0,
            'chapter_start'    => $validated['chapter_start'] ?? 2,
            'chapter_interval' => $validated['chapter_interval'] ?? 1,
            'hide_for_vip'     => $request->boolean('hide_for_vip'),
            'require_click'    => $request->boolean('require_click'),
            'priority'         => $validated['priority'] ?? 0,
            'is_active'        => $request->boolean('is_active'),
            'start_at'         => $validated['start_at'] ?? null,
            'end_at'           => $validated['end_at'] ?? null,
        ];
    }

    private function handleImage(Request $request, array $data): array
    {
        if ($request->hasFile('image_file')) {
            $data['image_path'] = $request->file('image_file')->store('ads', 'public');
        }
        return $data;
    }
}
