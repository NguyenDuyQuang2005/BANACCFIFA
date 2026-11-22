<x-layouts.app :title="$tool['name']" :meta-description="$tool['summary']">
    @include('tools._header')

    <div class="mx-auto max-w-6xl px-4 py-8 lg:px-8">
        <section class="inline-block rounded-3xl border border-line bg-surface px-6 py-5" data-reset-clock aria-live="polite">
            <p class="text-sm text-muted">Giờ hiện tại (giờ Việt Nam)</p>
            <p class="mt-1 font-display text-3xl font-semibold text-primary" data-reset-clock-time>--:--:--</p>
            <p class="mt-1 text-xs text-muted" data-reset-clock-note>Đang tải…</p>
        </section>

        <div class="mt-6">
            @include('tools._player-filters')
        </div>

        <p class="mt-4 text-xs text-muted">Giờ reset: phút trong giờ lẻ / giờ chẵn mà giá cầu thủ được làm mới trên TTCN. VD "Lẻ 55 - Chẵn 25" là 1:55, 2:25, 3:55, 4:25…</p>

        @if ($players->isEmpty())
            <x-empty-state class="mt-6" title="Chưa có dữ liệu giờ reset">Không có cầu thủ phù hợp, hoặc dữ liệu chưa được nhập trong trang quản trị.</x-empty-state>
        @else
            <div class="mt-4 overflow-x-auto rounded-3xl border border-line bg-surface">
                <table class="w-full min-w-[640px] text-sm">
                    <thead class="bg-surface-2 text-xs uppercase tracking-widest text-muted">
                        <tr>
                            <th class="px-4 py-3 text-left">OVR</th>
                            <th class="px-4 py-3 text-left">Cầu thủ</th>
                            <th class="px-4 py-3">Giờ reset</th>
                            <th class="px-4 py-3">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($players as $player)
                            @php $nextReset = $player->nextResetAt(); @endphp
                            <tr class="border-t border-line">
                                <td class="whitespace-nowrap px-4 py-3"><span class="text-[11px] text-muted">{{ $player->position }}</span> <strong class="font-display text-base">{{ $player->ovr }}</strong></td>
                                <td class="px-4 py-3">@include('tools._player-identity')</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($player->resetLabel())
                                        <span class="font-semibold">{{ $player->resetLabel() }}</span>
                                        @if ($nextReset)
                                            <span class="block text-xs text-muted" data-reset-countdown="{{ $nextReset->getTimestamp() }}">Lần tới {{ $nextReset->format('H:i') }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center"><a href="{{ route('tools.player', $player) }}" class="text-primary hover:underline">Xem →</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-pagination :paginator="$players" />
        @endif
    </div>
</x-layouts.app>
