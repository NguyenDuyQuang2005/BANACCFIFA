@php
    $baseQuery = array_filter(['q' => $filters['q'] ?: null, 'position' => $filters['position'] ?? null]);
@endphp
<form method="get" class="rounded-3xl border border-line bg-surface p-4">
    <div class="flex flex-col gap-2 sm:flex-row">
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Tên cầu thủ (VD: Ronaldo, Messi)" class="min-w-0 flex-1 rounded-xl border border-line bg-canvas px-4 py-3 text-sm" aria-label="Tên cầu thủ">
        @if ($filters['season'])<input type="hidden" name="season" value="{{ $filters['season'] }}">@endif
        @if (! empty($filters['position']))<input type="hidden" name="position" value="{{ $filters['position'] }}">@endif
        <button class="rounded-xl bg-primary px-6 py-3 text-sm font-bold uppercase text-white">Tìm kiếm</button>
        @if ($filters['q'] !== '' || $filters['season'] || ! empty($filters['position']))
            <a href="{{ url()->current() }}" class="self-center px-3 text-sm text-muted hover:text-ink">Bỏ lọc</a>
        @endif
    </div>

    @if ($seasons->isNotEmpty())
        <p class="mt-3 text-xs text-muted">Lọc theo mùa thẻ</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <a href="{{ url()->current().'?'.http_build_query($baseQuery) }}" class="rounded-lg border px-3 py-1.5 text-xs {{ ! $filters['season'] ? 'border-primary text-primary' : 'border-line text-muted' }}">Tất cả</a>
            @foreach ($seasons as $season)
                <a href="{{ url()->current().'?'.http_build_query([...$baseQuery, 'season' => $season->id]) }}" title="{{ $season->name }}" class="flex items-center gap-1 rounded-lg border px-2 py-1 text-xs {{ $filters['season'] === $season->id ? 'border-primary text-primary' : 'border-line text-muted' }}">
                    @if ($season->iconUrl())<img src="{{ $season->iconUrl() }}" alt="{{ $season->name }}" class="h-5 w-auto">@else{{ $season->code }}@endif
                </a>
            @endforeach
        </div>
    @endif

    @isset($positions)
        <p class="mt-3 text-xs text-muted">Vị trí</p>
        <div class="mt-2 flex flex-wrap gap-1.5">
            @foreach ($positions as $position)
                @php $positionQuery = array_filter([...$baseQuery, 'season' => $filters['season'], 'position' => $filters['position'] === $position ? null : $position]); @endphp
                <a href="{{ url()->current().'?'.http_build_query($positionQuery) }}" class="rounded-md border px-2 py-1 text-[11px] font-semibold {{ $filters['position'] === $position ? 'border-primary bg-primary/10 text-primary' : 'border-line text-muted' }}">{{ $position }}</a>
            @endforeach
        </div>
    @endisset
</form>
