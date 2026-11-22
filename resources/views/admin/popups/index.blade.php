<x-layouts.admin title="Popup">
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-semibold">Popup thông báo</h1>
        <a href="{{ route('admin.popups.create') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Thêm popup</a>
    </div>
    <p class="mt-2 text-sm text-muted">Mỗi lần vào hoặc tải lại trang, khách sẽ thấy 1 popup đang bật có ưu tiên cao nhất. Khi khách bấm đóng, popup ẩn trong số phút đã cài.</p>
    <div class="mt-8 grid gap-3">
        @forelse ($popups as $popup)
            <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4">
                <div>
                    <p class="flex flex-wrap items-center gap-2 font-medium">
                        {{ $popup->title }}
                        @if ($popup->id === $livePopupId)
                            <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-xs text-emerald-400">Đang hiển thị</span>
                        @endif
                    </p>
                    <p class="text-sm text-muted">
                        {{ ['none' => 'Chỉ chữ', 'image' => 'Có ảnh', 'video' => 'Có video'][$popup->media_type] ?? '' }}
                        · Ẩn {{ $popup->dismiss_minutes }} phút sau khi đóng
                        · Ưu tiên {{ $popup->sort_order }}
                        · {{ $popup->is_active ? 'Bật' : 'Tắt' }}
                        @if ($popup->starts_at || $popup->ends_at)
                            · {{ $popup->starts_at?->format('d/m/Y H:i') ?? '…' }} → {{ $popup->ends_at?->format('d/m/Y H:i') ?? '…' }}
                        @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.popups.edit', $popup) }}" class="rounded-full border border-line px-4 py-2 text-xs">Sửa</a>
                    <form method="post" action="{{ route('admin.popups.destroy', $popup) }}" onsubmit="return confirm('Xóa popup này?')">
                        @csrf
                        @method('delete')
                        <button class="rounded-full border border-line px-4 py-2 text-xs text-primary">Xóa</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="text-sm text-muted">Chưa có popup.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$popups" />
</x-layouts.admin>
