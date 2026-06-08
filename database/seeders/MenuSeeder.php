<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Path tương đối (an toàn khi đổi domain). route(..., false) -> '/path'
        $home      = $this->path('home.index');
        $latest    = $this->path('home.show_new_update_articles');
        $completed = $this->path('home.show_completed_articles');

        $menus = [
            'header' => [
                'name'  => 'Header chính',
                'items' => [
                    ['label' => 'Home',      'label_key' => 'messages.nav.home',      'url' => $home,      'icon' => 'fa fa-home'],
                    ['label' => 'Browse',    'label_key' => 'messages.nav.browse',    'url' => '#browse',  'icon' => 'fa fa-layer-group'],
                    ['label' => 'Search',    'label_key' => 'messages.nav.search',    'url' => '#search',  'icon' => 'fa fa-search'],
                    ['label' => 'Latest',    'label_key' => 'messages.nav.new',       'url' => $latest,    'icon' => 'fa fa-bolt'],
                    ['label' => 'Completed', 'label_key' => 'messages.nav.completed', 'url' => $completed, 'icon' => 'fa fa-check-circle'],
                ],
            ],
            'mobile' => [
                'name'  => 'Menu mobile',
                'items' => [
                    ['label' => 'Home',      'label_key' => 'messages.nav.home',      'url' => $home,      'icon' => 'fa fa-home'],
                    ['label' => 'Browse',    'label_key' => 'messages.nav.browse',    'url' => '#browse',  'icon' => 'fa fa-layer-group'],
                    ['label' => 'Latest',    'label_key' => 'messages.nav.new',       'url' => $latest,    'icon' => 'fa fa-bolt'],
                    ['label' => 'Completed', 'label_key' => 'messages.nav.completed', 'url' => $completed, 'icon' => 'fa fa-check-circle'],
                ],
            ],
            'footer' => [
                'name'  => 'Footer',
                'items' => [
                    ['label' => 'Feedback',     'label_key' => 'messages.footer.feedback', 'url' => $this->path('pages.feedback')],
                    ['label' => 'Terms of Use', 'label_key' => 'messages.footer.terms',    'url' => $this->path('pages.terms')],
                    ['label' => 'DMCA',         'label_key' => 'messages.footer.dmca',      'url' => $this->path('pages.dmca')],
                    ['label' => 'Rules',        'label_key' => 'messages.footer.rules',     'url' => $this->path('pages.rules')],
                    ['label' => 'FAQ',          'label_key' => 'messages.footer.faq',       'url' => $this->path('pages.faq')],
                ],
            ],
            // browse: để trống -> tự động liệt kê tất cả thể loại (admin có thể tự thêm để override)
            'browse' => [
                'name'  => 'Dropdown Browse',
                'items' => [],
            ],
        ];

        foreach ($menus as $location => $cfg) {
            $menu = Menu::firstOrCreate(['location' => $location], ['name' => $cfg['name']]);
            if ($menu->items()->exists()) {
                continue; // không ghi đè nếu admin đã cấu hình
            }
            foreach ($cfg['items'] as $i => $item) {
                MenuItem::create(array_merge([
                    'menu_id'   => $menu->id,
                    'parent_id' => null,
                    'target'    => '_self',
                    'order'     => $i + 1,
                    'is_active' => true,
                ], $item));
            }
        }

        Menu::flushCache();
    }

    private function path(string $routeName): string
    {
        try {
            return route($routeName, [], false);
        } catch (\Throwable $e) {
            return '#';
        }
    }
}
