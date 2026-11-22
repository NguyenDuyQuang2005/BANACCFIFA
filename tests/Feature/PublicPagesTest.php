<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_public_navigation_pages_return_successful_responses(): void
    {
        $this->get(route('catalog.index'))->assertOk()->assertSee('Danh mục tài khoản', false);
        $this->get(route('guides'))->assertOk()->assertSee('Hướng dẫn', false);
        $this->get(route('tools.index'))->assertOk()->assertSee('Công cụ FC Online', false);
        $this->get(route('transactions.history'))->assertOk()->assertSee('Lịch sử giao dịch', false);
        $this->get(route('contact'))->assertOk()->assertSee('Liên hệ', false);
        $this->get(route('register'))->assertOk()->assertSee('Đăng ký', false);
        $this->get(route('terms'))->assertOk();
        $this->get(route('privacy'))->assertOk();
        $this->get(route('policies'))->assertOk();
    }

    public function test_guest_account_and_deposit_pages_redirect_to_login(): void
    {
        $this->get(route('account'))->assertRedirect(route('login'));
        $this->get(route('deposit'))->assertRedirect(route('login'));
    }

    public function test_login_and_account_pages_are_not_indexed(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('noindex, nofollow', false);

        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertOk()
            ->assertSee('noindex, nofollow', false)
            ->assertSee('Tài khoản cá nhân', false);
    }

    public function test_tax_tool_page_renders_calculator(): void
    {
        $this->get(route('tools.show', 'tinh-thue'))
            ->assertOk()
            ->assertSee('Tính thuế cầu thủ', false)
            ->assertSee('data-tax-calculator', false)
            ->assertDontSee('Công cụ chưa tính toán', false);
    }

    public function test_unknown_tool_page_returns_404(): void
    {
        $this->get(route('tools.show', 'khong-ton-tai'))->assertNotFound();
    }

    public function test_catalog_keeps_allowed_sort_query(): void
    {
        $this->get(route('catalog.index', ['sort' => 'price_desc']))
            ->assertOk()
            ->assertSee('selected', false);
    }
}
