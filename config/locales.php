<?php

/*
|--------------------------------------------------------------------------
| Đa ngôn ngữ / Đa thị trường (Multi-market i18n)
|--------------------------------------------------------------------------
| Toàn bộ cấu hình ngôn ngữ tập trung tại đây.
| Thêm một thị trường mới = thêm 1 dòng vào 'supported' + tạo thư mục
| resources/lang/{code}/ tương ứng.
|
| Mỗi locale có thể kèm tiền tệ + định dạng -> tiện mở rộng cho nhiều thị trường.
*/

return [

    'default' => env('APP_LOCALE', 'en'),

    'switchable' => env('LOCALE_SWITCHABLE', true),

    'supported' => [
        'en' => [
            'name'     => 'English',
            'flag'     => '🇬🇧',
            'currency' => 'USD',
            'locale'   => 'en_US',
        ],
        'vi' => [
            'name'     => 'Tiếng Việt',
            'flag'     => '🇻🇳',
            'currency' => 'VND',
            'locale'   => 'vi_VN',
        ],
        // 'th' => ['name' => 'ไทย', 'flag' => '🇹🇭', 'currency' => 'THB', 'locale' => 'th_TH'],
        // 'id' => ['name' => 'Indonesia', 'flag' => '🇮🇩', 'currency' => 'IDR', 'locale' => 'id_ID'],
    ],
];
