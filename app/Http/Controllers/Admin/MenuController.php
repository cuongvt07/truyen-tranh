<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class MenuController extends Controller
{
    /** 1 trang duy nhất: chọn vị trí (tab) + builder kéo-thả lồng nhau */
    public function index(Request $request)
    {
        $this->ensureLocations();
        $menus = Menu::orderBy('id')->get();

        // vị trí đang chọn (mặc định header)
        $location = $request->get('menu', 'header');
        $menu = $menus->firstWhere('location', $location) ?: $menus->first();

        // cây mục: gốc + con (1 cấp) để render nestable
        $tree = $menu
            ? $menu->items()->whereNull('parent_id')->with(['children' => fn ($q) => $q->orderBy('order')])->orderBy('order')->get()
            : collect();

        // Danh sách nguồn dữ liệu để người dùng chọn (giống WordPress)
        $availableSources = $this->getAvailableSources();

        return view('admin.menus.index', [
            'menus' => $menus,
            'menu'  => $menu,
            'tree'  => $tree,
            'availableSources' => $availableSources,
        ]);
    }

    /** Lấy các nguồn dữ liệu có sẵn để tạo menu */
    private function getAvailableSources(): array
    {
        $locale = app()->getLocale();
        
        // Genres sử dụng polymorphic slug
        $genres = \App\Models\Genre::with('slug')
            ->orderBy('name')
            ->get()
            ->map(function ($genre) {
                return (object)[
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'slug' => $genre->slug?->slug ?? $genre->id,
                ];
            });
        
        // Static pages - chỉ lấy những trang có route cố định
        $staticPages = collect([
            (object)['id' => 'rules', 'title' => 'Nội quy', 'slug' => 'rules', 'type' => 'rules', 'route' => 'pages.rules'],
            (object)['id' => 'terms', 'title' => 'Điều khoản', 'slug' => 'terms', 'type' => 'terms', 'route' => 'pages.terms'],
            (object)['id' => 'dmca', 'title' => 'DMCA', 'slug' => 'dmca', 'type' => 'dmca', 'route' => 'pages.dmca'],
            (object)['id' => 'pricing', 'title' => 'Bảng giá', 'slug' => 'pricing', 'type' => 'pricing', 'route' => 'pages.pricing'],
            (object)['id' => 'feedback', 'title' => 'Góp ý', 'slug' => 'feedback', 'type' => 'feedback', 'route' => 'pages.feedback'],
            (object)['id' => 'faq', 'title' => 'FAQ', 'slug' => 'faq', 'type' => 'faq', 'route' => 'pages.faq'],
            (object)['id' => 'forum', 'title' => 'Forum', 'slug' => 'forum', 'type' => 'forum', 'route' => 'pages.forum'],
        ]);
        
        return [
            'genres' => $genres,
            'static_pages' => $staticPages,
            'forum_categories' => \App\Models\ForumCategory::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($cat) use ($locale) {
                    return (object)[
                        'id' => $cat->id,
                        'title' => $cat->{"title_$locale"} ?? $cat->title_en,
                        'slug' => $cat->slug,
                    ];
                }),
            'faq_categories' => \App\Models\FaqCategory::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($cat) use ($locale) {
                    return (object)[
                        'id' => $cat->id,
                        'title' => $cat->{"title_$locale"} ?? $cat->title_en,
                        'slug' => $cat->slug,
                    ];
                }),
        ];
    }

    /** Thêm mục mới (vào cuối menu) */
    public function storeItem(Request $request, Menu $menu)
    {
        $data = $this->validateItem($request);
        $data['menu_id']   = $menu->id;
        $data['parent_id'] = null;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['order']     = (int) $menu->items()->max('order') + 1;
        MenuItem::create($data);

        Menu::flushCache($menu->location);
        return $this->back($menu->location, 'Đã thêm mục menu.');
    }

    /** Thêm nhiều mục từ nguồn có sẵn (genres, pages, etc.) */
    public function addFromSource(Request $request, Menu $menu)
    {
        $request->validate([
            'source_type' => 'required|in:genre,static_page,forum_category,faq_category,custom',
            'items' => 'required|array',
            'items.*.id' => 'required_unless:source_type,custom',
            'items.*.label' => 'required_if:source_type,custom',
            'items.*.url' => 'required_if:source_type,custom',
        ]);

        $sourceType = $request->input('source_type');
        $items = $request->input('items', []);
        $maxOrder = (int) $menu->items()->max('order');

        foreach ($items as $index => $item) {
            $menuItemData = $this->buildMenuItemFromSource($sourceType, $item);
            $menuItemData['menu_id'] = $menu->id;
            $menuItemData['parent_id'] = null;
            $menuItemData['is_active'] = true;
            $menuItemData['order'] = $maxOrder + $index + 1;
            
            MenuItem::create($menuItemData);
        }

        Menu::flushCache($menu->location);
        return $this->back($menu->location, 'Đã thêm ' . count($items) . ' mục menu.');
    }

    /** Tạo dữ liệu menu item từ nguồn */
    private function buildMenuItemFromSource(string $sourceType, array $item): array
    {
        switch ($sourceType) {
            case 'genre':
                $genre = \App\Models\Genre::with('slug')->find($item['id']);
                if (!$genre) {
                    throw new \Exception("Genre not found");
                }
                $slug = $genre->slug?->slug ?? $genre->id;
                return [
                    'label' => $genre->name,
                    'url' => '/genres/' . $slug, // Relative path
                    'icon' => '',
                    'target' => '_self',
                ];
            
            case 'static_page':
                // Static pages với route cố định
                $routeName = $item['route'] ?? null;
                if (!$routeName || !Route::has($routeName)) {
                    throw new \Exception("Static page route not found");
                }
                // Lấy path từ route
                $path = '/' . ltrim(Route::getRoutes()->getByName($routeName)->uri(), '/');
                return [
                    'label' => $item['label'] ?? $item['title'] ?? 'Page',
                    'url' => $path, // Relative path
                    'icon' => '',
                    'target' => '_self',
                ];
            
            case 'forum_category':
                $category = \App\Models\ForumCategory::find($item['id']);
                if (!$category) {
                    throw new \Exception("Forum category not found");
                }
                $locale = app()->getLocale();
                return [
                    'label' => $category->{"title_$locale"} ?? $category->title_en,
                    'url' => '/forum/' . $category->slug, // Relative path
                    'icon' => '',
                    'target' => '_self',
                ];
            
            case 'faq_category':
                $category = \App\Models\FaqCategory::find($item['id']);
                if (!$category) {
                    throw new \Exception("FAQ category not found");
                }
                $locale = app()->getLocale();
                return [
                    'label' => $category->{"title_$locale"} ?? $category->title_en,
                    'url' => '/faq/' . $category->slug, // Relative path
                    'icon' => '',
                    'target' => '_self',
                ];
            
            case 'custom':
            default:
                return [
                    'label' => $item['label'] ?? 'Link',
                    'url' => $item['url'] ?? '#',
                    'icon' => $item['icon'] ?? '',
                    'target' => $item['target'] ?? '_self',
                ];
        }
    }

    /** Cập nhật 1 mục (title/url/icon/khóa dịch/target/active) */
    public function updateItem(Request $request, MenuItem $item)
    {
        $data = $this->validateItem($request);
        $data['is_active'] = $request->boolean('is_active', false);
        $item->update($data);

        $location = optional($item->menu)->location;
        Menu::flushCache($location);
        return $this->back($location, 'Đã cập nhật mục menu.');
    }

    /** Xoá 1 mục (cascade xoá mục con) */
    public function destroyItem(MenuItem $item)
    {
        $location = optional($item->menu)->location;
        $item->delete();

        Menu::flushCache($location);
        return $this->back($location, 'Đã xoá mục menu.');
    }

    /** Lưu thứ tự + lồng cha-con từ Nestable (cây JSON) */
    public function reorder(Request $request, Menu $menu)
    {
        $tree = $request->input('items', []);
        if (is_string($tree)) {
            $tree = json_decode($tree, true) ?: [];
        }
        $this->applyTree($menu, is_array($tree) ? $tree : [], null, 0);

        Menu::flushCache($menu->location);
        return response()->json(['ok' => true]);
    }

    /** Đệ quy gán parent_id + order; chỉ cho phép lồng 1 cấp (cha → con dropdown) */
    private function applyTree(Menu $menu, array $nodes, $parentId, int $depth): void
    {
        foreach (array_values($nodes) as $i => $node) {
            $id = (int) ($node['id'] ?? 0);
            if ($id) {
                MenuItem::where('id', $id)->where('menu_id', $menu->id)
                    ->update(['parent_id' => $parentId, 'order' => $i]);
            }
            $children = $node['children'] ?? [];
            if (is_array($children) && count($children)) {
                // sâu hơn 1 cấp -> con vẫn gắn vào item gốc gần nhất
                $nextParent = $depth < 1 ? ($id ?: $parentId) : $parentId;
                $this->applyTree($menu, $children, $nextParent, $depth + 1);
            }
        }
    }

    private function validateItem(Request $request): array
    {
        $data = $request->validate([
            'label'     => 'required|string|max:120',
            'label_key' => 'nullable|string|max:160',
            'url'       => 'nullable|string|max:255',
            'icon'      => 'nullable|string|max:80',
            'target'    => 'nullable|in:_self,_blank',
        ]);
        $data['url']    = $data['url'] ?: '#';
        $data['target'] = $data['target'] ?? '_self';
        return $data;
    }

    private function back(?string $location, string $msg)
    {
        return redirect()
            ->route('admin.menus.index', ['menu' => $location])
            ->with('success', $msg);
    }

    /** Đảm bảo 4 vị trí menu luôn tồn tại */
    private function ensureLocations(): void
    {
        $defaults = [
            'header' => 'Header chính',
            'browse' => 'Dropdown Browse',
            'footer' => 'Footer',
            'mobile' => 'Menu mobile',
        ];
        foreach ($defaults as $loc => $name) {
            Menu::firstOrCreate(['location' => $loc], ['name' => $name]);
        }
    }
}
