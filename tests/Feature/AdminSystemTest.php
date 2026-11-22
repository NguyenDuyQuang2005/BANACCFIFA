<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Deposit;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSystemTest extends TestCase
{
    public function test_every_admin_page_renders_for_admin_and_is_forbidden_for_member(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        Banner::factory()->create();

        $routes = [
            route('admin.dashboard'),
            route('admin.members.index'),
            route('admin.members.show', $member),
            route('admin.wallet-transactions.index'),
            route('admin.banners.index'),
            route('admin.banners.create'),
            route('admin.contact-messages.index'),
            route('admin.settings.edit'),
            route('admin.activity-logs.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
            $this->actingAs($member)->get($url)->assertForbidden();
        }
    }

    public function test_admin_can_credit_balance_with_reason_and_it_is_logged(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.members.balance', $member), [
                'type' => 'credit',
                'amount' => 50000,
                'reason' => 'Bù tiền nạp lỗi',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('50000.00', (string) $member->wallet()->first()->balance);
        $this->assertDatabaseHas('wallet_transactions', ['type' => 'credit', 'amount' => 50000, 'balance_after' => 50000]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'wallet.adjusted', 'user_id' => $admin->id, 'subject_id' => $member->id]);
    }

    public function test_admin_cannot_debit_more_than_balance_or_skip_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.members.balance', $member), ['type' => 'debit', 'amount' => 10000, 'reason' => 'Thu hồi tiền'])
            ->assertSessionHasErrors('amount');

        $this->actingAs($admin)
            ->post(route('admin.members.balance', $member), ['type' => 'credit', 'amount' => 10000, 'reason' => ''])
            ->assertSessionHasErrors('reason');

        $this->assertSame('0.00', (string) $member->wallet()->first()->balance);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_member_cannot_adjust_balance(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->post(route('admin.members.balance', $member), ['type' => 'credit', 'amount' => 50000, 'reason' => 'Tự cộng tiền'])
            ->assertForbidden();

        $this->assertSame('0.00', (string) $member->wallet()->first()->balance);
    }

    public function test_admin_can_change_role_but_not_their_own(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.members.role', $member), ['role' => 'admin'])->assertRedirect();
        $this->assertSame(UserRole::Admin, $member->fresh()->role);

        $this->actingAs($admin)->put(route('admin.members.role', $admin), ['role' => 'member'])->assertSessionHasErrors('role');
        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }

    public function test_banned_member_is_logged_out_and_cannot_log_in(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['password' => 'password-123']);

        $this->actingAs($admin)->post(route('admin.members.ban', $member))->assertRedirect();
        $this->assertNotNull($member->fresh()->banned_at);

        $this->actingAs($member->fresh())->get(route('account'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->post(route('login'), ['email' => $member->email, 'password' => 'password-123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_cannot_ban_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.members.ban', $admin))->assertSessionHasErrors('ban');
        $this->assertNull($admin->fresh()->banned_at);
    }

    public function test_settings_update_overrides_public_site_links(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'site_name' => 'DINHTIENFCO',
                'social_zalo' => 'https://zalo.me/0911111111',
                'social_telegram' => 'https://t.me/dinhtienfco',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'social_zalo', 'value' => 'https://zalo.me/0911111111']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'settings.updated']);

        Cache::forget('site_settings');
        $this->app->make(SiteSettings::class)->applyToConfig();

        $this->get(route('home'))
            ->assertSee('https://zalo.me/0911111111', false)
            ->assertSee('https://t.me/dinhtienfco', false);
    }

    public function test_settings_reject_non_http_links(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), ['site_name' => 'X', 'social_zalo' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('social_zalo');
    }

    public function test_banner_crud_shows_live_banner_on_home_and_logs(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'title' => 'Bơm BP siêu tốc',
                'link_url' => '/danh-muc',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.banners.index'));

        Banner::factory()->create(['title' => 'Banner da tat', 'is_active' => false]);
        Banner::factory()->create(['title' => 'Banner het han', 'ends_at' => now()->subDay()]);

        $this->get(route('home'))
            ->assertSee('Bơm BP siêu tốc')
            ->assertDontSee('Banner da tat')
            ->assertDontSee('Banner het han');

        $this->assertDatabaseHas('activity_logs', ['action' => 'banner.created']);
    }

    public function test_hero_image_replaces_text_hero_and_can_be_removed(): void
    {
        Storage::fake('public');
        $manager = User::factory()->manager()->create();

        $this->get(route('home'))->assertSee('Nền tảng tài khoản FC Online')->assertDontSee('data-hero-media', false);

        $this->actingAs($manager)
            ->put(route('admin.hero.update'), [
                'hero_media' => UploadedFile::fake()->image('hero.jpg', 1920, 820)->size(15_000),
                'hero_link_url' => '/danh-muc',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->get(route('home'))
            ->assertSee('data-hero-media', false)
            ->assertSee('/storage/hero/', false)
            ->assertSee('href="/danh-muc"', false)
            ->assertDontSee('Nền tảng tài khoản FC Online');

        $this->actingAs($manager)->delete(route('admin.hero.destroy'))->assertRedirect();

        $this->get(route('home'))->assertDontSee('data-hero-media', false)->assertSee('Nền tảng tài khoản FC Online');
    }

    public function test_hero_accepts_video_and_rejects_other_files(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.hero.update'), [
                'hero_media' => UploadedFile::fake()->create('intro.mp4', 8_000, 'video/mp4'),
            ])
            ->assertSessionHasNoErrors();

        $this->get(route('home'))->assertSee('<video', false)->assertSee('autoplay muted loop', false);

        $this->actingAs($admin)
            ->put(route('admin.hero.update'), [
                'hero_media' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('hero_media');

        $this->actingAs(User::factory()->create())->put(route('admin.hero.update'), [])->assertForbidden();
    }

    public function test_admin_category_update_is_logged_with_field_names_only(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Ten moi',
            'slug' => $category->slug,
            'sort_order' => 3,
            'is_visible' => '1',
        ]);

        $log = ActivityLog::query()->where('action', 'category.updated')->firstOrFail();
        $this->assertContains('name', $log->properties['changed']);
        $this->assertStringNotContainsString('Ten moi', json_encode($log->properties));
    }

    public function test_deposit_confirmation_is_logged(): void
    {
        $admin = User::factory()->admin()->create();
        $deposit = Deposit::factory()->create();

        $this->actingAs($admin)->post(route('admin.deposits.confirm', $deposit))->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['action' => 'deposit.confirmed', 'subject_id' => $deposit->id]);
    }
}
