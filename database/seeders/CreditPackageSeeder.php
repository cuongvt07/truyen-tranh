<?php

namespace Database\Seeders;

use App\Models\CreditPackage;
use Illuminate\Database\Seeder;

class CreditPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['name' => 'Gói Cơ bản',      'coins' => 500,   'price_vnd' => 50000,   'price_usd' => 2.00,  'price_display' => '50.000đ',      'icon' => 'media/payments/500_xKtb4nU.jpg', 'is_featured' => false, 'sort_order' => 1],
            ['name' => 'Gói Mọt sách',    'coins' => 1000,  'price_vnd' => 100000,  'price_usd' => 4.00,  'price_display' => '100.000đ',     'icon' => 'media/payments/1000.jpg',         'is_featured' => false, 'sort_order' => 2],
            ['name' => 'Gói Tường thuật', 'coins' => 2100,  'price_vnd' => 200000,  'price_usd' => 8.00,  'price_display' => '200.000đ',     'icon' => 'media/payments/2100.jpg',         'is_featured' => true,  'sort_order' => 3],
            ['name' => 'Gói Phê bình',    'coins' => 4450,  'price_vnd' => 400000,  'price_usd' => 16.00, 'price_display' => '400.000đ',     'icon' => 'media/payments/4450.jpg',         'is_featured' => false, 'sort_order' => 4],
            ['name' => 'Gói Tuyệt vời',   'coins' => 8500,  'price_vnd' => 700000,  'price_usd' => 28.00, 'price_display' => '700.000đ',     'icon' => 'media/payments/8500.jpg',         'is_featured' => false, 'sort_order' => 5],
            ['name' => 'Gói Hoàng gia',   'coins' => 12500, 'price_vnd' => 1000000, 'price_usd' => 40.00, 'price_display' => '1.000.000đ',   'icon' => 'media/payments/12500.jpg',        'is_featured' => false, 'sort_order' => 6],
        ];

        foreach ($packages as $pkg) {
            CreditPackage::updateOrCreate(
                ['name' => $pkg['name']],
                array_merge($pkg, ['is_active' => true])
            );
        }
    }
}
