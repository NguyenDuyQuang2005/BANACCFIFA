<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Tài khoản FC Online giá rẻ', 'description' => 'Tài khoản mức giá dễ tiếp cận.'],
            ['name' => 'Tài khoản nhiều BP', 'description' => 'Tài khoản có lượng BP lớn.'],
            ['name' => 'Tài khoản BP trắng', 'description' => 'Tài khoản BP trắng, chưa sử dụng nhiều.'],
            ['name' => 'Tài khoản có đội hình mạnh', 'description' => 'Đội hình đã xây sẵn, sẵn sàng thi đấu.'],
            ['name' => 'Tài khoản Team Color', 'description' => 'Tài khoản theo Team Color.'],
            ['name' => 'Tài khoản ICON', 'description' => 'Tài khoản có cầu thủ ICON.'],
            ['name' => 'Tài khoản nhiều cầu thủ giới hạn', 'description' => 'Nhiều thẻ cầu thủ giới hạn.'],
            ['name' => 'Tài khoản trắng thông tin', 'description' => 'Tài khoản trắng thông tin.'],
            ['name' => 'Tài khoản nhiều vật phẩm', 'description' => 'Nhiều vật phẩm trong game.'],
            ['name' => 'Tài khoản theo mức giá', 'description' => 'Phân nhóm theo khoảng giá.'],
        ];

        foreach ($categories as $index => $category) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'sort_order' => $index + 1,
                    'is_visible' => true,
                ],
            );
        }
    }
}
