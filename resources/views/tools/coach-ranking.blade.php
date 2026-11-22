<x-layouts.app :title="$tool['name']" :meta-description="$tool['summary']">
    @include('tools._header')

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        @if ($coachSkills->isEmpty())
            <x-empty-state title="Chưa có dữ liệu ranking">Bảng xếp hạng kỹ năng HLV sẽ hiện khi được cập nhật trong trang quản trị.</x-empty-state>
        @else
            <p class="mb-4 text-xs text-muted">Cập nhật lần cuối: {{ $coachSkills->max('updated_at')?->format('d/m/Y H:i') }}</p>
            <ol class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($coachSkills as $index => $skill)
                    <li class="relative flex flex-col items-center rounded-2xl border border-line bg-surface p-4 text-center transition hover:border-accent/50">
                        <span class="absolute left-3 top-2 text-xs font-bold text-primary">#{{ $index + 1 }}</span>
                        @if ($skill->iconUrl())
                            <img src="{{ $skill->iconUrl() }}" alt="" class="mt-3 h-12 w-12 object-contain" loading="lazy">
                        @else
                            <span class="mt-3 grid h-12 w-12 place-items-center rounded-xl bg-surface-2 font-display text-lg text-muted" aria-hidden="true">{{ mb_substr($skill->name, 0, 1) }}</span>
                        @endif
                        <p class="mt-3 text-sm font-semibold leading-snug">{{ $skill->name }}</p>
                        <p class="mt-1 font-display text-lg font-semibold text-emerald-400">{{ number_format((float) $skill->usage_percent, 2) }}%</p>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</x-layouts.app>
