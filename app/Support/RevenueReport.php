<?php

namespace App\Support;

use App\Enums\DepositStatus;
use App\Enums\OrderStatus;
use App\Models\Deposit;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Shop revenue (completed account orders) for the admin revenue page.
 * Bucketing is done in PHP so it works the same on MySQL and SQLite.
 */
class RevenueReport
{
    /**
     * @var array<string, string>
     */
    public const PERIODS = [
        '7d' => '7 ngày',
        '30d' => '30 ngày',
        '90d' => '90 ngày',
        '12m' => '12 tháng',
    ];

    public readonly CarbonImmutable $start;

    public readonly CarbonImmutable $end;

    public readonly bool $isMonthly;

    public function __construct(public readonly string $period = '30d')
    {
        $now = CarbonImmutable::now();
        $this->isMonthly = $period === '12m';
        $this->end = $now->endOfDay();
        $this->start = $this->isMonthly
            ? $now->startOfMonth()->subMonths(11)
            : $now->startOfDay()->subDays((int) rtrim($period, 'd') - 1);
    }

    public static function fromRequest(?string $period): self
    {
        return new self(array_key_exists((string) $period, self::PERIODS) ? (string) $period : '30d');
    }

    /**
     * One bucket per day (or month) in the period, including empty ones.
     *
     * @return Collection<int, array{key: string, label: string, tooltip: string, revenue: float, orders: int}>
     */
    public function series(): Collection
    {
        $format = $this->isMonthly ? 'Y-m' : 'Y-m-d';

        $grouped = $this->completedOrders($this->start, $this->end)
            ->get(['total', 'created_at'])
            ->groupBy(fn (Order $order): string => $order->created_at->format($format));

        $buckets = collect();
        $cursor = $this->start;

        while ($cursor->lessThanOrEqualTo($this->end)) {
            $key = $cursor->format($format);
            $orders = $grouped->get($key, collect());

            $buckets->push([
                'key' => $key,
                'label' => $this->isMonthly ? $cursor->format('m/Y') : $cursor->format('d/m'),
                'tooltip' => $this->isMonthly ? 'Tháng '.$cursor->format('m/Y') : $cursor->format('d/m/Y'),
                'revenue' => (float) $orders->sum('total'),
                'orders' => $orders->count(),
            ]);

            $cursor = $this->isMonthly ? $cursor->addMonth() : $cursor->addDay();
        }

        return $buckets;
    }

    /**
     * @return array{revenue: float, orders: int, average: float, deposits: float, previous_revenue: float, change: float|null}
     */
    public function summary(): array
    {
        $current = $this->completedOrders($this->start, $this->end);
        $revenue = (float) (clone $current)->sum('total');
        $orders = (clone $current)->count();

        $length = $this->start->diffInSeconds($this->end);
        $previousEnd = $this->start->subSecond();
        $previousStart = $previousEnd->subSeconds((int) $length);
        $previousRevenue = (float) $this->completedOrders($previousStart, $previousEnd)->sum('total');

        return [
            'revenue' => $revenue,
            'orders' => $orders,
            'average' => $orders > 0 ? $revenue / $orders : 0.0,
            'deposits' => (float) Deposit::query()
                ->where('status', DepositStatus::Success)
                ->whereBetween('confirmed_at', [$this->start, $this->end])
                ->sum('amount'),
            'previous_revenue' => $previousRevenue,
            'change' => $previousRevenue > 0 ? ($revenue - $previousRevenue) / $previousRevenue * 100 : null,
        ];
    }

    /**
     * Revenue per category in the period, largest first.
     *
     * @return Collection<int, array{name: string, revenue: float, orders: int}>
     */
    public function byCategory(): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', OrderStatus::Completed->value)
            ->whereNull('orders.deleted_at')
            ->whereBetween('orders.created_at', [$this->start, $this->end])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get([
                'categories.name',
                DB::raw('SUM(order_items.price) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders'),
            ])
            ->map(fn (object $row): array => [
                'name' => $row->name ?? 'Không rõ danh mục',
                'revenue' => (float) $row->revenue,
                'orders' => (int) $row->orders,
            ]);
    }

    /**
     * @return Builder<Order>
     */
    private function completedOrders(CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return Order::query()
            ->where('status', OrderStatus::Completed)
            ->whereBetween('created_at', [$from, $to]);
    }
}
