<?php

namespace Database\Seeders;

use App\Models\Popup;
use Illuminate\Database\Seeder;

class PopupSeeder extends Seeder
{
    /**
     * Seeds the Zalo community popup. Edit the button link in Admin → Popup.
     */
    public function run(): void
    {
        Popup::query()->firstOrCreate(
            ['title' => 'Gia nhập cộng đồng Zalo'],
            [
                'content' => 'Cứ 50 thành viên tặng 5 voucher 50k trên web.',
                'media_type' => Popup::MEDIA_NONE,
                'button_label' => 'Vào nhóm Zalo',
                'button_url' => config('site.social.zalo'),
                'dismiss_minutes' => 60,
                'sort_order' => 0,
                'is_active' => true,
            ],
        );
    }
}
