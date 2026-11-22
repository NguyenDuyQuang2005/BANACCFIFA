<x-layouts.admin title="Dịch vụ FCO">
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-semibold">Dịch vụ FCO</h1>
        <a href="{{ route('admin.services.create') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Thêm dịch vụ</a>
    </div>
    <p class="mt-2 text-sm text-muted">
        Dịch vụ (Bơm BP, ...) hiện ở mục "Dịch vụ FCO" trên trang chủ và trang /dich-vu. Khách bấm "Liên hệ admin" để trao đổi qua Zalo, không thanh toán bằng ví.
        @can('manage-system')
            Dòng thông báo phía trên danh sách sửa ở <a href="{{ route('admin.settings.edit') }}" class="text-accent hover:underline">Cấu hình & SEO</a>.
        @endcan
    </p>
    <div class="mt-8 grid gap-3">
        @forelse ($services as $service)
            <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4">
                <div class="flex items-center gap-4">
                    @if ($service->imageUrl())
                        <img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}" class="h-14 w-28 rounded-xl object-cover">
                    @endif
                    <div>
                        <p class="font-medium">@if ($service->code)<span class="text-accent">{{ $service->code }}</span> · @endif{{ $service->name }}</p>
                        <p class="text-sm text-muted">Thứ tự {{ $service->sort_order }} · {{ $service->is_visible ? 'Đang hiện' : 'Ẩn' }} · {{ $service->contactUrl() ?? 'Chưa có link liên hệ' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.services.edit', $service) }}" class="rounded-full border border-line px-4 py-2 text-xs">Sửa</a>
                    <form method="post" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Xóa dịch vụ này?')">
                        @csrf
                        @method('delete')
                        <button class="rounded-full border border-line px-4 py-2 text-xs text-primary">Xóa</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="text-sm text-muted">Chưa có dịch vụ.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$services" />
</x-layouts.admin>
