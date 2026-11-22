@props([
    /** @var list<array{label: string, tooltip: string, value: float, detail?: string}> */
    'items' => [],
    'title',
    'valueSuffix' => '₫',
])

@php
    $items = collect($items)->values();
    $rawMax = (float) $items->max('value');

    // Round the axis up to a "nice" number (1, 2, 2.5, 5 × 10^n) so gridline labels are readable.
    $niceMax = 0.0;
    if ($rawMax > 0) {
        $magnitude = 10 ** floor(log10($rawMax));
        foreach ([1, 2, 2.5, 5, 10] as $step) {
            if ($step * $magnitude >= $rawMax) {
                $niceMax = $step * $magnitude;
                break;
            }
        }
    }

    $short = function (float $value) use ($valueSuffix): string {
        if ($value >= 1_000_000_000) {
            return rtrim(rtrim(number_format($value / 1_000_000_000, 1, ',', '.'), '0'), ',').' tỷ';
        }
        if ($value >= 1_000_000) {
            return rtrim(rtrim(number_format($value / 1_000_000, 1, ',', '.'), '0'), ',').'tr';
        }
        if ($value >= 1_000) {
            return rtrim(rtrim(number_format($value / 1_000, 1, ',', '.'), '0'), ',').'k';
        }

        return number_format($value, 0, ',', '.').$valueSuffix;
    };

    $count = max($items->count(), 1);
    $labelEvery = (int) max(1, ceil($count / 12));
    $gridLines = [1, 0.75, 0.5, 0.25];
@endphp

<figure {{ $attributes->merge(['class' => 'rounded-3xl border border-line bg-surface p-5']) }} data-chart>
    <figcaption class="font-display text-lg font-semibold text-ink">{{ $title }}</figcaption>

    @if ($niceMax <= 0)
        <p class="mt-6 rounded-2xl border border-dashed border-line px-4 py-10 text-center text-sm text-muted">Chưa có doanh thu trong khoảng thời gian này.</p>
    @else
        <div class="relative mt-6 grid grid-cols-[44px_1fr] gap-2">
            {{-- Y axis labels --}}
            <div class="relative h-56 text-right text-[11px] text-muted" aria-hidden="true">
                @foreach ($gridLines as $line)
                    <span class="absolute right-0 -translate-y-1/2" style="top: {{ (1 - $line) * 100 }}%">{{ $short($niceMax * $line) }}</span>
                @endforeach
                <span class="absolute bottom-0 right-0 translate-y-1/2">0</span>
            </div>

            <div class="relative">
                {{-- Recessive gridlines --}}
                <div class="pointer-events-none absolute inset-x-0 top-0 h-56" aria-hidden="true">
                    @foreach ($gridLines as $line)
                        <div class="absolute inset-x-0 border-t border-line/50" style="top: {{ (1 - $line) * 100 }}%"></div>
                    @endforeach
                    <div class="absolute inset-x-0 bottom-0 border-t border-line"></div>
                </div>

                {{-- Bars: the whole column is the hover/focus target, the bar inside is the mark --}}
                <div class="relative flex h-56 items-end gap-[2px]" role="list" aria-label="{{ $title }}">
                    @foreach ($items as $item)
                        @php
                            $height = $item['value'] > 0 ? max(1.5, $item['value'] / $niceMax * 100) : 0;
                            $tooltip = $item['tooltip'].' · '.number_format($item['value'], 0, ',', '.').$valueSuffix.(isset($item['detail']) ? ' · '.$item['detail'] : '');
                        @endphp
                        <div
                            class="group flex h-full min-w-0 flex-1 cursor-default items-end rounded-t outline-none focus-visible:bg-surface-2"
                            role="listitem"
                            tabindex="0"
                            data-chart-tooltip="{{ $tooltip }}"
                            aria-label="{{ $tooltip }}"
                        >
                            <div class="w-full rounded-t-[4px] bg-accent transition group-hover:bg-primary group-focus-visible:bg-primary" style="height: {{ $height }}%"></div>
                        </div>
                    @endforeach
                </div>

                {{-- X axis labels --}}
                <div class="mt-2 flex gap-[2px] text-[11px] text-muted" aria-hidden="true">
                    @foreach ($items as $index => $item)
                        <span class="min-w-0 flex-1 overflow-visible whitespace-nowrap text-center">{{ $index % $labelEvery === 0 ? $item['label'] : '' }}</span>
                    @endforeach
                </div>

                <div class="pointer-events-none absolute z-10 hidden whitespace-nowrap rounded-lg border border-line bg-canvas px-3 py-2 text-xs text-ink shadow-xl" data-chart-tooltip-box></div>
            </div>
        </div>

        <details class="mt-4 text-sm">
            <summary class="cursor-pointer text-xs text-muted hover:text-ink">Xem dạng bảng</summary>
            <div class="mt-3 max-h-64 overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-muted"><tr><th class="py-1">Thời gian</th><th class="py-1 text-right">Doanh thu</th><th class="py-1 text-right">Chi tiết</th></tr></thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr class="border-t border-line">
                                <td class="py-1">{{ $item['tooltip'] }}</td>
                                <td class="py-1 text-right">{{ number_format($item['value'], 0, ',', '.') }}{{ $valueSuffix }}</td>
                                <td class="py-1 text-right text-muted">{{ $item['detail'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @endif
</figure>
