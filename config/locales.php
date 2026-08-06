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

    // Frontend can follow the selected locale or be locked to one locale.
    'user_multilingual' => env('USER_MULTILINGUAL', true),
    'user_locale' => env('USER_LOCALE', env('APP_LOCALE', 'en')),

    'supported' => [
        'en' => [
            'name'      => 'English',
            'flag'      => '🇬🇧',
            'flag_code' => 'gb', // ISO country cho flag-icon-css (emoji cờ không render trên Windows)
            'currency'  => 'USD',
            'locale'    => 'en_US',
        ],
        'vi' => [
            'name'      => 'Tiếng Việt',
            'flag'      => '🇻🇳',
            'flag_code' => 'vn',
            'currency'  => 'VND',
            'locale'    => 'vi_VN',
        ],
        'fr' => [
            'name'      => 'Français',
            'flag'      => '🇫🇷',
            'flag_code' => 'fr',
            'currency'  => 'EUR',
            'locale'    => 'fr_FR',
        ],
        'de' => [
            'name'      => 'Deutsch',
            'flag'      => '🇩🇪',
            'flag_code' => 'de',
            'currency'  => 'EUR',
            'locale'    => 'de_DE',
        ],
        // 'th' => ['name' => 'ไทย', 'flag' => '🇹🇭', 'currency' => 'THB', 'locale' => 'th_TH'],
        // 'id' => ['name' => 'Indonesia', 'flag' => '🇮🇩', 'currency' => 'IDR', 'locale' => 'id_ID'],
    ],
];
