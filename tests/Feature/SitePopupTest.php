<?php

namespace Tests\Feature;

use App\Models\Popup;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SitePopupTest extends TestCase
{
    public function test_live_popup_is_rendered_on_public_pages(): void
    {
        Popup::factory()->create([
            'title' => 'Gia nhập cộng đồng Zalo',
            'content' => 'Cứ 50 thành viên tặng 5 voucher 50k trên web.',
            'button_label' => 'Vào nhóm Zalo',
            'button_url' => 'https://zalo.me/g/abc',
            'dismiss_minutes' => 60,
        ]);

        foreach ([route('home'), route('catalog.index')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-site-popup', false)
                ->assertSee('Gia nhập cộng đồng Zalo')
                ->assertSee('href="https://zalo.me/g/abc"', false)
                ->assertSee('data-popup-minutes="60"', false);
        }
    }

    public function test_inactive_or_expired_popup_is_not_rendered(): void
    {
        Popup::factory()->create(['title' => 'Popup tat', 'is_active' => false]);
        Popup::factory()->create(['title' => 'Popup het han', 'ends_at' => now()->subMinute()]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-site-popup', false);
    }

    public function test_admin_can_create_popup_with_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.popups.store'), [
                'title' => 'Popup co anh',
                'content' => 'Noi dung',
                'media_type' => 'image',
                'image' => UploadedFile::fake()->image('banner.jpg', 800, 600),
                'button_label' => 'Vào nhóm Zalo',
                'button_url' => 'https://zalo.me/g/abc',
                'dismiss_minutes' => 60,
                'sort_order' => 0,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.popups.index'))
            ->assertSessionHasNoErrors();

        $popup = Popup::query()->firstOrFail();
        $this->assertSame('image', $popup->media_type);
        Storage::disk('public')->assertExists($popup->media_path);
        $this->assertDatabaseHas('activity_logs', ['action' => 'popup.created']);
    }

    public function test_admin_can_upload_video_and_switching_to_none_removes_it(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $popup = Popup::factory()->create();

        $payload = [
            'title' => 'Popup video',
            'media_type' => 'video',
            'dismiss_minutes' => 60,
            'sort_order' => 0,
            'is_active' => '1',
        ];

        $this->actingAs($admin)
            ->put(route('admin.popups.update', $popup), [
                ...$payload,
                'video' => UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4'),
            ])
            ->assertSessionHasNoErrors();

        $path = $popup->fresh()->media_path;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($admin)
            ->put(route('admin.popups.update', $popup), [...$payload, 'media_type' => 'none'])
            ->assertSessionHasNoErrors();

        $this->assertNull($popup->fresh()->media_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_popup_validation_rejects_missing_media_and_unsafe_links(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.popups.store'), [
                'title' => 'Thieu anh',
                'media_type' => 'image',
                'button_label' => 'Bam',
                'button_url' => 'javascript:alert(1)',
                'dismiss_minutes' => 60,
                'sort_order' => 0,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['button_url']);

        $this->actingAs($admin)
            ->post(route('admin.popups.store'), [
                'title' => 'Thieu anh',
                'media_type' => 'image',
                'dismiss_minutes' => 60,
                'sort_order' => 0,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['image']);

        $this->assertDatabaseCount('popups', 0);
    }

    public function test_member_cannot_manage_popups(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('admin.popups.index'))->assertForbidden();
    }
}
