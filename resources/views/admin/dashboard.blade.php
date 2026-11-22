@php
    $money = fn ($value): string => \Illuminate\Support\Number::format((float) $value, locale: 'vi').'₫';
    $stats = [
        ['label' => 'Doanh thu (đơn hoàn tất)', 'value' => $money($revenueTotal), 'hint' => 'Hôm nay: '.$money($revenueToday), 'url' => route('admin.orders.index')],
        ['label' => 'Tiền nạp thành công', 'value' => $money($depositTotal), 'hint' => 'Hôm nay: '.$money($depositToday), 'url' => route('admin.deposits.index')],
        ['label' => 'Tổng số dư ví thành viên', 'value' => $money($walletBalanceTotal), 'hint' => 'Tiền shop đang giữ hộ', 'url' => route('admin.wallet-transactions.index')],
        ['label' => 'Nạp chờ duyệt', 'value' => number_format($pendingDepositCount), 'hint' => 'Cần xác minh', 'url' => route('admin.deposits.index')],
        ['label' => 'Thành viên', 'value' => number_format($memberCount), 'hint' => 'Mới hôm nay: '.number_format($newMembersToday), 'url' => route('admin.members.index')],
        ['label' => 'Acc còn hàng', 'value' => number_format($availableCount), 'hint' => 'Đã bán: '.number_format($soldCount), 'url' => route('admin.products.index')],
        ['label' => 'Đơn hoàn tất', 'value' => number_format($orderCount), 'hint' => null, 'url' => route('admin.orders.index')],
        ['label' => 'Liên hệ chưa xử lý', 'value' => number_format($openContactCount), 'hint' => null, 'url' => route('admin.contact-messages.index')],
    ];
@endphp

<x-layouts.admin title="Tổng quan">
    <h1 class="font-display text-3xl font-semibold">Tổng quan</h1>
    <p class="mt-2 text-sm text-muted">Số liệu lấy trực tiếp từ database. Không dùng số liệu minh họa.</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <a href="{{ $stat['url'] }}" class="rounded-3xl border border-line bg-surface p-5 transition hover:border-accent/50">
                <p class="text-xs uppercase tracking-widest text-muted">{{ $stat['label'] }}</p>
                <p class="mt-2 font-display text-2xl text-accent">{{ $stat['value'] }}</p>
                @if ($stat['hint'])
                    <p class="mt-1 text-xs text-muted">{{ $stat['hint'] }}</p>
                @endif
            </a>
        @endforeach
    </div>

    <div class="mt-6 flex justify-end">
        <a href="{{ route('admin.revenue') }}" class="text-sm text-accent hover:underline">Xem báo cáo doanh thu chi tiết →</a>
    </div>
    <x-column-chart
        class="mt-2"
        title="Doanh thu 30 ngày gần nhất"
        :items="$revenueSeries->map(fn (array $bucket): array => [
            'label' => $bucket['label'],
            'tooltip' => $bucket['tooltip'],
            'value' => $bucket['revenue'],
            'detail' => $bucket['orders'].' đơn',
        ])->all()"
    />

    <div class="mt-10 grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl">Đơn hàng mới</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-muted hover:text-accent">Xem tất cả</a>
            </div>
            @forelse ($recentOrders as $order)
                <a href="{{ route('admin.orders.show', $order) }}" class="mt-3 flex justify-between gap-3 text-sm hover:text-accent">
                    <span>{{ $order->code }} · {{ $order->user?->email }}</span>
                    <span class="text-muted">{{ $order->formattedTotal() }}</span>
                </a>
            @empty
                <p class="mt-3 text-sm text-muted">Chưa có đơn hàng.</p>
            @endforelse
        </section>
        @can('manage-system')
        <section class="rounded-3xl border border-line bg-surface p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl">Hoạt động gần đây</h2>
                <a href="{{ route('admin.activity-logs.index') }}" class="text-xs text-muted hover:text-accent">Nhật ký</a>
            </div>
            @forelse ($recentActivity as $log)
                <p class="mt-3 text-sm">
                    <span class="text-muted">{{ $log->created_at->format('d/m H:i') }}</span>
                    · {{ $log->description }}
                    <span class="text-muted">— {{ $log->user?->email ?? 'Hệ thống' }}</span>
                </p>
            @empty
                <p class="mt-3 text-sm text-muted">Chưa có hoạt động.</p>
            @endforelse
        </section>
        @endcan
    </div>
</x-layouts.admin>
