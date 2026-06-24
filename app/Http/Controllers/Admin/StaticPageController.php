<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaticPageController extends Controller
{
    public function index(Request $request)
    {
        $query = StaticPage::query()->with('parent:id,title_en,title_vi,slug');

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('page_type', $type);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Search
        if ($q = trim((string) $request->get('search'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('slug', 'like', "%{$q}%")
                    ->orWhere('title_en', 'like', "%{$q}%")
                    ->orWhere('title_vi', 'like', "%{$q}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'default');
        switch ($sort) {
            case 'title':
                $query->orderBy('title_en');
                break;
            case 'views':
                $query->orderByDesc('view_count');
                break;
            case 'id_desc':
                $query->orderByDesc('id');
                break;
            case 'id_asc':
                $query->orderBy('id');
                break;
            case 'default':
            default:
                $query->orderBy('page_type')
                    ->orderBy('sort_order')
                    ->orderBy('id');
        }

        $pages = $query->paginate(30)->withQueryString();

        return view('admin.static-pages.index', [
            'pages' => $pages,
            'filters' => $request->only(['type', 'status', 'search', 'sort'])
        ]);
    }

    public function create()
    {
        $page = new StaticPage([
            'page_type' => 'custom',
            'is_active' => true,
            'comments_enabled' => false,
            'is_pinned' => false,
            'sort_order' => 0,
        ]);

        return view('admin.static-pages.form', [
            'page' => $page,
            'parents' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request)
    {
        StaticPage::create($this->validatedData($request));

        return redirect()->route('admin.static-pages.index')->with('success', __('messages.flash.static_page.created'));
    }

    public function edit(StaticPage $staticPage)
    {
        return view('admin.static-pages.form', [
            'page' => $staticPage,
            'parents' => $this->parentOptions($staticPage->id),
        ]);
    }

    public function update(Request $request, StaticPage $staticPage)
    {
        $staticPage->update($this->validatedData($request, $staticPage->id));

        return redirect()->route('admin.static-pages.index')->with('success', __('messages.flash.static_page.updated'));
    }

    public function destroy(StaticPage $staticPage)
    {
        $staticPage->delete();

        return redirect()->route('admin.static-pages.index')->with('success', __('messages.flash.static_page.deleted'));
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $unique = 'unique:static_pages,slug';
        if ($ignoreId) {
            $unique .= ',' . $ignoreId;
        }

        $validated = $request->validate([
            'parent_id'         => ['nullable', 'integer', 'exists:static_pages,id'],
            'page_type'         => ['required', 'in:' . implode(',', array_keys(StaticPage::TYPES))],
            'slug'              => ['nullable', 'string', 'max:180', $unique],
            'title_en'          => ['required', 'string', 'max:255'],
            'title_vi'          => ['nullable', 'string', 'max:255'],
            'title_fr'          => ['nullable', 'string', 'max:255'],
            'excerpt_en'        => ['nullable', 'string'],
            'excerpt_vi'        => ['nullable', 'string'],
            'excerpt_fr'        => ['nullable', 'string'],
            'content_en'        => ['nullable', 'string'],
            'content_vi'        => ['nullable', 'string'],
            'content_fr'        => ['nullable', 'string'],
            'section_label_en'  => ['nullable', 'string', 'max:120'],
            'section_label_vi'  => ['nullable', 'string', 'max:120'],
            'section_label_fr'  => ['nullable', 'string', 'max:120'],
            'sort_order'        => ['nullable', 'integer', 'min:0', 'max:999999'],
            'status'            => ['nullable', 'in:pending,approved,rejected'],
        ]);

        $slug = $validated['slug'] ?: Str::slug($validated['title_en']);
        if ($slug === '') {
            $slug = Str::random(8);
        }

        return [
            'parent_id'        => $validated['parent_id'] ?? null,
            'page_type'        => $validated['page_type'],
            'slug'             => $slug,
            'title_en'         => $validated['title_en'],
            'title_vi'         => $validated['title_vi'] ?? null,
            'title_fr'         => $validated['title_fr'] ?? null,
            'excerpt_en'       => $validated['excerpt_en'] ?? null,
            'excerpt_vi'       => $validated['excerpt_vi'] ?? null,
            'excerpt_fr'       => $validated['excerpt_fr'] ?? null,
            'content_en'       => $validated['content_en'] ?? null,
            'content_vi'       => $validated['content_vi'] ?? null,
            'content_fr'       => $validated['content_fr'] ?? null,
            'section_label_en' => $validated['section_label_en'] ?? null,
            'section_label_vi' => $validated['section_label_vi'] ?? null,
            'section_label_fr' => $validated['section_label_fr'] ?? null,
            'comments_enabled' => $request->boolean('comments_enabled'),
            'is_pinned'        => $request->boolean('is_pinned'),
            'is_active'        => $request->boolean('is_active'),
            'sort_order'       => $validated['sort_order'] ?? 0,
            'status'           => $validated['status'] ?? StaticPage::STATUS_APPROVED,
        ];
    }

    private function parentOptions(?int $excludeId = null)
    {
        return StaticPage::query()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('page_type')
            ->orderBy('sort_order')
            ->orderBy('title_en')
            ->get(['id', 'page_type', 'title_en', 'title_vi', 'slug']);
    }
}
