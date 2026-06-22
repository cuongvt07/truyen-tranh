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

        return redirect()->route('admin.ads.index')->with('success', __('messages.flash.ad.created'));
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

        return redirect()->route('admin.ads.index')->with('success', __('messages.flash.ad.updated'));
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();

        return redirect()->route('admin.ads.index')->with('success', __('messages.flash.ad.deleted'));
    }

    public function toggle(Ad $ad)
    {
        $ad->update(['is_active' => ! $ad->is_active]);

        return redirect()->route('admin.ads.index')->with('success', __('messages.flash.ad.status_changed'));
    }

    private function validateData(Request $request): array
    {
        $mode = $request->input('display_mode');

        $rules = [
            'name'         => 'required|string|max:160',
            'display_mode' => 'required|in:' . implode(',', array_keys(Ad::MODES)),
            'pages'        => 'nullable|array',
            'pages.*'      => 'in:' . implode(',', array_keys(Ad::PAGES)),
            'priority'     => 'nullable|integer|min:0|max:9999',
            'start_at'     => 'nullable|date',
            'end_at'       => 'nullable|date|after_or_equal:start_at',
        ];

        // Fields tuỳ theo loại
        $rules['script_code'] = 'nullable|string';

        if (in_array($mode, ['banner', 'popup'])) {
            $rules['image_url']  = 'nullable|string|max:500';
            $rules['image_file'] = 'nullable|image|max:32768';
            $rules['link']       = 'nullable|string|max:500';
        }
        if ($mode === 'banner') {
            $rules['placement'] = 'nullable|in:' . implode(',', array_keys(Ad::PLACEMENTS));
        }
        if ($mode === 'click_anywhere') {
            $rules['link'] = 'nullable|required_without:script_code|string|max:500';
        }
        if (in_array($mode, ['popup', 'click_anywhere'])) {
            $rules['frequency']       = 'required|in:' . implode(',', array_keys(Ad::FREQUENCIES));
            $rules['frequency_value'] = 'nullable|integer|min:1|max:9999';
            $rules['after_click']     = 'required|in:' . implode(',', array_keys(Ad::AFTER_CLICKS));
            $rules['cooldown_seconds']= 'nullable|integer|min:0|max:86400';
        }
        if ($mode === 'popup') {
            $rules['delay_seconds'] = 'nullable|integer|min:0|max:600';
        }
        if ($mode === 'chapter') {
            $rules['chapter_inline_count'] = 'nullable|integer|min:1|max:20';
            $rules['items']            = 'nullable|array';
            $rules['items.*.id']       = 'nullable|integer|exists:ad_items,id';
            $rules['items.*.title']    = 'nullable|string|max:160';
            $rules['items.*.image_url']= 'nullable|string|max:500';
            $rules['items.*.script_code']= 'nullable|string';
            $rules['items.*.image_file']= 'nullable|image|max:32768';
            $rules['items.*.link']     = 'nullable|string|max:500';
            $rules['items.*.sort_order']= 'nullable|integer|min:0|max:9999';
            $rules['items.*.is_active']= 'nullable|boolean';
            $rules['items.*.delete']   = 'nullable|boolean';
        }

        $v = $request->validate($rules);
        $scriptCode = trim((string) ($v['script_code'] ?? ''));

        // Build payload theo từng loại
        $data = [
            'name'         => $v['name'],
            'display_mode' => $mode,
            'pages'        => $v['pages'] ?? ['all'],
            'priority'     => $v['priority'] ?? 0,
            'is_active'    => $request->boolean('is_active'),
            'hide_for_vip' => $request->boolean('hide_for_vip'),
            'start_at'     => $v['start_at'] ?? null,
            'end_at'       => $v['end_at'] ?? null,
            // Nullify fields irrelevant to this mode
            'placement'          => null,
            'image_url'          => null,
            'script_code'        => null,
            'link'               => null,
            'frequency'          => null,
            'frequency_value'    => null,
            'delay_seconds'      => null,
            'after_click'        => null,
            'cooldown_seconds'   => null,
            'require_click'      => false,
            'chapter_inline_count' => null,
        ];

        match ($mode) {
            'banner' => array_merge($data, [
                'image_url'  => $v['image_url'] ?? null,
                'script_code'=> $scriptCode !== '' ? $scriptCode : null,
                'link'       => $v['link'] ?? null,
                'placement'  => $v['placement'] ?? 'top',
            ]),
            'click_anywhere' => array_merge($data, [
                'script_code'      => $scriptCode !== '' ? $scriptCode : null,
                'link'             => $v['link'] ?? null,
                'frequency'        => $v['frequency'] ?? 'once_session',
                'frequency_value'  => $v['frequency_value'] ?? 1,
                'after_click'      => $v['after_click'] ?? 'stop_session',
                'cooldown_seconds' => $v['cooldown_seconds'] ?? 0,
            ]),
            'popup' => array_merge($data, [
                'image_url'        => $v['image_url'] ?? null,
                'script_code'      => $scriptCode !== '' ? $scriptCode : null,
                'link'             => $v['link'] ?? null,
                'delay_seconds'    => $v['delay_seconds'] ?? 5,
                'frequency'        => $v['frequency'] ?? 'once_session',
                'frequency_value'  => $v['frequency_value'] ?? 1,
                'after_click'      => $v['after_click'] ?? 'none',
                'cooldown_seconds' => $v['cooldown_seconds'] ?? 0,
            ]),
            'chapter' => array_merge($data, [
                'script_code'        => $scriptCode !== '' ? $scriptCode : null,
                'require_click'      => $request->boolean('require_click'),
                'chapter_inline_count' => $v['chapter_inline_count'] ?? 1,
            ]),
            default => [],
        };

        // match() returns value; assign properly per mode
        if ($mode === 'banner') {
            $data['image_url'] = $v['image_url'] ?? null;
            $data['script_code'] = $scriptCode !== '' ? $scriptCode : null;
            $data['link']      = $v['link'] ?? null;
            $data['placement'] = $v['placement'] ?? 'top';
        } elseif ($mode === 'click_anywhere') {
            $data['script_code']      = $scriptCode !== '' ? $scriptCode : null;
            $data['link']             = $v['link'] ?? null;
            $data['frequency']        = $v['frequency'] ?? 'once_session';
            $data['frequency_value']  = $v['frequency_value'] ?? 1;
            $data['after_click']      = $v['after_click'] ?? 'stop_session';
            $data['cooldown_seconds'] = $v['cooldown_seconds'] ?? 0;
        } elseif ($mode === 'popup') {
            $data['image_url']        = $v['image_url'] ?? null;
            $data['script_code']      = $scriptCode !== '' ? $scriptCode : null;
            $data['link']             = $v['link'] ?? null;
            $data['delay_seconds']    = $v['delay_seconds'] ?? 5;
            $data['frequency']        = $v['frequency'] ?? 'once_session';
            $data['frequency_value']  = $v['frequency_value'] ?? 1;
            $data['after_click']      = $v['after_click'] ?? 'none';
            $data['cooldown_seconds'] = $v['cooldown_seconds'] ?? 0;
        } elseif ($mode === 'chapter') {
            $data['script_code']          = $scriptCode !== '' ? $scriptCode : null;
            $data['require_click']        = $request->boolean('require_click');
            $data['chapter_inline_count'] = $v['chapter_inline_count'] ?? 1;
        } elseif ($mode === 'footer') {
            // Footer script: chèn nguyên văn trước </body> (ad network, consent, analytics...).
            $data['script_code'] = $scriptCode !== '' ? $scriptCode : null;
        }

        return $data;
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
            $scriptCode = trim((string) ($itemData['script_code'] ?? ''));
            $link = trim((string) ($itemData['link'] ?? ''));
            $uploadedFile = $files[$index]['image_file'] ?? null;

            if ($title === '' && $imageUrl === '' && $scriptCode === '' && $link === '' && ! $uploadedFile && ! $itemId) {
                continue;
            }

            $payload = [
                'title' => $title !== '' ? $title : $ad->name,
                'image_url' => $imageUrl !== '' ? $imageUrl : null,
                'script_code' => $scriptCode !== '' ? $scriptCode : null,
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
