<x-layouts.app :title="$tool['name']" :meta-description="$tool['summary']">
    @include('tools._header')

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8" data-skill-lookup>
        <div class="flex flex-col gap-3 rounded-3xl border border-line bg-surface p-4 md:flex-row md:items-center">
            <input type="search" placeholder="Tìm kỹ thuật..." class="min-w-0 flex-1 rounded-xl border border-line bg-canvas px-4 py-3 text-sm" data-skill-search aria-label="Tìm kỹ thuật">
            <select class="rounded-xl border border-line bg-canvas px-4 py-3 text-sm" data-skill-level aria-label="Lọc theo cấp">
                <option value="">Tất cả cấp</option>
                @foreach ($levels as $level)
                    <option value="{{ $level }}">Cấp {{ $level }}</option>
                @endforeach
            </select>
            <div class="flex gap-2" role="radiogroup" aria-label="Kiểu điều khiển">
                <button type="button" role="radio" aria-checked="true" class="rounded-full border border-primary bg-primary/10 px-4 py-2 text-sm font-semibold text-primary" data-skill-mode="keyboard">Bàn phím</button>
                <button type="button" role="radio" aria-checked="false" class="rounded-full border border-line px-4 py-2 text-sm font-semibold text-muted" data-skill-mode="controller">Tay cầm</button>
            </div>
        </div>

        @if ($skills->isEmpty())
            <x-empty-state class="mt-6" title="Chưa có kỹ thuật">Kỹ thuật sẽ hiện ở đây khi được thêm trong trang quản trị.</x-empty-state>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($skills as $skill)
                    <article class="overflow-hidden rounded-3xl border border-line bg-surface" data-skill-card data-name="{{ mb_strtolower($skill->name.' '.$skill->description) }}" data-level="{{ $skill->level }}">
                        @if ($skill->mediaUrl())
                            @if ($skill->isVideo())
                                <video src="{{ $skill->mediaUrl() }}" class="aspect-video w-full bg-black object-contain" autoplay muted loop playsinline preload="metadata"></video>
                            @else
                                <img src="{{ $skill->mediaUrl() }}" alt="Minh họa {{ $skill->name }}" loading="lazy" class="aspect-video w-full bg-black object-contain">
                            @endif
                        @endif
                        <div class="p-5">
                            <span class="rounded-md bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">Cấp {{ $skill->level }}</span>
                            <h2 class="mt-2 font-display text-lg font-semibold">{{ $skill->name }}</h2>
                            @if ($skill->description)
                                <p class="mt-1 text-sm text-muted">{{ $skill->description }}</p>
                            @endif
                            <p class="mt-2 text-sm" data-skill-keys="keyboard"><strong>Phím:</strong> {{ $skill->keyboard_keys ?: 'Chưa cập nhật' }}</p>
                            <p class="mt-2 hidden text-sm" data-skill-keys="controller"><strong>Tay cầm:</strong> {{ $skill->controller_keys ?: 'Chưa cập nhật' }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
            <p class="mt-6 hidden text-center text-sm text-muted" data-skill-empty>Không có kỹ thuật phù hợp.</p>
        @endif
    </div>
</x-layouts.app>
