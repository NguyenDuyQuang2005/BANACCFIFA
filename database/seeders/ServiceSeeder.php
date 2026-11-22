<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seeds the Bơm BP service. Image and wording are edited in Admin → Dịch vụ FCO.
     */
    public function run(): void
    {
        Service::query()->firstOrCreate(
            ['code' => 'BOMBP'],
            [
                'name' => 'BƠM BP siêu tốc. Tặng 5% giá trị đơn hàng cho anh em giới thiệu',
                'description' => "- Bảo hành 1-1 với các trường hợp bị ban do bơm BP\n- Thời gian nhanh\n- Giá đẹp",
                'contact_label' => 'Liên hệ admin',
                'sort_order' => 0,
                'is_visible' => true,
            ],
        );
    }
}
