<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Công cụ FC Online
    |--------------------------------------------------------------------------
    |
    | Each module is one page under /cong-cu/{slug}. Data-driven tools (skills,
    | coach ranking, players, reset hours) read admin-entered data; calculators
    | use the parameters below. "requires_auth" = true makes the backend demand login.
    |
    */

    'modules' => [
        [
            'slug' => 'tinh-thue',
            'name' => 'Tính thuế cầu thủ',
            'summary' => 'Tính thuế 40% và thu nhập thực nhận sau VIP, SVIP và coupon.',
            'requires_auth' => false,
        ],
        [
            'slug' => 'dao-tao-ovr',
            'name' => 'Tính điểm đào tạo OVR',
            'summary' => 'Chọn vị trí, nhập chỉ số và mô phỏng đào tạo để xem OVR trước và sau.',
            'requires_auth' => false,
        ],
        [
            'slug' => 'ky-thuat',
            'name' => 'Tra cứu kỹ thuật',
            'summary' => 'Kỹ thuật theo cấp, phím bàn phím / tay cầm và ảnh động minh họa.',
            'requires_auth' => false,
        ],
        [
            'slug' => 'ranking',
            'name' => 'Ranking kỹ năng HLV',
            'summary' => 'Top kỹ năng HLV được dùng nhiều nhất.',
            'requires_auth' => false,
        ],
        [
            'slug' => 'du-lieu-cau-thu',
            'name' => 'Dữ liệu cầu thủ',
            'summary' => 'Tra cứu OVR, tốc độ, thể lực; lọc theo mùa thẻ, vị trí và so sánh 2 cầu thủ.',
            'requires_auth' => false,
        ],
        [
            'slug' => 'gio-reset',
            'name' => 'Giờ reset cầu thủ',
            'summary' => 'Khung giờ reset giá cầu thủ trên TTCN theo giờ chẵn / lẻ.',
            'requires_auth' => false,
        ],
    ],

    /*
    | Tax calculator: tax = price × rate; account perk and coupon each refund that % of the tax.
    */
    'tax' => [
        'rate' => 40,
        'perks' => [
            ['key' => 'svip', 'label' => 'SVIP', 'percent' => 30],
            ['key' => 'vip', 'label' => 'VIP', 'percent' => 10],
            ['key' => 'none', 'label' => 'Không có', 'percent' => 0],
        ],
        'coupons' => [40, 35, 30, 20, 10, 0],
    ],

    /*
    | OVR training: OVR = Σ(stat × weight) / Σ(weight) for the chosen position.
    | Weights follow the widely published FIFA position formula; adjust here if needed.
    */
    'ovr' => [
        'max_trained_stats' => 5,
        'max_per_stat' => 2,
        'stat_names' => [
            'finishing' => 'Dứt điểm', 'positioning' => 'Chọn vị trí', 'ball_control' => 'Giữ bóng',
            'shot_power' => 'Lực sút', 'heading' => 'Đánh đầu', 'reactions' => 'Phản ứng',
            'dribbling' => 'Rê bóng', 'strength' => 'Sức mạnh', 'sprint_speed' => 'Tốc độ',
            'short_passing' => 'Chuyền ngắn', 'acceleration' => 'Tăng tốc', 'long_shots' => 'Sút xa',
            'volleys' => 'Vô lê', 'vision' => 'Tầm nhìn', 'crossing' => 'Tạt bóng', 'agility' => 'Khéo léo',
            'long_passing' => 'Chuyền dài', 'stamina' => 'Thể lực', 'interceptions' => 'Cắt bóng',
            'marking' => 'Kèm người', 'standing_tackle' => 'Lấy bóng', 'sliding_tackle' => 'Xoạc bóng',
            'aggression' => 'Quyết đoán', 'jumping' => 'Nhảy', 'gk_diving' => 'TM đổ người',
            'gk_handling' => 'TM bắt bóng', 'gk_kicking' => 'TM phát bóng', 'gk_reflexes' => 'TM phản xạ',
            'gk_positioning' => 'TM chọn vị trí',
        ],
        'positions' => [
            'ST' => ['label' => 'LS / ST / RS', 'weights' => ['finishing' => 18, 'positioning' => 13, 'ball_control' => 10, 'shot_power' => 10, 'heading' => 10, 'reactions' => 8, 'dribbling' => 7, 'strength' => 5, 'sprint_speed' => 5, 'short_passing' => 5, 'acceleration' => 4, 'long_shots' => 3, 'volleys' => 2]],
            'CF' => ['label' => 'LF / CF / RF', 'weights' => ['ball_control' => 15, 'dribbling' => 14, 'positioning' => 13, 'finishing' => 11, 'reactions' => 9, 'short_passing' => 9, 'vision' => 8, 'shot_power' => 5, 'acceleration' => 5, 'sprint_speed' => 5, 'long_shots' => 4, 'heading' => 2]],
            'W' => ['label' => 'LW / RW', 'weights' => ['dribbling' => 16, 'ball_control' => 14, 'finishing' => 10, 'positioning' => 9, 'short_passing' => 9, 'crossing' => 9, 'reactions' => 7, 'acceleration' => 7, 'vision' => 6, 'sprint_speed' => 6, 'long_shots' => 4, 'agility' => 3]],
            'CAM' => ['label' => 'LAM / CAM / RAM', 'weights' => ['short_passing' => 16, 'ball_control' => 15, 'vision' => 14, 'dribbling' => 13, 'positioning' => 9, 'finishing' => 7, 'reactions' => 7, 'long_shots' => 5, 'acceleration' => 4, 'long_passing' => 4, 'agility' => 3, 'shot_power' => 3]],
            'M' => ['label' => 'LM / RM', 'weights' => ['dribbling' => 15, 'ball_control' => 13, 'short_passing' => 11, 'crossing' => 10, 'positioning' => 8, 'reactions' => 7, 'acceleration' => 7, 'vision' => 7, 'sprint_speed' => 6, 'finishing' => 6, 'long_passing' => 5, 'stamina' => 5]],
            'CM' => ['label' => 'LCM / CM / RCM', 'weights' => ['short_passing' => 17, 'ball_control' => 14, 'long_passing' => 13, 'vision' => 13, 'reactions' => 8, 'dribbling' => 7, 'positioning' => 6, 'stamina' => 6, 'interceptions' => 5, 'standing_tackle' => 5, 'long_shots' => 4]],
            'CDM' => ['label' => 'LDM / CDM / RDM', 'weights' => ['short_passing' => 14, 'interceptions' => 14, 'standing_tackle' => 12, 'long_passing' => 10, 'ball_control' => 10, 'marking' => 9, 'reactions' => 7, 'stamina' => 6, 'sliding_tackle' => 5, 'aggression' => 5, 'vision' => 4, 'strength' => 4]],
            'WB' => ['label' => 'LWB / RWB', 'weights' => ['interceptions' => 12, 'crossing' => 12, 'sliding_tackle' => 11, 'stamina' => 10, 'short_passing' => 10, 'reactions' => 8, 'ball_control' => 8, 'standing_tackle' => 8, 'marking' => 7, 'sprint_speed' => 6, 'acceleration' => 4, 'dribbling' => 4]],
            'FB' => ['label' => 'LB / RB', 'weights' => ['sliding_tackle' => 14, 'interceptions' => 12, 'standing_tackle' => 11, 'crossing' => 9, 'stamina' => 8, 'reactions' => 8, 'marking' => 8, 'sprint_speed' => 7, 'ball_control' => 7, 'short_passing' => 7, 'acceleration' => 5, 'heading' => 4]],
            'CB' => ['label' => 'LCB / CB / RCB', 'weights' => ['standing_tackle' => 17, 'marking' => 14, 'interceptions' => 13, 'strength' => 10, 'heading' => 10, 'sliding_tackle' => 10, 'aggression' => 7, 'reactions' => 5, 'short_passing' => 5, 'ball_control' => 4, 'jumping' => 3, 'sprint_speed' => 2]],
            'GK' => ['label' => 'GK', 'weights' => ['gk_diving' => 21, 'gk_handling' => 21, 'gk_reflexes' => 21, 'gk_positioning' => 21, 'reactions' => 11, 'gk_kicking' => 5]],
        ],
    ],

    /*
    | Positions used to filter player data.
    */
    'player_positions' => ['ST', 'LW', 'RW', 'CF', 'CAM', 'LM', 'RM', 'CM', 'CDM', 'LWB', 'RWB', 'LB', 'RB', 'CB', 'GK'],

];
