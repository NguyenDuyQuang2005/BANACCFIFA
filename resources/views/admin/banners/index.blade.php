<x-layouts.admin title="Banner">
    <section class="rounded-3xl border border-accent/40 bg-surface p-6">
        <h1 class="font-display text-2xl font-semibold">Banner đầu trang chủ</h1>
        <p class="mt-1 text-sm text-muted">Ảnh hoặc video thay cho phần giới thiệu ở đầu trang chủ, hiển thị toàn chiều ngang, không kèm chữ. Video tự chạy, tắt tiếng, lặp lại. Gỡ đi thì trang chủ quay về phần giới thiệu dạng chữ.</p>

        @if ($hero)
            <div class="mt-4 overflow-hidden rounded-2xl border border-line bg-canvas">
                @if ($hero['type'] === 'video')
                    <video src="{{ $hero['url'] }}" class="max-h-72 w-full object-cover" autoplay muted loop playsinline></video>
                @else
                    <img src="{{ $hero['url'] }}" alt="Banner đầu trang hiện tại" class="max-h-72 w-full object-cover">
                @endif
            </div>
        @else
            <p class="mt-4 rounded-2xl border border-dashed border-line px-4 py-6 text-center text-sm text-muted">Chưa có banner đầu trang. Trang chủ đang dùng phần giới thiệu dạng chữ.</p>
        @endif

        <form method="post" action="{{ route('admin.hero.update') }}" enctype="multipart/form-data" class="mt-4 grid gap-3">
            @csrf
            @method('put')
            <label class="grid gap-2 text-sm">
                <span class="font-medium">{{ $hero ? 'Thay bằng ảnh/video mới' : 'Chọn ảnh hoặc video' }} (jpg, png, webp, gif, mp4, webm · tối đa 20MB · nên dùng tỉ lệ ngang ~ 21:9 hoặc 16:9)</span>
                <input type="file" name="hero_media" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm" class="text-sm">
            </label>
            @error('hero_media') <p class="text-sm text-primary">{{ $message }}</p> @enderror
            <x-form-input name="hero_link_url" label="Link khi bấm vào banner (tuỳ chọn, https://... hoặc /duong-dan)" :value="old('hero_link_url', $heroLink)" />
            @error('hero_link_url') <p class="text-sm text-primary">{{ $message }}</p> @enderror
            <button class="justify-self-start rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white">Lưu banner đầu trang</button>
        </form>

        @if ($hero)
            <form method="post" action="{{ route('admin.hero.destroy') }}" class="mt-3" onsubmit="return confirm('Gỡ banner đầu trang?')">
                @csrf
                @method('delete')
                <button class="text-sm text-muted hover:text-primary">Gỡ banner đầu trang</button>
            </form>
        @endif
    </section>

    <div class="mt-10 flex items-center justify-between gap-4">
        <h2 class="font-display text-2xl font-semibold">Banner nhỏ dưới đầu trang</h2>
        <a href="{{ route('admin.banners.create') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Thêm banner</a>
    </div>
    <p class="mt-2 text-sm text-muted">Banner đang bật và trong thời gian hiển thị sẽ xuất hiện dưới khu vực giới thiệu ở trang chủ.</p>
    <div class="mt-8 grid gap-3">
        @forelse ($banners as $banner)
            <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4">
                <div class="flex items-center gap-4">
                    @if ($banner->imageUrl())
                        <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="h-14 w-24 rounded-xl object-cover">
                    @endif
                    <div>
                        <p class="font-medium">{{ $banner->title }}</p>
                        <p class="text-sm text-muted">
                            Thứ tự {{ $banner->sort_order }} · {{ $banner->is_active ? 'Đang bật' : 'Tắt' }}
                            @if ($banner->starts_at || $banner->ends_at)
                                · {{ $banner->starts_at?->format('d/m/Y H:i') ?? '…' }} → {{ $banner->ends_at?->format('d/m/Y H:i') ?? '…' }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.banners.edit', $banner) }}" class="rounded-full border border-line px-4 py-2 text-xs">Sửa</a>
                    <form method="post" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Xóa banner này?')">
                        @csrf
                        @method('delete')
                        <button class="rounded-full border border-line px-4 py-2 text-xs text-primary">Xóa</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="text-sm text-muted">Chưa có banner.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$banners" />
</x-layouts.admin>
