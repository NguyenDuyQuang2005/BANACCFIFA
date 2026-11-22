<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneSoldProductsTest extends TestCase
{
    public function test_removes_only_accounts_sold_more_than_thirty_days_ago(): void
    {
        Storage::fake('public');

        $old = Product::factory()->sold()->create(['code' => 'OLD1', 'sold_at' => now()->subDays(31)]);
        $path = UploadedFile::fake()->image('acc.jpg')->store('products', 'public');
        $old->images()->create(['path' => $path, 'alt' => 'acc', 'sort_order' => 1, 'is_primary' => true]);
        $order = Order::factory()->create();
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $old->id, 'credentials' => 'game-user:secret-pass']);

        $recent = Product::factory()->sold()->create(['code' => 'NEW1', 'sold_at' => now()->subDays(29)]);
        $available = Product::factory()->create(['code' => 'AVAIL1']);

        $this->artisan('products:prune-sold')
            ->expectsOutput('Đã xóa 1 acc đã bán quá 30 ngày.')
            ->assertSuccessful();

        $this->assertSoftDeleted($old);
        $this->assertNull(Product::withTrashed()->find($old->id)->credentials);
        $this->assertSame(0, $old->images()->count());
        Storage::disk('public')->assertMissing($path);

        $this->assertNotSoftDeleted($recent);
        $this->assertNotSoftDeleted($available);

        $this->assertSame('game-user:secret-pass', $order->items()->first()->credentials);
        $this->assertDatabaseHas('activity_logs', ['action' => 'products.pruned_sold']);
    }

    public function test_dry_run_and_custom_days(): void
    {
        $product = Product::factory()->sold()->create(['sold_at' => now()->subDays(10)]);

        $this->artisan('products:prune-sold --dry-run --days=7')->assertSuccessful();
        $this->assertNotSoftDeleted($product);

        $this->artisan('products:prune-sold --days=7')->assertSuccessful();
        $this->assertSoftDeleted($product);
    }

    public function test_command_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('products:prune-sold')->assertSuccessful();
    }

    public function test_home_stats_show_members_only(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Giao dịch hoàn tất');
    }
}
