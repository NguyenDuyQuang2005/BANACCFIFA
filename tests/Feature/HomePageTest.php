<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_renders_brand_and_core_sections(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('DINHTIENFCO', false);
        $response->assertSee('Xem danh mục', false);
        $response->assertSee('Nạp tiền', false);
        $response->assertSee('Giao dịch gần đây', false);
        $response->assertSee('Danh mục tài khoản', false);
        $response->assertSee('Sản phẩm nổi bật', false);
        $response->assertSee('Công cụ FC Online', false);
        $response->assertSee('Tính thuế cầu thủ', false);
        $response->assertSee('Đào tạo OVR', false);
        $response->assertDontSee('Bài viết', false);
    }

    public function test_featured_products_come_before_account_categories(): void
    {
        $this->get(route('home'))->assertSeeInOrder([
            'id="san-pham-noi-bat"',
            'id="danh-muc-tai-khoan"',
        ], false);
    }

    public function test_posts_page_no_longer_exists(): void
    {
        $this->get('/bai-viet')->assertNotFound();
    }

    public function test_home_page_shows_empty_states_instead_of_placeholder_data(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Chưa có giao dịch hoàn tất', false);
        $response->assertSee('Chưa có danh mục', false);
        $response->assertSee('Chưa có sản phẩm', false);
        $response->assertSee('Chưa có số liệu', false);
        $response->assertDontSee('3.717', false);
    }

    public function test_home_page_renders_zalo_facebook_and_tiktok_links(): void
    {
        config([
            'site.social.zalo' => 'https://zalo.me/0982280146',
            'site.social.facebook' => 'https://www.facebook.com/DDTshopVuaBuildTeam',
            'site.social.tiktok' => 'https://www.tiktok.com/@dinhtienfco?lang=vi-VN',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://zalo.me/0982280146', false);
        $response->assertSee('https://www.facebook.com/DDTshopVuaBuildTeam', false);
        $response->assertSee('https://www.tiktok.com/@dinhtienfco?lang=vi-VN', false);
        $response->assertSee('Facebook', false);
        $response->assertDontSee('Telegram', false);
    }
}
