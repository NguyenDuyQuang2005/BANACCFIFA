<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class AdminRolesAndRevenueTest extends TestCase
{
    public function test_manager_can_open_shop_pages_and_revenue(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->create();

        $routes = [
            route('admin.dashboard'),
            route('admin.revenue'),
            route('admin.categories.index'),
            route('admin.products.index'),
            route('admin.services.index'),
            route('admin.orders.index'),
            route('admin.deposits.index'),
            route('admin.wallet-transactions.index'),
            route('admin.members.index'),
            route('admin.members.show', $member),
            route('admin.contact-messages.index'),
            route('admin.banners.index'),
            route('admin.popups.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($manager)->get($url)->assertOk();
        }
    }

    public function test_manager_is_blocked_from_dev_only_areas(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->create();

        $this->actingAs($manager)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($manager)->put(route('admin.settings.update'), ['site_name' => 'Hack'])->assertForbidden();
        $this->actingAs($manager)->get(route('admin.activity-logs.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.payment-methods.index'))->assertForbidden();
        $this->actingAs($manager)
            ->post(route('admin.members.balance', $member), ['type' => 'credit', 'amount' => 50000, 'reason' => 'Tu cong tien'])
            ->assertForbidden();
        $this->actingAs($manager)->put(route('admin.members.role', $manager), ['role' => 'admin'])->assertForbidden();
        $this->actingAs($manager)->post(route('admin.members.ban', $member))->assertForbidden();

        $this->assertSame(UserRole::Manager, $manager->fresh()->role);
        $this->assertSame('0.00', (string) $member->wallet()->first()->balance);
    }

    public function test_manager_menu_hides_dev_only_links_and_dev_sees_them(): void
    {
        $manager = User::factory()->manager()->create();
        $developer = User::factory()->admin()->create();

        $this->actingAs($manager)->get(route('admin.dashboard'))
            ->assertSee('Admin quản lý')
            ->assertSee('Doanh thu &amp; biểu đồ', false)
            ->assertDontSee(route('admin.settings.edit'), false)
            ->assertDontSee(route('admin.activity-logs.index'), false)
            ->assertDontSee(route('admin.payment-methods.index'), false);

        $this->actingAs($developer)->get(route('admin.dashboard'))
            ->assertSee('Admin dev')
            ->assertSee(route('admin.settings.edit'), false)
            ->assertSee(route('admin.activity-logs.index'), false);
    }

    public function test_manager_login_redirects_to_admin_dashboard(): void
    {
        $manager = User::factory()->manager()->create(['password' => 'password-123']);

        $this->post(route('login'), ['email' => $manager->email, 'password' => 'password-123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_dev_admin_can_promote_member_to_manager(): void
    {
        $developer = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($developer)->put(route('admin.members.role', $member), ['role' => 'manager'])->assertRedirect();

        $this->assertSame(UserRole::Manager, $member->fresh()->role);
    }

    public function test_revenue_report_sums_only_completed_orders_in_period(): void
    {
        $manager = User::factory()->manager()->create();
        $category = Category::factory()->create(['name' => 'Acc ICON']);

        $this->travelTo(now()->setTime(12, 0));

        $todayOrder = Order::factory()->create(['total' => 300000, 'created_at' => now()]);
        OrderItem::factory()->create([
            'order_id' => $todayOrder->id,
            'product_id' => Product::factory()->create(['category_id' => $category->id])->id,
            'price' => 300000,
        ]);
        Order::factory()->create(['total' => 200000, 'created_at' => now()->subDays(3)]);
        Order::factory()->create(['total' => 999000, 'status' => OrderStatus::Cancelled, 'created_at' => now()]);
        Order::factory()->create(['total' => 777000, 'created_at' => now()->subDays(40)]);

        $response = $this->actingAs($manager)->get(route('admin.revenue', ['ky' => '7d']));

        $response->assertOk()
            ->assertSee('500.000₫')
            ->assertSee('250.000₫')
            ->assertSee('Acc ICON')
            ->assertSee('data-chart-tooltip', false)
            ->assertDontSee('999.000₫')
            ->assertDontSee('777.000₫');

        $series = $response->viewData('series');
        $this->assertCount(7, $series);
        $this->assertSame(300000.0, $series->last()['revenue']);
        $this->assertSame(2, $response->viewData('summary')['orders']);
    }

    public function test_revenue_monthly_view_and_invalid_period_fallback(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get(route('admin.revenue', ['ky' => '12m']))
            ->assertOk()
            ->assertSee('Doanh thu theo tháng')
            ->assertViewHas('series', fn ($series): bool => $series->count() === 12);

        $this->actingAs($manager)->get(route('admin.revenue', ['ky' => 'abc']))
            ->assertOk()
            ->assertViewHas('series', fn ($series): bool => $series->count() === 30);
    }

    public function test_member_cannot_view_revenue(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.revenue'))->assertForbidden();
    }
}
