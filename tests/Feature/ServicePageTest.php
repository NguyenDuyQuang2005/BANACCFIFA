<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServicePageTest extends TestCase
{
    public function test_home_shows_service_section_and_no_filter_bar(): void
    {
        config(['site.social.zalo' => 'https://zalo.me/0900000000']);
        Service::factory()->create([
            'code' => 'ACC279',
            'name' => 'BƠM BP siêu tốc',
        ]);
        Service::factory()->hidden()->create(['name' => 'Dich vu an']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Dịch vụ FCO')
            ->assertSee('BƠM BP siêu tốc')
            ->assertSee('ACC279')
            ->assertSee('Liên hệ admin')
            ->assertSee('Phiền ae nhắn tin vào Zalo nhé')
            ->assertSee('href="https://zalo.me/0900000000"', false)
            ->assertDontSee('Dich vu an')
            ->assertDontSee('Lọc sản phẩm');
    }

    public function test_home_hides_service_section_when_there_are_no_services(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-service-card', false)
            ->assertDontSee('id="dich-vu-fco"', false);
    }

    public function test_service_page_lists_visible_services_with_own_contact_link(): void
    {
        Service::factory()->create([
            'name' => 'Bom BP 5 ty',
            'contact_label' => 'Nhắn Zalo',
            'contact_url' => 'https://zalo.me/g/bombp',
        ]);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Bom BP 5 ty')
            ->assertSee('Nhắn Zalo')
            ->assertSee('href="https://zalo.me/g/bombp"', false);
    }

    public function test_admin_can_create_service_with_image_and_it_is_logged(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.services.store'), [
                'code' => 'BOMBP',
                'name' => 'Bơm BP',
                'description' => "- Nhanh\n- Bảo hành",
                'image' => UploadedFile::fake()->image('bombp.jpg', 1200, 600),
                'contact_url' => 'https://zalo.me/g/abc',
                'sort_order' => 0,
                'is_visible' => '1',
            ])
            ->assertRedirect(route('admin.services.index'))
            ->assertSessionHasNoErrors();

        $service = Service::query()->firstOrFail();
        Storage::disk('public')->assertExists($service->image_path);
        $this->assertDatabaseHas('activity_logs', ['action' => 'service.created']);
    }

    public function test_service_validation_rejects_unsafe_link_and_duplicate_code(): void
    {
        $admin = User::factory()->admin()->create();
        Service::factory()->create(['code' => 'BOMBP']);

        $this->actingAs($admin)
            ->post(route('admin.services.store'), [
                'code' => 'BOMBP',
                'name' => 'Trung ma',
                'contact_url' => 'javascript:alert(1)',
                'sort_order' => 0,
                'is_visible' => '1',
            ])
            ->assertSessionHasErrors(['code', 'contact_url']);
    }

    public function test_member_cannot_manage_services(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('admin.services.index'))->assertForbidden();
        $this->actingAs($member)->post(route('admin.services.store'), ['name' => 'X'])->assertForbidden();
    }
}
