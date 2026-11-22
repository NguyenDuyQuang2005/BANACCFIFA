<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductFormTest extends TestCase
{
    public function test_create_with_several_photos_first_becomes_cover(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), $this->payload([
                'images' => [
                    UploadedFile::fake()->image('1.jpg'),
                    UploadedFile::fake()->image('2.png'),
                    UploadedFile::fake()->image('3.webp'),
                ],
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('code', 'DTFORM1')->firstOrFail();
        $images = $product->images()->orderBy('sort_order')->get();

        $this->assertCount(3, $images);
        $this->assertTrue($images[0]->is_primary);
        $this->assertFalse($images[1]->is_primary);
        $images->each(fn ($image) => Storage::disk('public')->assertExists($image->path));
    }

    public function test_update_adds_more_photos_and_cover_can_be_changed(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->payload(['images' => [UploadedFile::fake()->image('a.jpg')]]));
        $product = Product::query()->where('code', 'DTFORM1')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.products.update', $product), $this->payload([
            'images' => [UploadedFile::fake()->image('b.jpg'), UploadedFile::fake()->image('c.jpg')],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(3, $product->images()->count());
        $this->assertSame(1, $product->images()->where('is_primary', true)->count());

        $last = $product->images()->where('is_primary', false)->reorder()->latest('id')->first();
        $this->actingAs($admin)->put(route('admin.products.images.primary', [$product, $last]))->assertRedirect();

        $this->assertTrue($last->fresh()->is_primary);
        $this->assertSame(1, $product->images()->where('is_primary', true)->count());
    }

    public function test_rejects_non_images_and_too_many_photos(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->payload([
            'images' => [UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')],
        ]))->assertSessionHasErrors('images.0');

        $this->actingAs($admin)->post(route('admin.products.store'), $this->payload([
            'images' => array_map(fn (int $i) => UploadedFile::fake()->image("{$i}.jpg", 10, 10), range(1, 21)),
        ]))->assertSessionHasErrors('images');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_prices_typed_with_dots_are_accepted(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), $this->payload(['price' => '1.250.500', 'original_price' => '1 500 000']))
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('code', 'DTFORM1')->firstOrFail();
        $this->assertSame('1250500.00', (string) $product->price);
        $this->assertSame('1500000.00', (string) $product->original_price);
        $this->assertSame(17, $product->discountPercent());
    }

    public function test_discount_percent_computes_sale_price_and_shows_badge(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), $this->payload([
                'price' => '',
                'original_price' => '2.000.000',
                'discount_percent' => '25',
            ]))
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('code', 'DTFORM1')->firstOrFail();
        $this->assertSame('1500000.00', (string) $product->price);
        $this->assertSame(25, $product->discountPercent());

        $this->get(route('catalog.index'))->assertSee('-25%')->assertSee('data-discount-badge', false);
        $this->get(route('catalog.show', $product))->assertSee('-25%');
    }

    public function test_real_bp_in_billions_is_accepted_and_shown_short(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), $this->payload(['bp' => '5.200.000.000']))
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('code', 'DTFORM1')->firstOrFail();
        $this->assertSame(5_200_000_000, $product->bp);
        $this->assertSame('5,2 tỷ', $product->formattedBp());

        $this->get(route('catalog.index', ['bp_min' => '5.000.000.000']))->assertOk()->assertSee('DTFORM1');
        $this->get(route('catalog.index', ['bp_min' => '6.000.000.000']))->assertOk()->assertDontSee('DTFORM1');
    }

    public function test_validation_messages_are_in_vietnamese_not_raw_keys(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->payload(['price' => '2.000.000.000', 'ovr' => '500']))
            ->assertSessionHasErrors([
                'price' => 'Giá bán không được lớn hơn 1000000000.',
                'ovr' => 'OVR không được lớn hơn 200.',
            ]);

        $this->assertStringNotContainsString('validation.', trans('validation.max.numeric', ['attribute' => 'x', 'max' => 1]));
    }

    public function test_original_price_not_above_price_means_no_discount(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), $this->payload(['price' => '500.000', 'original_price' => '400.000']))
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('code', 'DTFORM1')->firstOrFail();
        $this->assertNull($product->original_price);
        $this->assertNull($product->discountPercent());
    }

    public function test_discount_needs_original_price_and_price_needs_a_value(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->payload(['price' => '', 'discount_percent' => '20']))
            ->assertSessionHasErrors('original_price');

        $this->actingAs($admin)->post(route('admin.products.store'), $this->payload(['price' => '']))
            ->assertSessionHasErrors('price');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'category_id' => Category::query()->value('id') ?? Category::factory()->create()->id,
            'name' => 'Acc form test',
            'code' => 'DTFORM1',
            'price' => '300.000',
            'status' => ProductStatus::Available->value,
            'is_featured' => '0',
            ...$overrides,
        ];
    }
}
