<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_member_cannot_open_admin_pages(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_admin_login_redirects_to_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin-test@example.com',
        ]);

        $this->post('/dang-nhap', [
            'email' => 'admin-test@example.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Acc Team Color moi',
                'slug' => '',
                'description' => 'Danh muc that',
                'sort_order' => 11,
                'is_visible' => '1',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Acc Team Color moi',
            'is_visible' => 1,
        ]);
    }

    public function test_image_uploads_accept_up_to_20mb_and_reject_larger(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $payload = [
            'name' => $category->name,
            'slug' => $category->slug,
            'sort_order' => 1,
            'is_visible' => '1',
        ];

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                ...$payload,
                'image' => UploadedFile::fake()->image('big.jpg', 1920, 1080)->size(19_000),
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                ...$payload,
                'image' => UploadedFile::fake()->image('too-big.jpg', 1920, 1080)->size(21_000),
            ])
            ->assertSessionHasErrors('image');
    }

    public function test_admin_can_upload_and_remove_category_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $payload = [
            'name' => $category->name,
            'slug' => $category->slug,
            'sort_order' => 1,
            'is_visible' => '1',
        ];

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                ...$payload,
                'image' => UploadedFile::fake()->image('banner.jpg', 1280, 720),
            ])
            ->assertSessionHasNoErrors();

        $path = $category->fresh()->image_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [...$payload, 'remove_image' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertNull($category->fresh()->image_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_set_category_notice_and_rejects_invalid_url(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Bom BP']);
        $payload = [
            'name' => 'Bom BP',
            'slug' => $category->slug,
            'notice' => 'Phien ae nhan tin vao Zalo nhe',
            'notice_url' => 'https://zalo.me/0900000000',
            'sort_order' => 1,
            'is_visible' => '1',
        ];

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'notice' => 'Phien ae nhan tin vao Zalo nhe',
            'notice_url' => 'https://zalo.me/0900000000',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [...$payload, 'notice_url' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('notice_url');
    }

    public function test_admin_can_create_a_product_and_upload_an_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Acc BP test admin',
                'code' => 'DT9999',
                'price' => 250000,
                'status' => ProductStatus::Available->value,
                'is_featured' => '1',
                'credentials' => 'user:pass-admin',
                'featured_players_text' => "Ronaldo\nMessi",
            ])
            ->assertRedirect();

        $product = Product::query()->where('code', 'DT9999')->first();
        $this->assertNotNull($product);
        $this->assertSame(['Ronaldo', 'Messi'], $product->featured_players);
        $this->assertSame('user:pass-admin', $product->credentials);

        $this->actingAs($admin)
            ->post(route('admin.products.images.store', $product), [
                'images' => [UploadedFile::fake()->image('acc.jpg')],
                'alt' => 'Anh acc',
                'is_primary' => '1',
            ])
            ->assertRedirect();

        $this->assertSame(1, $product->images()->count());
        Storage::disk('public')->assertExists($product->images()->first()->path);

        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertDontSee('user:pass-admin', false);
    }

    public function test_admin_can_create_a_payment_method(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.payment-methods.store'), [
                'name' => 'Vietcombank',
                'account_name' => 'DINH TIEN',
                'account_number' => '0123456789',
                'bank_code' => 'VCB',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.payment-methods.index'));

        $this->assertDatabaseHas('payment_methods', [
            'account_number' => '0123456789',
            'is_active' => 1,
        ]);
    }

    public function test_admin_seeder_creates_admin_from_config(): void
    {
        config([
            'site.admin.email' => 'seed-admin@example.com',
            'site.admin.password' => 'secret-admin-pass',
        ]);

        $this->seed(AdminUserSeeder::class);

        $user = User::query()->where('email', 'seed-admin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertSame('0.00', $user->wallet->balance);
        $this->assertTrue(Hash::check('secret-admin-pass', $user->password));
    }
}
