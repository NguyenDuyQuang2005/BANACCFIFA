<?php

namespace Tests\Feature;

use App\Actions\ProcessBankTransferAction;
use App\Enums\BankTransactionStatus;
use App\Enums\DepositStatus;
use App\Models\BankTransaction;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SepayAutoDepositTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.sepay.webhook_api_key' => 'test-sepay-key']);
    }

    public function test_matching_transfer_credits_wallet_automatically(): void
    {
        $member = User::factory()->create();
        $deposit = Deposit::factory()->create(['user_id' => $member->id, 'code' => 'DTAB12CD34', 'amount' => 100000]);

        $this->sepay(['id' => 9001, 'content' => 'MBVCB.123456.NGUYEN VAN A chuyen tien DTAB12CD34 FT2611', 'transferAmount' => 100000])
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'matched']);

        $this->assertSame(DepositStatus::Success, $deposit->fresh()->status);
        $this->assertSame('sepay:9001', $deposit->fresh()->provider_reference);
        $this->assertSame('100000.00', (string) $member->wallet()->first()->balance);
        $this->assertDatabaseHas('bank_transactions', ['provider_transaction_id' => '9001', 'status' => 'matched', 'deposit_id' => $deposit->id]);
    }

    public function test_same_webhook_delivered_twice_credits_only_once(): void
    {
        $member = User::factory()->create();
        Deposit::factory()->create(['user_id' => $member->id, 'code' => 'DTAB12CD34', 'amount' => 100000]);

        $payload = ['id' => 9002, 'content' => 'DTAB12CD34', 'transferAmount' => 100000];
        $this->sepay($payload)->assertOk();
        $this->sepay($payload)->assertOk();

        $this->assertSame('100000.00', (string) $member->wallet()->first()->balance);
        $this->assertDatabaseCount('bank_transactions', 1);
        $this->assertDatabaseCount('wallet_transactions', 1);
    }

    public function test_code_glued_to_other_words_and_lowercase_is_still_matched(): void
    {
        $member = User::factory()->create();
        Deposit::factory()->create(['user_id' => $member->id, 'code' => 'DTQW12ER34', 'amount' => 50000]);

        $this->sepay(['id' => 9003, 'content' => 'IBFT napdtqw12er34chuyen', 'transferAmount' => 50000])
            ->assertJson(['status' => 'matched']);

        $this->assertSame('50000.00', (string) $member->wallet()->first()->balance);
    }

    public function test_wrong_amount_is_held_for_admin_review(): void
    {
        $member = User::factory()->create();
        $deposit = Deposit::factory()->create(['user_id' => $member->id, 'code' => 'DTAB12CD34', 'amount' => 100000]);

        $this->sepay(['id' => 9004, 'content' => 'DTAB12CD34', 'transferAmount' => 90000])
            ->assertJson(['status' => 'amount_mismatch']);

        $this->assertSame(DepositStatus::Verifying, $deposit->fresh()->status);
        $this->assertSame('90000.00', $deposit->fresh()->meta['received_amount']);
        $this->assertSame('0.00', (string) $member->wallet()->first()->balance);
    }

    public function test_unknown_content_and_outgoing_transfers_do_not_credit(): void
    {
        $this->sepay(['id' => 9005, 'content' => 'tra tien an trua', 'transferAmount' => 100000])
            ->assertJson(['status' => 'unmatched']);
        $this->sepay(['id' => 9006, 'content' => 'DTAB12CD34', 'transferAmount' => 100000, 'transferType' => 'out'])
            ->assertJson(['status' => 'ignored']);

        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_transfer_to_cancelled_deposit_is_flagged_not_credited(): void
    {
        $member = User::factory()->create();
        Deposit::factory()->create(['user_id' => $member->id, 'code' => 'DTAB12CD34', 'amount' => 100000, 'status' => DepositStatus::Cancelled]);

        $this->sepay(['id' => 9007, 'content' => 'DTAB12CD34', 'transferAmount' => 100000])
            ->assertJson(['status' => 'deposit_closed']);

        $this->assertSame('0.00', (string) $member->wallet()->first()->balance);
    }

    public function test_webhook_rejects_wrong_or_missing_api_key_and_is_off_without_config(): void
    {
        $this->postJson(route('payments.sepay'), $this->payload(['id' => 1]), ['Authorization' => 'Apikey wrong'])->assertUnauthorized();
        $this->postJson(route('payments.sepay'), $this->payload(['id' => 1]))->assertUnauthorized();

        config(['services.sepay.webhook_api_key' => null]);
        $this->postJson(route('payments.sepay'), $this->payload(['id' => 1]), ['Authorization' => 'Apikey '])->assertStatus(503);

        $this->assertDatabaseCount('bank_transactions', 0);
    }

    public function test_new_deposits_get_fixed_format_code_and_vietqr_link(): void
    {
        $member = User::factory()->create();
        $method = PaymentMethod::factory()->create(['bank_code' => 'MB', 'account_number' => '0123456789', 'account_name' => 'DINH TIEN']);

        $this->actingAs($member)->post(route('deposits.store'), ['payment_method_id' => $method->id, 'amount' => 200000]);

        $deposit = Deposit::query()->firstOrFail();
        $this->assertMatchesRegularExpression('/^DT[A-Z0-9]{8}$/', $deposit->code);
        $this->assertSame([$deposit->code], array_values(array_intersect(ProcessBankTransferAction::candidateCodes('ck '.$deposit->code), [$deposit->code])));

        $this->assertStringStartsWith('https://img.vietqr.io/image/MB-0123456789-', $deposit->qrUrl());
        $this->assertStringContainsString('amount=200000', $deposit->qrUrl());
        $this->assertStringContainsString('addInfo='.$deposit->code, $deposit->qrUrl());

        $this->actingAs($member)->get(route('deposits.show', $deposit))
            ->assertOk()
            ->assertSee('img.vietqr.io', false)
            ->assertSee($deposit->code);
    }

    public function test_status_endpoint_reports_live_state_only_to_owner(): void
    {
        $member = User::factory()->create();
        $deposit = Deposit::factory()->create(['user_id' => $member->id, 'code' => 'DTAB12CD34', 'amount' => 100000]);

        $this->actingAs($member)->getJson(route('deposits.status', $deposit))->assertJson(['status' => 'pending', 'open' => true]);

        $this->sepay(['id' => 9008, 'content' => 'DTAB12CD34', 'transferAmount' => 100000]);

        $this->actingAs($member)->getJson(route('deposits.status', $deposit))
            ->assertJson(['status' => 'success', 'successful' => true, 'open' => false]);

        $this->actingAs(User::factory()->create())->getJson(route('deposits.status', $deposit))->assertForbidden();
    }

    public function test_staff_can_view_bank_transactions_but_members_cannot(): void
    {
        BankTransaction::factory()->create(['content' => 'noi dung la', 'status' => BankTransactionStatus::Unmatched]);

        $this->actingAs(User::factory()->manager()->create())
            ->get(route('admin.bank-transactions.index', ['status' => 'review']))
            ->assertOk()
            ->assertSee('noi dung la');

        $this->actingAs(User::factory()->create())->get(route('admin.bank-transactions.index'))->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function sepay(array $overrides): TestResponse
    {
        return $this->postJson(route('payments.sepay'), $this->payload($overrides), ['Authorization' => 'Apikey test-sepay-key']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides): array
    {
        return [
            'id' => 1,
            'gateway' => 'MBBank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber' => '0123456789',
            'code' => null,
            'content' => '',
            'transferType' => 'in',
            'transferAmount' => 100000,
            'accumulated' => 0,
            'subAccount' => null,
            'referenceCode' => 'FT'.random_int(1000, 9999),
            'description' => '',
            ...$overrides,
        ];
    }
}
