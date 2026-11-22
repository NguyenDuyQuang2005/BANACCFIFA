<?php

return [

    'name' => env('APP_NAME', 'DINHTIENFCO'),

    'tagline' => 'Nền tảng tài khoản và công cụ FC Online',

    'description' => 'DINHTIENFCO là nền tảng mua bán tài khoản FC Online và bộ công cụ hỗ trợ người chơi. Danh mục, sản phẩm và giao dịch chỉ hiển thị khi có dữ liệu thật từ hệ thống.',

    'social' => [
        'zalo' => env('SITE_ZALO_URL'),
        'telegram' => env('SITE_TELEGRAM_URL'),
        'facebook' => env('SITE_FACEBOOK_URL'),
        'tiktok' => env('SITE_TIKTOK_URL'),
    ],

    'contact' => [
        'email' => env('SITE_CONTACT_EMAIL'),
        'phone' => env('SITE_CONTACT_PHONE'),
    ],

    'services' => [
        'notice' => env('SITE_SERVICES_NOTICE', 'Phiền ae nhắn tin vào Zalo nhé'),
    ],

    'seo' => [
        'og_image' => env('SITE_OG_IMAGE'),
        'google_verification' => env('SITE_GOOGLE_VERIFICATION'),
    ],

    /*
    | Sold account listings are hidden right away and removed this many days after the sale.
    | The buyer keeps the order and the game-account details (stored on the order itself).
    */
    'sold_product_retention_days' => (int) env('SOLD_PRODUCT_RETENTION_DAYS', 30),

    'admin' => [
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'manager' => [
        'email' => env('MANAGER_EMAIL'),
        'password' => env('MANAGER_PASSWORD'),
    ],

];
