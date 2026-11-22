<?php

namespace Tests\Feature;

use App\Enums\DepositStatus;
use App\Enums\ProductStatus;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class AuthWalletPurchaseTest extends TestCase
{
    public function test_registration_creates_a_wallet_with_zero_balance(): void
    {
        $response = $this->post('/dang-ky', [
            'name' => 'Thanh Vien',
            'email' => 'thanhvien@example.com',
            'phone' => '0982280146',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('account'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'thanhvien@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->wallet);
        $this->assertSame('0.00', $user->wallet->balance);
    }

    public function test_creating_a_deposit_does_not_credit_the_wallet(): void
    {
        $user = User::factory()->create();
        $method = PaymentMethod::factory()->create();

        $this->actingAs($user)
            ->post(route('deposits.store'), [
                'payment_method_id' => $method->id,
                'amount' => 150000,
            ])
            ->assertRedirect();

        $deposit = $user->deposits()->first();
        $this->assertNotNull($deposit);
        $this->assertSame(DepositStatus::Pending, $deposit->status);
        $this->assertSame('0.00', $user->wallet->fresh()->balance);
    }

    public function test_admin_can_confirm_a_deposit_only_once(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        $deposit = Deposit::factory()->create([
            'user_id' => $member->id,
            'amount' => 200000,
            'status' => DepositStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.deposits.confirm', $deposit))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.deposits.confirm', $deposit))
            ->assertRedirect();

        $this->assertSame(DepositStatus::Success, $deposit->fresh()->status);
        $this->assertSame('200000.00', $member->wallet->fresh()->balance);
        $this->assertSame(1, $member->wallet->transactions()->count());
    }

    public function test_member_cannot_open_admin_deposit_review(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('admin.deposits.index'))
            ->assertForbidden();
    }

    public function test_purchase_debits_wallet_marks_product_sold_and_shows_credentials_to_owner_only(): void
    {
        $buyer = User::factory()->create();
        $buyer->wallet()->update(['balance' => 500000]);
        $stranger = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 150000,
            'credentials' => 'game-user:secret-pass',
        ]);

        $this->actingAs($buyer)
            ->post(route('purchases.store', $product))
            ->assertRedirect();

        $order = $buyer->orders()->with('items')->first();
        $this->assertNotNull($order);
        $this->assertSame('350000.00', $buyer->wallet->fresh()->balance);
        $this->assertSame(ProductStatus::Sold, $product->fresh()->status);
        $this->assertSame('game-user:secret-pass', $order->items->first()->credentials);

        $this->actingAs($buyer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('game-user:secret-pass', false);

        $this->actingAs($stranger)
            ->get(route('orders.show', $order))
            ->assertForbidden();

        $this->get(route('catalog.show', $product))->assertNotFound();
    }

    public function test_purchase_fails_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 150000,
            'credentials' => 'acc:pass',
        ]);

        $this->actingAs($user)
            ->post(route('purchases.store', $product))
            ->assertRedirect(route('deposit'));

        $this->assertSame(0, $user->orders()->count());
        $this->assertSame(ProductStatus::Available, $product->fresh()->status);
        $this->assertSame('0.00', $user->wallet->fresh()->balance);
    }

    public function test_payment_webhook_requires_a_valid_signature_and_is_idempotent(): void
    {
        $secret = 'test-webhook-secret';
        config(['services.payments.webhook_secret' => $secret]);

        $user = User::factory()->create();
        $deposit = Deposit::factory()->create([
            'user_id' => $user->id,
            'amount' => 100000,
            'code' => 'DTTESTCODE1',
        ]);

        $payload = [
            'code' => 'DTTESTCODE1',
            'provider_reference' => 'bank-ref-1',
            'status' => 'success',
        ];
        $body = json_encode($payload);
        $headers = ['X-Payment-Signature' => hash_hmac('sha256', $body, $secret)];

        $this->call('POST', route('payments.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_PAYMENT_SIGNATURE' => 'invalid',
        ], $body)->assertForbidden();

        $this->call('POST', route('payments.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_PAYMENT_SIGNATURE' => $headers['X-Payment-Signature'],
        ], $body)->assertOk();

        $this->call('POST', route('payments.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_PAYMENT_SIGNATURE' => $headers['X-Payment-Signature'],
        ], $body)->assertOk();

        $this->assertSame('100000.00', $user->wallet->fresh()->balance);
        $this->assertSame(1, $user->wallet->transactions()->count());
        $this->assertSame(DepositStatus::Success, $deposit->fresh()->status);
    }

    public function test_payment_webhook_returns_service_unavailable_when_secret_is_missing(): void
    {
        config(['services.payments.webhook_secret' => '']);

        $this->postJson(route('payments.webhook'), [
            'code' => 'DT1',
            'provider_reference' => 'x',
            'status' => 'success',
        ])->assertStatus(503);
    }
}
