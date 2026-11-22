<x-layouts.app
    :title="$currentCategory?->name ?? 'Danh mục tài khoản FC Online'"
    meta-description="Danh sách tài khoản FC Online trên DINHTIENFCO. Lọc theo danh mục, giá, BP, OVR và tình trạng."
>
    <div class="mx-auto max-w-7xl px-4 py-10 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Danh mục tài khoản', 'url' => $currentCategory ? route('catalog.index') : null],
            ...($currentCategory ? [['label' => $currentCategory->name]] : []),
        ]" />

        <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="font-display text-3xl font-semibold">{{ $currentCategory?->name ?? 'Danh mục tài khoản' }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted">
                    {{ $currentCategory?->description ?? 'Bộ lọc bên trái trên desktop, dạng ngăn kéo trên mobile. Kết quả lấy từ database.' }}
                </p>
            </div>
            <button type="button" class="inline-flex rounded-full border border-line px-4 py-2 text-sm lg:hidden" data-filter-open>
                Mở bộ lọc
            </button>
        </div>

        @if ($currentCategory?->notice)
            @php
                $noticeUrl = $currentCategory->notice_url ?: config('site.social.zalo');
            @endphp
            <div class="mt-6 rounded-2xl border border-primary/30 bg-primary/10 px-4 py-3 text-sm" data-category-notice>
                @if ($noticeUrl)
                    <a href="{{ $noticeUrl }}" target="_blank" rel="noopener nofollow" class="flex flex-wrap items-center gap-3 hover:opacity-90">
                        <span aria-hidden="true">📢</span>
                        <span class="font-medium">{{ $currentCategory->notice }}</span>
                        <span class="ml-auto rounded-full bg-primary px-3 py-1 text-xs font-semibold text-white">Nhắn Zalo →</span>
                    </a>
                @else
                    <p class="flex items-center gap-3">
                        <span aria-hidden="true">📢</span>
                        <span class="font-medium">{{ $currentCategory->notice }}</span>
                    </p>
                @endif
            </div>
        @endif

        <div class="mt-8 grid gap-8 lg:grid-cols-[280px_1fr]">
            <aside class="hidden lg:block">
                @include('catalog.partials.filters')
            </aside>

            <div class="fixed inset-0 z-50 hidden bg-canvas/80 p-4 lg:hidden" data-filter-drawer>
                <div class="ml-auto h-full max-w-sm overflow-y-auto rounded-3xl border border-line bg-surface p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="font-display font-semibold">Bộ lọc</p>
                        <button type="button" class="rounded-lg border border-line px-3 py-1 text-sm" data-filter-close>Đóng</button>
                    </div>
                    @include('catalog.partials.filters')
                </div>
            </div>

            <div>
                @if ($products->isEmpty())
                    <x-empty-state title="Chưa có tài khoản để hiển thị">
                        Không có sản phẩm khớp bộ lọc, hoặc chưa có tài khoản được đăng bán.
                    </x-empty-state>
                @else
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                    <x-pagination :paginator="$products" />
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
