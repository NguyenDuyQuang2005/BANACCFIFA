<x-layouts.admin title="Kỹ thuật">
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-semibold">Tra cứu kỹ thuật</h1>
        <a href="{{ route('admin.tool-skills.create') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Thêm kỹ thuật</a>
    </div>
    <p class="mt-2 text-sm text-muted">Hiển thị ở <a href="{{ route('tools.show', 'ky-thuat') }}" class="text-accent hover:underline" target="_blank">/cong-cu/ky-thuat</a>. Mỗi kỹ thuật có cấp, phím bàn phím / tay cầm và ảnh GIF hoặc video minh họa (tự quay hoặc có quyền sử dụng).</p>
    <div class="mt-8 grid gap-3">
        @forelse ($skills as $skill)
            <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4">
                <div class="flex items-center gap-4">
                    @if ($skill->mediaUrl() && ! $skill->isVideo())
                        <img src="{{ $skill->mediaUrl() }}" alt="" class="h-14 w-24 rounded-xl bg-black object-contain">
                    @elseif ($skill->isVideo())
                        <span class="grid h-14 w-24 place-items-center rounded-xl bg-black text-xs text-muted">Video</span>
                    @endif
                    <div>
                        <p class="font-medium"><span class="text-primary">Cấp {{ $skill->level }}</span> · {{ $skill->name }}</p>
                        <p class="text-sm text-muted">Phím: {{ $skill->keyboard_keys ?: '—' }} · Tay cầm: {{ $skill->controller_keys ?: '—' }} · {{ $skill->is_visible ? 'Hiện' : 'Ẩn' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.tool-skills.edit', $skill) }}" class="rounded-full border border-line px-4 py-2 text-xs">Sửa</a>
                    <form method="post" action="{{ route('admin.tool-skills.destroy', $skill) }}" onsubmit="return confirm('Xóa kỹ thuật này?')">
                        @csrf
                        @method('delete')
                        <button class="rounded-full border border-line px-4 py-2 text-xs text-primary">Xóa</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="text-sm text-muted">Chưa có kỹ thuật.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$skills" />
</x-layouts.admin>
