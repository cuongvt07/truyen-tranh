<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\HomeBlock;
use Illuminate\Database\Seeder;

class HomeBlockSeeder extends Seeder
{
    /**
     * Chuyển đúng bộ khối vốn hard-code trong HomeController sang DB, để sau khi
     * migrate trang chủ giữ nguyên diện mạo cũ rồi admin tự sửa tiếp.
     * Chạy được nhiều lần: đã có khối thì bỏ qua.
     */
    public function run(): void
    {
        if (HomeBlock::exists()) {
            $this->command?->info('home_blocks đã có dữ liệu — bỏ qua seeder.');

            return;
        }

        $fixed = [
            ['title' => 'Top Trending',                        'source' => 'trending',  'variant' => 'trending'],
            ['title' => 'Hottest New',                         'source' => 'latest',    'variant' => 'rail'],
            ['title' => "Editors' Choice",                     'source' => 'hot',       'variant' => 'rail'],
            ['title' => 'Only at '.config('app.name'),         'source' => 'exclusive', 'variant' => 'rail'],
        ];

        $order = 0;
        foreach ($fixed as $row) {
            HomeBlock::create($row + [
                'limit'        => 9,
                'show_see_all' => true,
                'is_active'    => true,
                'order'        => $order += 10,
            ]);
        }

        // Bộ khối thể loại cũ khớp theo TÊN; giữ nguyên danh sách tên thay thế để
        // tìm đúng genre trên từng site (dữ liệu mỗi site đặt tên khác nhau).
        $genreBlocks = [
            'Top Werewolf'         => ['Werewolf'],
            'Top Billionaire/CEO'  => ['Billionaire/CEO', 'Billionaire', 'CEO'],
            'Top Romance'          => ['Romance', 'Tinh cam', 'Tình cảm'],
            'Top Paranormal'       => ['Paranormal', 'Supernatural'],
            'Top Fantasy'          => ['Fantasy', 'Tham hiem', 'Thám hiểm', 'Xuyen khong', 'Xuyên không'],
            'Top YA/Teen'          => ['YA/Teen', 'Young Adult', 'School Life'],
            'Top LGBTQ+'           => ['LGBTQ+', 'LGBTQ'],
        ];

        foreach ($genreBlocks as $title => $names) {
            $genre = Genre::whereIn('name', $names)->orderBy('name')->first();

            if (!$genre) {
                $this->command?->warn("Bỏ qua \"{$title}\": không tìm thấy thể loại ".implode(' / ', $names));
                continue;
            }

            HomeBlock::create([
                'title'        => $title,
                'source'       => 'genre',
                'genre_id'     => $genre->id,
                'limit'        => 9,
                'variant'      => 'rail',
                'show_see_all' => true,
                'is_active'    => true,
                'order'        => $order += 10,
            ]);
        }

        $this->command?->info('Đã tạo '.HomeBlock::count().' khối trang chủ.');
    }
}
