<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class CatalogPageTest extends TestCase
{
    public function test_catalog_lists_available_products_from_the_database(): void
    {
        Product::factory()->create([
            'name' => 'Acc BP trang test',
            'code' => 'DT1001',
        ]);
        Product::factory()->hidden()->create();

        $response = $this->get(route('catalog.index'));

        $response->assertOk();
        $response->assertSee('DT1001', false);
        $response->assertSee('Acc BP trang test', false);
    }

    public function test_catalog_hides_hidden_products_and_hidden_categories(): void
    {
        $hiddenCategory = Category::factory()->hidden()->create(['name' => 'Danh muc an']);
        Product::factory()->create([
            'category_id' => $hiddenCategory->id,
            'name' => 'Acc trong danh muc an',
        ]);
        Product::factory()->hidden()->create(['name' => 'Acc an khoi shop']);

        $response = $this->get(route('catalog.index'));

        $response->assertOk();
        $response->assertDontSee('Acc trong danh muc an', false);
        $response->assertDontSee('Acc an khoi shop', false);
        $this->get(route('catalog.category', $hiddenCategory))->assertNotFound();
    }

    public function test_catalog_filters_by_code_price_and_sort(): void
    {
        $cheap = Product::factory()->create(['code' => 'DT2001', 'price' => 200000, 'name' => 'Acc re']);
        Product::factory()->create(['code' => 'DT2002', 'price' => 900000, 'name' => 'Acc dat']);

        $this->get(route('catalog.index', ['q' => 'DT2001']))
            ->assertOk()
            ->assertSee('DT2001', false)
            ->assertDontSee('DT2002', false);

        $this->get(route('catalog.index', ['price_max' => 300000]))
            ->assertOk()
            ->assertSee('DT2001', false)
            ->assertDontSee('DT2002', false);

        $this->get(route('catalog.index', ['sort' => 'price_desc']))
            ->assertOk()
            ->assertSeeInOrder(['Acc dat', 'Acc re']);

        $this->assertTrue($cheap->isAvailable());
    }

    public function test_category_page_only_shows_products_in_that_category(): void
    {
        $category = Category::factory()->create(['name' => 'BP trang']);
        $other = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Acc dung danh muc',
        ]);
        Product::factory()->create([
            'category_id' => $other->id,
            'name' => 'Acc khac danh muc',
        ]);

        $this->get(route('catalog.category', $category))
            ->assertOk()
            ->assertSee('Acc dung danh muc', false)
            ->assertDontSee('Acc khac danh muc', false);
    }

    public function test_category_page_shows_notice_with_zalo_link(): void
    {
        $category = Category::factory()->create([
            'notice' => 'Phien ae nhan tin vao Zalo nhe',
            'notice_url' => 'https://zalo.me/0900000000',
        ]);

        $this->get(route('catalog.category', $category))
            ->assertOk()
            ->assertSee('Phien ae nhan tin vao Zalo nhe', false)
            ->assertSee('href="https://zalo.me/0900000000"', false);
    }

    public function test_home_category_card_shows_available_count_and_view_all_button(): void
    {
        $category = Category::factory()->create(['name' => 'Acc reg trang thong tin']);
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        Product::factory()->hidden()->create(['category_id' => $category->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Acc reg trang thong tin')
            ->assertSee('Còn lại: <span class="font-semibold text-accent">3</span>', false)
            ->assertSee('Xem tất cả')
            ->assertSee(route('catalog.category', $category), false);
    }

    public function test_category_page_without_notice_has_no_banner(): void
    {
        $category = Category::factory()->create(['notice' => null]);

        $this->get(route('catalog.category', $category))
            ->assertOk()
            ->assertDontSee('data-category-notice', false);
    }
}
