<x-layouts.app :title="$player->name.' '.($player->season?->name ?? '').' · OVR '.$player->ovr" :meta-description="'Chỉ số '.$player->name.': OVR '.$player->ovr.', vị trí '.$player->position.', giờ reset giá trên TTCN.'">
    <div class="mx-auto max-w-3xl px-4 py-10 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Dữ liệu cầu thủ', 'url' => route('tools.show', 'du-lieu-cau-thu')],
            ['label' => $player->name],
        ]" />

        <section class="mt-6 rounded-3xl border border-line bg-surface p-6">
            <div class="flex flex-wrap items-center gap-5">
                @if ($player->imageUrl())
                    <img src="{{ $player->imageUrl() }}" alt="{{ $player->name }}" class="h-24 w-24 rounded-2xl bg-surface-2 object-cover">
                @endif
                <div>
                    <h1 class="font-display text-3xl font-semibold">{{ $player->name }}</h1>
                    <p class="mt-1 text-sm text-muted">{{ $player->season?->name ?? 'Không rõ mùa thẻ' }} · {{ $player->position }}</p>
                    @if ($player->stars)
                        <p class="mt-1 text-accent" aria-label="{{ $player->stars }} sao">{{ str_repeat('★', $player->stars) }}</p>
                    @endif
                </div>
            </div>

            <dl class="mt-6 grid grid-cols-3 gap-3 text-center">
                @foreach (['OVR' => $player->ovr, 'Tốc độ' => $player->pace, 'Thể lực' => $player->stamina] as $label => $value)
                    <div class="rounded-2xl border border-line bg-canvas p-4">
                        <dt class="text-xs text-muted">{{ $label }}</dt>
                        <dd class="mt-1 font-display text-2xl font-semibold">{{ $value ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-6 rounded-2xl border border-primary/30 bg-primary/10 p-4 text-sm">
                <p class="font-semibold">Giờ reset giá: {{ $player->resetLabel() ?? 'Chưa có dữ liệu' }}</p>
                @if ($nextReset)
                    <p class="mt-1 text-muted">Lần reset tiếp theo: <strong class="text-ink">{{ $nextReset->format('H:i') }}</strong> <span data-reset-countdown="{{ $nextReset->getTimestamp() }}"></span></p>
                @endif
            </div>
        </section>

        <div class="mt-6 flex flex-wrap gap-3 text-sm">
            <a href="{{ route('tools.show', 'du-lieu-cau-thu') }}" class="rounded-full border border-line px-4 py-2 hover:border-accent">← Dữ liệu cầu thủ</a>
            <a href="{{ route('tools.show', 'gio-reset') }}" class="rounded-full border border-line px-4 py-2 hover:border-accent">Giờ reset cầu thủ</a>
        </div>
    </div>
</x-layouts.app>
