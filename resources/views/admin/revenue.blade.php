@php
    $money = fn (float $value): string => number_format($value, 0, ',', '.').'₫';
    $categoryMax = max((float) $categories->max('revenue'), 1);
    $chartItems = $series->map(fn (array $bucket): array => [
        'label' => $bucket['label'],
        'tooltip' => $bucket['tooltip'],
        'value' => $bucket['revenue'],
        'detail' => $bucket['orders'].' đơn',
    ])->all();
@endphp

<x-layouts.admin title="Doanh thu">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold">Doanh thu shop acc</h1>
            <p class="mt-2 text-sm text-muted">
                Tính từ đơn mua tài khoản đã hoàn tất · {{ $report->start->format('d/m/Y') }} – {{ $report->end->format('d/m/Y') }}.
                Dịch vụ liên hệ Zalo (Bơm BP...) không đi qua ví nên không nằm trong số liệu này.
            </p>
        </div>
        <nav class="flex gap-1 rounded-full border border-line bg-surface p-1 text-sm" aria-label="Chọn khoảng thời gian">
            @foreach ($periods as $key => $label)
                <a
                    href="{{ route('admin.revenue', ['ky' => $key]) }}"
                    class="rounded-full px-4 py-1.5 {{ $report->period === $key ? 'bg-primary font-semibold text-white' : 'text-muted hover:text-ink' }}"
                    @if ($report->period === $key) aria-current="page" @endif
                >{{ $label }}</a>
            @endforeach
        </nav>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <section class="rounded-3xl border border-line bg-surface p-5">
            <p class="text-xs uppercase tracking-widest text-muted">Doanh thu</p>
            <p class="mt-2 font-display text-2xl text-ink">{{ $money($summary['revenue']) }}</p>
            <p class="mt-1 text-xs text-muted">
                @if ($summary['change'] === null)
                    Kỳ trước: {{ $money($summary['previous_revenue']) }}
                @else
                    <span class="{{ $summary['change'] >= 0 ? 'text-emerald-400' : 'text-primary' }}">
                        {{ $summary['change'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($summary['change']), 1, ',', '.') }}%
                    </span>
                    so với kỳ trước ({{ $money($summary['previous_revenue']) }})
                @endif
            </p>
        </section>
        <section class="rounded-3xl border border-line bg-surface p-5">
            <p class="text-xs uppercase tracking-widest text-muted">Số đơn hoàn tất</p>
            <p class="mt-2 font-display text-2xl text-ink">{{ number_format($summary['orders']) }}</p>
        </section>
        <section class="rounded-3xl border border-line bg-surface p-5">
            <p class="text-xs uppercase tracking-widest text-muted">Giá trị trung bình / đơn</p>
            <p class="mt-2 font-display text-2xl text-ink">{{ $money($summary['average']) }}</p>
        </section>
        <section class="rounded-3xl border border-line bg-surface p-5">
            <p class="text-xs uppercase tracking-widest text-muted">Tiền nạp đã duyệt</p>
            <p class="mt-2 font-display text-2xl text-ink">{{ $money($summary['deposits']) }}</p>
        </section>
    </div>

    <x-column-chart
        class="mt-6"
        :title="$report->isMonthly ? 'Doanh thu theo tháng' : 'Doanh thu theo ngày'"
        :items="$chartItems"
    />

    <section class="mt-6 rounded-3xl border border-line bg-surface p-5">
        <h2 class="font-display text-lg font-semibold">Doanh thu theo danh mục</h2>
        @if ($categories->isEmpty())
            <p class="mt-6 rounded-2xl border border-dashed border-line px-4 py-10 text-center text-sm text-muted">Chưa có đơn hoàn tất trong khoảng thời gian này.</p>
        @else
            <div class="mt-5 grid gap-3">
                @foreach ($categories as $category)
                    <div class="grid items-center gap-x-4 gap-y-1 sm:grid-cols-[200px_1fr_auto]">
                        <p class="truncate text-sm text-ink">{{ $category['name'] }}</p>
                        <div class="h-3 rounded-r-[4px] bg-surface-2" aria-hidden="true">
                            <div class="h-3 rounded-r-[4px] bg-accent" style="width: {{ max(1, $category['revenue'] / $categoryMax * 100) }}%"></div>
                        </div>
                        <p class="text-sm text-ink sm:text-right">{{ $money($category['revenue']) }} <span class="text-xs text-muted">· {{ $category['orders'] }} đơn</span></p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.admin>
