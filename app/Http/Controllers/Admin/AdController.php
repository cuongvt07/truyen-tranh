<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdItem;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $query = Ad::query()->with('items')->withCount('items');

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('mode')) {
            $query->where('display_mode', $request->mode);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sort = $request->get('sort', 'priority');
        match ($sort) {
            'name' => $query->orderBy('name'),
            'id_desc' => $query->orderByDesc('id'),
            default => $query->orderBy('priority')->orderBy('id'),
        };

        $ads = $query->paginate(30)->withQueryString();

        return view('admin.ads.index', [
            'ads' => $ads,
            'filters' => $request->only(['status', 'mode', 'search', 'sort']),
        ]);
    }

    public function create()
    {
        $ad = new Ad([
            'display_mode' => 'banner',
            'placement' => 'top',
            'pages' => ['all'],
            'frequency' => 'once_session',
            'frequency_value' => 1,
            'after_click' => 'none',
            'cooldown_seconds' => 0,
            'chapter_start' => 1,
            'chapter_interval' => 1,
            'chapter_inline_count' => 1,
            'chapter_inline_first_after' => 4,
            'chapter_inline_every' => 8,
            'hide_for_vip' => true,
            'is_active' => true,
        ]);

        $ad->setRelation('items', collect());

        return view('admin.ads.form', compact('ad'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data = $this->handleImage($request, $data);
        $ad = Ad::create($data);
        $this->syncItems($request, $ad);

        return redirect()->route('admin.ads.index')->with('success', 'Đã thêm quảng cáo.');
    }

    public function edit(Ad $ad)
    {
        $ad->load('items');

        return view('admin.ads.form', compact('ad'));
    }

    public function update(Request $request, Ad $ad)
    {
        $data = $this->validateData($request);
        $data = $this->handleImage($request, $data);
        $ad->update($data);
        $this->syncItems($request, $ad);

        return redirect()->route('admin.ads.index')->with('success', 'Đã cập nhật quảng cáo.');
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();

        return redirect()->route('admin.ads.index')->with('success', 'Đã xoá quảng cáo.');
    }

    public function toggle(Ad $ad)
    {
        $ad->update(['is_active' => ! $ad->is_active]);

        return redirect()->route('admin.ads.index')->with('success', 'Đã đổi trạng thái quảng cáo.');
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:160',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:32768',
            'link' => 'nullable|string|max:500',
            'display_mode' => 'required|in:' . implode(',', array_keys(Ad::MODES)),
            'placement' => 'nullable|in:' . implode(',', array_keys(Ad::PLACEMENTS)),
            'pages' => 'nullable|array',
            'pages.*' => 'in:' . implode(',', array_keys(Ad::PAGES)),
            'frequency' => 'required|in:' . implode(',', array_keys(Ad::FREQUENCIES)),
            'frequency_value' => 'nullable|integer|min:1|max:9999',
            'delay_seconds' => 'nullable|integer|min:0|max:600',
            'after_click' => 'required|in:' . implode(',', array_keys(Ad::AFTER_CLICKS)),
            'cooldown_seconds' => 'nullable|integer|min:0|max:86400',
            'chapter_start' => 'nullable|integer|min:1|max:9999',
            'chapter_interval' => 'nullable|integer|min:1|max:9999',
            'chapter_inline_count' => 'nullable|integer|min:1|max:20',
            'chapter_inline_first_after' => 'nullable|integer|min:1|max:200',
            'chapter_inline_every' => 'nullable|integer|min:1|max:200',
            'priority' => 'nullable|integer|min:0|max:9999',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|integer|exists:ad_items,id',
            'items.*.title' => 'nullable|string|max:160',
            'items.*.image_url' => 'nullable|string|max:500',
            'items.*.image_file' => 'nullable|image|max:32768',
            'items.*.link' => 'nullable|string|max:500',
            'items.*.sort_order' => 'nullable|integer|min:0|max:9999',
            'items.*.is_active' => 'nullable|boolean',
            'items.*.delete' => 'nullable|boolean',
        ]);

        return [
            'name' => $validated['name'],
            'image_url' => $validated['image_url'] ?? null,
            'link' => $validated['link'] ?? null,
            'display_mode' => $validated['display_mode'],
            'placement' => $validated['display_mode'] === 'banner' ? ($validated['placement'] ?? 'top') : null,
            'pages' => $validated['pages'] ?? ['all'],
            'frequency' => $validated['frequency'],
            'frequency_value' => $validated['frequency_value'] ?? 1,
            'delay_seconds' => $validated['delay_seconds'] ?? 0,
            'after_click' => $validated['after_click'],
            'cooldown_seconds' => $validated['cooldown_seconds'] ?? 0,
            'chapter_start' => 1,
            'chapter_interval' => 1,
            'chapter_inline_count' => $validated['chapter_inline_count'] ?? 1,
            'chapter_inline_first_after' => 4,
            'chapter_inline_every' => 8,
            'hide_for_vip' => $request->boolean('hide_for_vip'),
            'require_click' => $request->boolean('require_click'),
            'priority' => $validated['priority'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'start_at' => $validated['start_at'] ?? null,
            'end_at' => $validated['end_at'] ?? null,
        ];
    }

    private function handleImage(Request $request, array $data): array
    {
        if ($request->hasFile('image_file')) {
            $data['image_path'] = $request->file('image_file')->store('ads', 'public');
        } elseif ($request->input('image_file_remove') === '1') {
            $data['image_path'] = null;
        }

        return $data;
    }

    private function syncItems(Request $request, Ad $ad): void
    {
        $items = $request->input('items', []);
        $files = $request->file('items', []);

        foreach ($items as $index => $itemData) {
            $itemId = $itemData['id'] ?? null;
            $delete = filter_var($itemData['delete'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($delete && $itemId) {
                AdItem::where('ad_id', $ad->id)->where('id', $itemId)->delete();
                continue;
            }

            $title = trim((string) ($itemData['title'] ?? ''));
            $imageUrl = trim((string) ($itemData['image_url'] ?? ''));
            $link = trim((string) ($itemData['link'] ?? ''));
            $uploadedFile = $files[$index]['image_file'] ?? null;

            if ($title === '' && $imageUrl === '' && $link === '' && ! $uploadedFile && ! $itemId) {
                continue;
            }

            $payload = [
                'title' => $title !== '' ? $title : $ad->name,
                'image_url' => $imageUrl !== '' ? $imageUrl : null,
                'link' => $link !== '' ? $link : null,
                'sort_order' => (int) ($itemData['sort_order'] ?? $index),
                'is_active' => filter_var($itemData['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];

            if ($uploadedFile) {
                $payload['image_path'] = $uploadedFile->store('ads/items', 'public');
            } elseif (($itemData['image_remove'] ?? '0') === '1') {
                $payload['image_path'] = null;
            }

            $item = $itemId
                ? AdItem::where('ad_id', $ad->id)->where('id', $itemId)->first()
                : null;

            if ($item) {
                $item->update($payload);
            } else {
                $ad->items()->create($payload);
            }
        }

        Ad::flushCache();
    }
}
