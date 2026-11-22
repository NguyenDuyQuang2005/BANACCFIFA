<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Tests\TestCase;

class VoucherPurchaseTest extends TestCase
{
    public function test_quote_shows_price_balance_and_applied_voucher(): void
    {
        $buyer = $this->buyerWith(1_000_000);
        $product = Product::factory()->create(['price' => 400_000]);
        Voucher::factory()->create(['code' => 'GIAM50K', 'value' => 50_000]);

        $this->actingAs($buyer)->postJson(route('purchases.quote', $product))
            ->assertOk()
            ->assertJson(['available' => true, 'subtotal' => '400.000₫', 'total' => '400.000₫', 'enough_balance' => true, 'has_discount' => false]);

        $this->actingAs($buyer)->postJson(route('purchases.quote', $product), ['voucher' => 'giam50k'])
            ->assertJson(['has_discount' => true, 'discount' => '50.000₫', 'total' => '350.000₫', 'voucher_error' => null]);

        $this->actingAs($buyer)->postJson(route('purchases.quote', $product), ['voucher' => 'KHONGCO'])
            ->assertJson(['has_discount' => false, 'voucher_error' => 'Mã giảm giá không tồn tại.']);
    }

    public function test_json_purchase_with_voucher_charges_discounted_total_and_returns_credentials(): void
    {
        $buyer = $this->buyerWith(1_000_000);
        $product = Product::factory()->create(['price' => 400_000, 'credentials' => "user: acc01\npass: secret"]);
        $voucher = Voucher::factory()->percent(10, 30_000)->create(['code' => 'SALE10']);

        $response = $this->actingAs($buyer)->postJson(route('purchases.store', $product), ['voucher' => 'SALE10']);

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('items.0.credentials', "user: acc01\npass: secret")
            ->assertJsonPath('order.total', '370.000₫');

        $order = Order::query()->firstOrFail();
        $this->assertSame('400000.00', (string) $order->subtotal);
        $this->assertSame('30000.00', (string) $order->discount_total);
        $this->assertSame('370000.00', (string) $order->total);
        $this->assertSame('SALE10', $order->voucher_code);
        $this->assertSame('630000.00', (string) $buyer->wallet()->first()->balance);
        $this->assertSame(1, $voucher->fresh()->used_count);
        $this->assertDatabaseHas('voucher_redemptions', ['voucher_id' => $voucher->id, 'user_id' => $buyer->id, 'order_id' => $order->id]);
        $this->assertSame(ProductStatus::Sold, $product->fresh()->status);
    }

    public function test_voucher_per_user_and_total_limits(): void
    {
        $buyer = $this->buyerWith(5_000_000);
        Voucher::factory()->create(['code' => 'MOTLAN', 'per_user_limit' => 1, 'usage_limit' => 2]);

        $this->actingAs($buyer)->postJson(route('purchases.store', Product::factory()->create(['price' => 200_000])), ['voucher' => 'MOTLAN'])->assertOk();
        $this->actingAs($buyer)->postJson(route('purchases.store', Product::factory()->create(['price' => 200_000])), ['voucher' => 'MOTLAN'])
            ->assertStatus(422)
            ->assertJson(['reason' => 'voucher', 'message' => 'Bạn đã dùng hết lượt của mã giảm giá này.']);

        $other = $this->buyerWith(5_000_000);
        $this->actingAs($other)->postJson(route('purchases.store', Product::factory()->create(['price' => 200_000])), ['voucher' => 'MOTLAN'])->assertOk();

        $third = $this->buyerWith(5_000_000);
        $this->actingAs($third)->postJson(route('purchases.store', Product::factory()->create(['price' => 200_000])), ['voucher' => 'MOTLAN'])
            ->assertStatus(422)
            ->assertJson(['message' => 'Mã giảm giá đã hết lượt sử dụng.']);

        $this->assertSame(2, Voucher::query()->where('code', 'MOTLAN')->value('used_count'));
    }

    public function test_voucher_rules_expiry_min_order_and_member_only(): void
    {
        $buyer = $this->buyerWith(5_000_000);
        $product = Product::factory()->create(['price' => 100_000]);
        Voucher::factory()->create(['code' => 'HETHAN', 'ends_at' => now()->subDay()]);
        Voucher::factory()->create(['code' => 'DONLON', 'min_order' => 500_000]);
        Voucher::factory()->create(['code' => 'RIENG', 'user_id' => User::factory()->create()->id]);
        Voucher::factory()->create(['code' => 'TAT', 'is_active' => false]);

        foreach ([
            'HETHAN' => 'Mã giảm giá đã hết hạn.',
            'DONLON' => 'Đơn tối thiểu 500.000₫ để dùng mã này.',
            'RIENG' => 'Mã giảm giá này không dành cho tài khoản của bạn.',
            'TAT' => 'Mã giảm giá không còn hiệu lực.',
        ] as $code => $message) {
            $this->actingAs($buyer)->postJson(route('purchases.quote', $product), ['voucher' => $code])
                ->assertJson(['voucher_error' => $message]);
        }

        $this->assertSame(ProductStatus::Available, $product->fresh()->status);
    }

    public function test_fixed_voucher_never_makes_total_negative(): void
    {
        $buyer = $this->buyerWith(0);
        $product = Product::factory()->create(['price' => 30_000]);
        Voucher::factory()->create(['code' => 'FREE', 'value' => 50_000]);

        $this->actingAs($buyer)->postJson(route('purchases.store', $product), ['voucher' => 'FREE'])->assertOk();

        $this->assertSame('0.00', (string) Order::query()->value('total'));
        $this->assertSame('0.00', (string) $buyer->wallet()->first()->balance);
    }

    public function test_insufficient_balance_and_sold_product_return_reasons(): void
    {
        $buyer = $this->buyerWith(100_000);
        $product = Product::factory()->create(['price' => 400_000]);

        $this->actingAs($buyer)->postJson(route('purchases.quote', $product))
            ->assertJson(['enough_balance' => false, 'shortfall' => '300.000₫']);

        $this->actingAs($buyer)->postJson(route('purchases.store', $product))
            ->assertStatus(422)->assertJson(['reason' => 'balance']);

        $sold = Product::factory()->sold()->create();
        $this->actingAs($this->buyerWith(5_000_000))->postJson(route('purchases.store', $sold))
            ->assertStatus(422)->assertJson(['reason' => 'unavailable']);
    }

    public function test_guest_cannot_quote_or_buy_and_buy_buttons_render(): void
    {
        $product = Product::factory()->create();

        $this->postJson(route('purchases.quote', $product))->assertUnauthorized();
        $this->postJson(route('purchases.store', $product))->assertUnauthorized();

        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertSee('data-buy-modal', false)
            ->assertSee('data-buy-quote-url="'.route('purchases.quote', $product).'"', false)
            ->assertDontSee('Sau khi xác nhận', false);

        $this->get(route('catalog.show', Product::factory()->sold()->create()))->assertNotFound();
    }

    public function test_admin_creates_member_only_voucher_and_manager_can_manage(): void
    {
        $member = User::factory()->create(['email' => 'khach@example.com']);
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->post(route('admin.vouchers.store'), [
            'code' => 'zalo 50k',
            'type' => 'fixed',
            'value' => '50.000',
            'per_user_limit' => 1,
            'user_email' => 'khach@example.com',
            'is_active' => '1',
        ])->assertRedirect(route('admin.vouchers.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('vouchers', ['code' => 'ZALO50K', 'value' => 50000, 'user_id' => $member->id]);

        $this->actingAs($manager)->post(route('admin.vouchers.store'), [
            'type' => 'percent', 'value' => '150', 'is_active' => '1',
        ])->assertSessionHasErrors('value');

        $this->actingAs($manager)->post(route('admin.vouchers.store'), [
            'type' => 'fixed', 'value' => '10.000', 'user_email' => 'khong@co.vn', 'is_active' => '1',
        ])->assertSessionHasErrors('user_email');

        $this->actingAs($manager)->get(route('admin.vouchers.index'))->assertOk()->assertSee('ZALO50K');
        $this->actingAs($member)->get(route('admin.vouchers.index'))->assertForbidden();
    }

    private function buyerWith(int $balance): User
    {
        $user = User::factory()->create();
        $user->wallet()->update(['balance' => $balance]);

        return $user;
    }
}
