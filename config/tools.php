<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Công cụ FC Online
    |--------------------------------------------------------------------------
    |
    | Đăng ký module công cụ tại đây để trang chủ và menu đọc động.
    | Công thức, dữ liệu cầu thủ và lịch reset chưa được xác nhận nguồn,
    | nên các module chỉ mở khung giao diện ở Giai đoạn 1.
    |
    */

    'modules' => [
        [
            'slug' => 'tinh-thue',
            'name' => 'Tính thuế cầu thủ',
            'summary' => 'Ước tính số tiền nhận được sau thuế khi bán cầu thủ.',
            'requires_auth' => false,
            'status' => 'coming_soon',
            'needs' => 'Công thức thuế chính thức từ nguồn hợp lệ.',
        ],
        [
            'slug' => 'dao-tao-ovr',
            'name' => 'Đào tạo OVR',
            'summary' => 'Tính toán nâng cấp OVR theo thông số đã xác nhận.',
            'requires_auth' => false,
            'status' => 'coming_soon',
            'needs' => 'Công thức đào tạo OVR được xác nhận.',
        ],
        [
            'slug' => 'ky-thuat',
            'name' => 'Tra cứu kỹ thuật',
            'summary' => 'Danh sách kỹ thuật, thao tác và phím điều khiển.',
            'requires_auth' => false,
            'status' => 'coming_soon',
            'needs' => 'Nội dung kỹ thuật và video từ nguồn hợp pháp.',
        ],
        [
            'slug' => 'ranking',
            'name' => 'Tra cứu Ranking',
            'summary' => 'Xem xếp hạng và tìm kiếm người chơi.',
            'requires_auth' => true,
            'status' => 'coming_soon',
            'needs' => 'API xếp hạng hợp pháp nếu có, hoặc dữ liệu nội bộ.',
        ],
        [
            'slug' => 'du-lieu-cau-thu',
            'name' => 'Dữ liệu cầu thủ',
            'summary' => 'Tìm kiếm cầu thủ theo tên, mùa và vị trí.',
            'requires_auth' => true,
            'status' => 'coming_soon',
            'needs' => 'Nguồn dữ liệu chỉ số cầu thủ được phép sử dụng.',
        ],
        [
            'slug' => 'gio-reset',
            'name' => 'Giờ reset game',
            'summary' => 'Hiển thị mốc reset và đếm ngược khi có lịch chính thức.',
            'requires_auth' => false,
            'status' => 'coming_soon',
            'needs' => 'Lịch reset chính thức của FC Online.',
        ],
    ],

];
