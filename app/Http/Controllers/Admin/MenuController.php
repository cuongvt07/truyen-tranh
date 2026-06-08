<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;

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

        return view('admin.menus.index', [
            'menus' => $menus,
            'menu'  => $menu,
            'tree'  => $tree,
        ]);
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
