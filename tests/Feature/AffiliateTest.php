<?php

namespace Tests\Feature;

use App\Enums\AffiliateWithdrawalStatus;
use App\Http\Controllers\AffiliateLinkController;
use App\Models\AffiliateWithdrawal;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class AffiliateTest extends TestCase
{
    public function test_every_user_gets_a_unique_affiliate_code_and_link(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $first->affiliate_code);
        $this->assertNotSame($first->affiliate_code, $second->affiliate_code);
        $this->assertStringEndsWith('/gioi-thieu/'.$first->affiliate_code, $first->affiliateLink());
    }

    public function test_link_click_is_counted_once_and_signup_is_attributed(): void
    {
        $referrer = User::factory()->create();

        $this->get(route('affiliate.track', $referrer->affiliate_code))
            ->assertRedirect(route('register'))
            ->assertCookie(AffiliateLinkController::COOKIE, $referrer->affiliate_code);

        $this->withCookie('dt_ref_seen_'.$referrer->affiliate_code, '1')
            ->get(route('affiliate.track', $referrer->affiliate_code));

        $this->assertSame(1, $referrer->fresh()->affiliate_clicks);

        $this->withCookie(AffiliateLinkController::COOKIE, $referrer->affiliate_code)
            ->post(route('register'), $this->registration('ban-moi@example.com'))
            ->assertRedirect();

        $this->assertSame($referrer->id, User::query()->where('email', 'ban-moi@example.com')->value('referred_by_id'));
    }

    public function test_signup_with_typed_referral_code_and_invalid_code_is_rejected(): void
    {
        $referrer = User::factory()->create();

        $this->post(route('register'), [...$this->registration('typed@example.com'), 'referral_code' => strtolower($referrer->affiliate_code)]);
        $this->assertSame($referrer->id, User::query()->where('email', 'typed@example.com')->value('referred_by_id'));

        $this->post(route('logout'));
        $this->post(route('register'), [...$this->registration('wrong@example.com'), 'referral_code' => 'KHONGTONTAI'])
            ->assertSessionHasErrors('referral_code');
    }

    public function test_purchase_by_referred_member_pays_commission_once(): void
    {
        $referrer = User::factory()->create();
        $buyer = User::factory()->create();
        $buyer->forceFill(['referred_by_id' => $referrer->id])->save();
        $buyer->wallet()->update(['balance' => 1000000]);
        $product = Product::factory()->create(['price' => 400000]);

        $this->actingAs($buyer)->post(route('purchases.store', $product))->assertRedirect();

        $this->assertSame('20000.00', (string) $referrer->fresh()->commission_balance);
        $this->assertDatabaseHas('affiliate_commissions', [
            'referrer_id' => $referrer->id,
            'referred_user_id' => $buyer->id,
            'amount' => 20000,
            'rate' => 5,
        ]);

        $this->actingAs($buyer)->post(route('purchases.store', $product));
        $this->assertDatabaseCount('affiliate_commissions', 1);
    }

    public function test_custom_member_rate_overrides_default_and_disabled_program_pays_nothing(): void
    {
        $referrer = User::factory()->create();
        $referrer->forceFill(['affiliate_rate' => 10])->save();
        $buyer = User::factory()->create();
        $buyer->forceFill(['referred_by_id' => $referrer->id])->save();
        $buyer->wallet()->update(['balance' => 1000000]);

        $this->actingAs($buyer)->post(route('purchases.store', Product::factory()->create(['price' => 300000])));
        $this->assertSame('30000.00', (string) $referrer->fresh()->commission_balance);

        $this->actingAs(User::factory()->admin()->create())->put(route('admin.affiliates.settings'), [
            'enabled' => '0', 'rate' => 5, 'min_withdraw' => 30000, 'max_withdraw' => 10000000, 'cookie_days' => 30,
        ])->assertSessionHasNoErrors();

        $this->actingAs($buyer)->post(route('purchases.store', Product::factory()->create(['price' => 300000])));
        $this->assertSame('30000.00', (string) $referrer->fresh()->commission_balance);
    }

    public function test_withdraw_to_wallet_is_instant_and_respects_limits(): void
    {
        $member = User::factory()->create();
        $member->forceFill(['commission_balance' => 100000])->save();

        $this->actingAs($member)->post(route('affiliate.withdraw'), ['amount' => 20000, 'method' => 'wallet'])
            ->assertSessionHasErrors('amount');
        $this->actingAs($member)->post(route('affiliate.withdraw'), ['amount' => 200000, 'method' => 'wallet'])
            ->assertSessionHasErrors('amount');

        $this->actingAs($member)->post(route('affiliate.withdraw'), ['amount' => 60000, 'method' => 'wallet'])
            ->assertSessionHasNoErrors();

        $this->assertSame('40000.00', (string) $member->fresh()->commission_balance);
        $this->assertSame('60000.00', (string) $member->wallet()->first()->balance);
        $this->assertDatabaseHas('affiliate_withdrawals', ['user_id' => $member->id, 'method' => 'wallet', 'status' => 'approved']);
    }

    public function test_bank_withdrawal_is_held_and_rejection_refunds(): void
    {
        $member = User::factory()->create();
        $member->forceFill(['commission_balance' => 100000])->save();
        $manager = User::factory()->manager()->create();

        $this->actingAs($member)->post(route('affiliate.withdraw'), [
            'amount' => 50000,
            'method' => 'bank',
            'bank_name' => 'MB Bank',
            'bank_account_number' => '0123 456 789',
            'bank_account_name' => 'NGUYEN VAN A',
        ])->assertSessionHasNoErrors();

        $withdrawal = AffiliateWithdrawal::query()->firstOrFail();
        $this->assertSame('50000.00', (string) $member->fresh()->commission_balance);
        $this->assertSame('0123456789', $withdrawal->bank_account_number);
        $this->assertNotSame('0123456789', \DB::table('affiliate_withdrawals')->value('bank_account_number'));

        $this->actingAs($manager)->get(route('admin.affiliates.withdrawals'))->assertOk()->assertSee('0123456789');

        $this->actingAs($manager)->post(route('admin.affiliates.withdrawals.process', $withdrawal), ['decision' => 'reject', 'note' => 'Sai tên'])
            ->assertSessionHasNoErrors();

        $this->assertSame(AffiliateWithdrawalStatus::Rejected, $withdrawal->fresh()->status);
        $this->assertSame('100000.00', (string) $member->fresh()->commission_balance);

        $this->actingAs($manager)->post(route('admin.affiliates.withdrawals.process', $withdrawal), ['decision' => 'approve'])
            ->assertSessionHasErrors('withdrawal');
    }

    public function test_bank_withdrawal_requires_bank_details_and_cannot_exceed_balance(): void
    {
        $member = User::factory()->create();
        $member->forceFill(['commission_balance' => 40000])->save();

        $this->actingAs($member)->post(route('affiliate.withdraw'), ['amount' => 30000, 'method' => 'bank'])
            ->assertSessionHasErrors(['bank_name', 'bank_account_number', 'bank_account_name']);

        $this->actingAs($member)->post(route('affiliate.withdraw'), ['amount' => 50000, 'method' => 'wallet'])
            ->assertSessionHasErrors('amount');

        $this->assertSame('40000.00', (string) $member->fresh()->commission_balance);
    }

    public function test_member_affiliate_page_shows_link_and_stats(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('affiliate.show'))
            ->assertOk()
            ->assertSee($member->affiliateLink())
            ->assertSee('5%')
            ->assertSee('Rút hoa hồng');

        $this->get(route('logout'));
    }

    public function test_admin_pages_and_dev_only_edits(): void
    {
        $manager = User::factory()->manager()->create();
        $developer = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($manager)->get(route('admin.affiliates.index'))->assertOk();
        $this->actingAs($manager)->put(route('admin.affiliates.member', $member), ['affiliate_code' => 'VIP2026', 'affiliate_rate' => 20])->assertForbidden();

        $this->actingAs($developer)->put(route('admin.affiliates.member', $member), ['affiliate_code' => 'vip2026', 'affiliate_rate' => 8])
            ->assertSessionHasNoErrors();

        $this->assertSame('VIP2026', $member->fresh()->affiliate_code);
        $this->assertSame('8.00', (string) $member->fresh()->affiliate_rate);
        $this->assertDatabaseHas('activity_logs', ['action' => 'affiliate.member_updated']);

        $this->actingAs($member)->get(route('admin.affiliates.index'))->assertForbidden();
    }

    /**
     * @return array<string, string>
     */
    private function registration(string $email): array
    {
        return [
            'name' => 'Ban moi',
            'email' => $email,
            'password' => 'MatKhau#2026',
            'password_confirmation' => 'MatKhau#2026',
        ];
    }
}
