<x-layouts.app :title="$tool['name']" :meta-description="$tool['summary']">
    @include('tools._header')

    <div class="mx-auto max-w-6xl px-4 py-8 lg:px-8">
        @include('tools._player-filters')

        @if ($compared->count() === 2)
            @php [$left, $right] = [$compared->firstWhere('id', $compareIds[0]), $compared->firstWhere('id', $compareIds[1])]; @endphp
            <section class="mt-6 rounded-3xl border border-accent/40 bg-surface p-5" aria-label="So sánh cầu thủ">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-semibold">So sánh 2 cầu thủ</h2>
                    <a href="{{ request()->fullUrlWithoutQuery(['compare']) }}" class="text-xs text-muted hover:text-primary">Bỏ so sánh</a>
                </div>
                <table class="mt-4 w-full text-sm">
                    <thead>
                        <tr>
                            <th class="w-1/3 py-2 text-left">@include('tools._player-identity', ['player' => $left])</th>
                            <th class="w-1/3 py-2 text-center text-xs uppercase tracking-widest text-muted">Chỉ số</th>
                            <th class="w-1/3 py-2">@include('tools._player-identity', ['player' => $right])</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (['position' => 'Vị trí', 'ovr' => 'OVR', 'pace' => 'Tốc độ', 'stamina' => 'Thể lực', 'stars' => 'Sao kỹ năng'] as $field => $label)
                            @php
                                $leftValue = $left->{$field};
                                $rightValue = $right->{$field};
                                $numeric = is_int($leftValue) && is_int($rightValue);
                            @endphp
                            <tr class="border-t border-line">
                                <td class="py-2 font-semibold {{ $numeric && $leftValue > $rightValue ? 'text-emerald-400' : '' }}">{{ $leftValue ?? '—' }}</td>
                                <td class="py-2 text-center text-muted">{{ $label }}</td>
                                <td class="py-2 text-right font-semibold {{ $numeric && $rightValue > $leftValue ? 'text-emerald-400' : '' }}">{{ $rightValue ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @elseif (count($compareIds) === 1)
            <x-alert class="mt-6">Đã chọn 1 cầu thủ. Bấm "So sánh" ở một cầu thủ khác để so sánh.</x-alert>
        @endif

        @if ($players->isEmpty())
            <x-empty-state class="mt-6" title="Chưa có dữ liệu cầu thủ">Không có cầu thủ phù hợp, hoặc dữ liệu chưa được nhập trong trang quản trị.</x-empty-state>
        @else
            <div class="mt-6 overflow-x-auto rounded-3xl border border-line bg-surface">
                <table class="w-full min-w-[640px] text-sm">
                    <thead class="bg-surface-2 text-xs uppercase tracking-widest text-muted">
                        <tr>
                            <th class="px-4 py-3 text-left">OVR</th>
                            <th class="px-4 py-3 text-left">Cầu thủ</th>
                            <th class="px-4 py-3">Tốc độ</th>
                            <th class="px-4 py-3">Thể lực</th>
                            <th class="px-4 py-3">So sánh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($players as $player)
                            @php
                                $isCompared = in_array($player->id, $compareIds, true);
                                $nextCompare = $isCompared
                                    ? array_values(array_diff($compareIds, [$player->id]))
                                    : array_slice([...$compareIds, $player->id], -2);
                            @endphp
                            <tr class="border-t border-line {{ $isCompared ? 'bg-accent/5' : '' }}">
                                <td class="whitespace-nowrap px-4 py-3"><span class="text-[11px] text-muted">{{ $player->position }}</span> <strong class="font-display text-base">{{ $player->ovr }}</strong></td>
                                <td class="px-4 py-3"><a href="{{ route('tools.player', $player) }}" class="hover:text-accent">@include('tools._player-identity')</a></td>
                                <td class="px-4 py-3 text-center">{{ $player->pace ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $player->stamina ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['compare' => $nextCompare]) }}" class="rounded-full border px-3 py-1 text-xs {{ $isCompared ? 'border-accent text-accent' : 'border-line text-muted hover:text-ink' }}">{{ $isCompared ? 'Đã chọn' : 'So sánh' }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-pagination :paginator="$players" />
        @endif
    </div>
</x-layouts.app>
