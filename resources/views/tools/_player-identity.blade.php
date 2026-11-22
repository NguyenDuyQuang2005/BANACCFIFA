<div class="flex items-center gap-3">
    @if ($player->imageUrl())
        <img src="{{ $player->imageUrl() }}" alt="{{ $player->name }}" loading="lazy" class="h-10 w-10 rounded-full bg-surface-2 object-cover">
    @else
        <span class="grid h-10 w-10 place-items-center rounded-full bg-surface-2 text-xs text-muted" aria-hidden="true">{{ mb_substr($player->name, 0, 1) }}</span>
    @endif
    <div>
        <p class="flex items-center gap-1.5 font-semibold">
            @if ($player->season?->iconUrl())
                <img src="{{ $player->season->iconUrl() }}" alt="{{ $player->season->name }}" title="{{ $player->season->name }}" class="h-4 w-auto">
            @elseif ($player->season)
                <span class="rounded bg-surface-2 px-1 text-[10px] text-muted">{{ $player->season->code }}</span>
            @endif
            {{ $player->name }}
        </p>
        @if ($player->stars)
            <p class="text-xs text-accent" aria-label="{{ $player->stars }} sao">{{ str_repeat('★', $player->stars) }}<span class="text-muted">{{ str_repeat('★', max(0, 5 - $player->stars)) }}</span></p>
        @endif
    </div>
</div>
