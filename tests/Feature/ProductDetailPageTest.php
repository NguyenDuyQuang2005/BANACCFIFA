<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class ProductDetailPageTest extends TestCase
{
    public function test_product_detail_shows_public_fields_and_hides_credentials(): void
    {
        $product = Product::factory()->sold()->create([
            'name' => 'Acc da ban',
            'code' => 'DT3001',
            'featured_players' => ['Cau thu A'],
        ]);

        $response = $this->get(route('catalog.show', $product));

        $response->assertOk();
        $response->assertSee('DT3001', false);
        $response->assertSee('Acc da ban', false);
        $response->assertSee('Cau thu A', false);
        $response->assertSee('Đã bán', false);
        $response->assertDontSee('game-user:secret-pass', false);
        $response->assertDontSee('secret-pass', false);
    }

    public function test_hidden_product_returns_404(): void
    {
        $product = Product::factory()->hidden()->create();

        $this->get(route('catalog.show', $product))->assertNotFound();
    }

    public function test_related_products_come_from_the_same_category(): void
    {
        $product = Product::factory()->create(['name' => 'Acc goc']);
        $related = Product::factory()->create([
            'category_id' => $product->category_id,
            'name' => 'Acc lien quan',
        ]);
        Product::factory()->create(['name' => 'Acc khac loai']);

        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertSee('Acc lien quan', false)
            ->assertDontSee('Acc khac loai', false);

        $this->assertTrue($related->category_id === $product->category_id);
    }
}
