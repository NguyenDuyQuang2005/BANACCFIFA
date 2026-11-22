<x-layouts.app title="Shop acc FC Online">
    @if ($hero)
        <section class="relative border-b border-line bg-canvas" aria-labelledby="gioi-thieu" data-hero-media>
            <h1 id="gioi-thieu" class="sr-only">{{ config('site.name') }} — Shop acc FC Online</h1>
            @if ($hero['link'])
                <a href="{{ $hero['link'] }}" @if (str_starts_with($hero['link'], 'http')) target="_blank" rel="noopener nofollow" @endif class="block">
            @endif
            @if ($hero['type'] === 'video')
                <video src="{{ $hero['url'] }}" class="block max-h-[78vh] w-full object-cover" autoplay muted loop playsinline preload="auto" aria-label="Banner {{ config('site.name') }}"></video>
            @else
                <img src="{{ $hero['url'] }}" alt="Banner {{ config('site.name') }}" class="block max-h-[78vh] w-full object-cover" fetchpriority="high">
            @endif
            @if ($hero['link'])
                </a>
            @endif
        </section>
    @else
    <section class="pitch-grid relative overflow-hidden border-b border-line" aria-labelledby="gioi-thieu">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 lg:grid-cols-[1.2fr_0.8fr] lg:px-8 lg:py-20">
            <div>
                <p class="inline-flex rounded-full border border-accent/40 bg-surface px-3 py-1 text-xs uppercase tracking-[0.24em] text-accent">{{ config('site.name') }}</p>
                <h1 id="gioi-thieu" class="mt-5 max-w-3xl font-display text-4xl font-semibold leading-tight text-ink md:text-5xl">
                    Nền tảng tài khoản FC Online cho người chơi muốn rõ ràng và chủ động.
                </h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-muted">
                    {{ config('site.description') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('catalog.index') }}" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">Xem danh mục</a>
                    <a href="{{ route('deposit') }}" class="rounded-full border border-accent px-5 py-3 text-sm font-semibold text-accent transition hover:bg-accent hover:text-canvas">Nạp tiền</a>
                    @foreach (['zalo' => 'Zalo', 'telegram' => 'Telegram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok'] as $key => $label)
                        @if (! empty(config('site.social.'.$key)))
                            <a href="{{ config('site.social.'.$key) }}" class="rounded-full border border-line px-5 py-3 text-sm font-medium text-muted hover:text-ink" target="_blank" rel="noopener noreferrer">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
                @if (empty(config('site.social.zalo')) && empty(config('site.social.telegram')) && empty(config('site.social.facebook')) && empty(config('site.social.tiktok')))
                    <p class="mt-4 text-xs text-muted">Liên kết Zalo, Telegram, Facebook và TikTok sẽ hiện khi được cấu hình trong Admin → Cấu hình website.</p>
                @endif
            </div>

            <div class="grid gap-3 self-start rounded-3xl border border-line bg-surface/80 p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-muted">Thống kê hệ thống</p>
                @if ($memberCount !== null || $completedOrderCount !== null)
                    <dl class="grid grid-cols-2 gap-4">
                        @if ($memberCount !== null)
                            <div>
                                <dt class="text-sm text-muted">Thành viên</dt>
                                <dd class="font-display text-3xl font-semibold text-accent">{{ number_format($memberCount) }}</dd>
                            </div>
                        @endif
                        @if ($completedOrderCount !== null)
                            <div>
                                <dt class="text-sm text-muted">Giao dịch hoàn tất</dt>
                                <dd class="font-display text-3xl font-semibold text-ink">{{ number_format($completedOrderCount) }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <x-empty-state title="Chưa có số liệu">
                        Thành viên và giao dịch chỉ được đếm từ database khi module thành viên và đơn hàng đi vào hoạt động.
                    </x-empty-state>
                @endif
            </div>
        </div>
    </section>
    @endif

    @if ($banners->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-10 lg:px-8" aria-label="Banner khuyến mãi">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($banners as $banner)
                    @php
                        $isExternal = $banner->link_url && str_starts_with($banner->link_url, 'http');
                    @endphp
                    <{{ $banner->link_url ? 'a' : 'div' }}
                        @if ($banner->link_url) href="{{ $banner->link_url }}" @if ($isExternal) target="_blank" rel="noopener nofollow" @endif @endif
                        class="group relative block overflow-hidden rounded-3xl border border-line bg-surface transition hover:-translate-y-0.5 hover:border-accent/50"
                        data-banner
                    >
                        @if ($banner->imageUrl())
                            <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" loading="lazy" class="aspect-[16/7] w-full object-cover">
                        @endif
                        <div class="p-5">
                            <p class="font-display text-lg font-semibold text-ink group-hover:text-accent">{{ $banner->title }}</p>
                            @if ($banner->subtitle)
                                <p class="mt-1 text-sm text-muted">{{ $banner->subtitle }}</p>
                            @endif
                        </div>
                    </{{ $banner->link_url ? 'a' : 'div' }}>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-14 lg:px-8" aria-labelledby="giao-dich-gan-day">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-accent">Realtime</p>
                <h2 id="giao-dich-gan-day" class="mt-2 font-display text-2xl font-semibold">Giao dịch gần đây</h2>
            </div>
            <a href="{{ route('transactions.history') }}" class="text-sm text-muted hover:text-accent">Xem lịch sử</a>
        </div>

        <div
            class="grid gap-3"
            data-recent-transactions
            data-endpoint="{{ route('transactions.recent') }}"
        >
            @foreach ($transactions as $transaction)
                <article class="grid grid-cols-2 gap-3 rounded-2xl border border-line bg-surface p-4 md:grid-cols-6 md:items-center">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-muted">Mã</p>
                        <p class="font-medium">{{ $transaction['code'] }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-muted">Tài khoản / sản phẩm</p>
                        <p class="font-medium">{{ $transaction['product_name'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-muted">Khách hàng</p>
                        <p class="font-medium">{{ $transaction['masked_phone'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-muted">Giá trị</p>
                        <p class="font-display font-semibold text-accent">{{ $transaction['amount'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-muted">Trạng thái</p>
                        <p class="text-sm">{{ $transaction['status'] }} · {{ $transaction['completed_at'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        <div @class(['mt-4' => $transactions->isNotEmpty(), 'hidden' => $transactions->isNotEmpty()]) data-recent-empty>
            <x-empty-state title="Chưa có giao dịch hoàn tất">
                Khu vực này chỉ lấy đơn hàng hợp lệ đã hoàn tất từ database. Website không tạo giao dịch giả để minh họa.
            </x-empty-state>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-6 lg:px-8" aria-labelledby="danh-muc-tai-khoan">
        <div class="mb-6">
            <p class="text-xs uppercase tracking-[0.2em] text-accent">Catalog</p>
            <h2 id="danh-muc-tai-khoan" class="mt-2 font-display text-2xl font-semibold">Danh mục tài khoản</h2>
            <p class="mt-2 max-w-2xl text-sm text-muted">Danh mục được đọc từ hệ thống quản trị, có thể thêm, sửa, xóa, sắp xếp và ẩn/hiện. Không hardcode trên giao diện.</p>
        </div>
        @if ($categories->isEmpty())
            <x-empty-state title="Chưa có danh mục" :action-href="route('catalog.index')" action-label="Mở trang danh mục">
                Chạy <code class="text-accent">php artisan db:seed</code> để nạp danh mục mẫu, hoặc thêm danh mục trong Admin → Danh mục.
            </x-empty-state>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <x-category-card :category="$category" />
                @endforeach
            </div>
        @endif
    </section>

    @if ($services->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-14 lg:px-8" aria-labelledby="dich-vu-fco">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-accent">Services</p>
                    <h2 id="dich-vu-fco" class="mt-2 font-display text-2xl font-semibold">Dịch vụ FCO</h2>
                </div>
                <a href="{{ route('services.index') }}" class="text-sm text-muted hover:text-accent">Xem tất cả dịch vụ →</a>
            </div>
            @if (config('site.services.notice'))
                <x-zalo-notice class="mb-6" :text="config('site.services.notice')" :url="config('site.social.zalo')" />
            @endif
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($services as $service)
                    <x-service-card :service="$service" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-14 lg:px-8" aria-labelledby="san-pham-noi-bat">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-accent">Marketplace</p>
                <h2 id="san-pham-noi-bat" class="mt-2 font-display text-2xl font-semibold">Sản phẩm nổi bật</h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-sm text-muted hover:text-accent">Xem tất cả tài khoản →</a>
        </div>

        @if ($products->isEmpty())
            <x-empty-state title="Chưa có sản phẩm" :action-href="route('catalog.index')" action-label="Xem danh mục">
                Sản phẩm nổi bật chỉ hiện tài khoản còn hàng được đánh dấu nổi bật trong database. Website không tạo tài khoản giả.
            </x-empty-state>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <x-pagination />
        @endif
    </section>

    <section class="mx-auto max-w-7xl px-4 py-6 lg:px-8" aria-labelledby="bai-viet-moi">
        <div class="mb-6 flex items-end justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-accent">Nội dung</p>
                <h2 id="bai-viet-moi" class="mt-2 font-display text-2xl font-semibold">Bài viết mới</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="text-sm text-muted hover:text-accent">Tất cả bài viết</a>
        </div>
        @if ($posts->isEmpty())
            <x-empty-state title="Chưa có bài viết" :action-href="route('posts.index')" action-label="Mở mục bài viết">
                Bài viết mới sẽ hiện ảnh đại diện, tiêu đề, mô tả ngắn, ngày đăng, danh mục và nút đọc tiếp khi module nội dung được triển khai.
            </x-empty-state>
        @else
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($posts as $post)
                    <article class="overflow-hidden rounded-3xl border border-line bg-surface">
                        @if (! empty($post['image_url']))
                            <img src="{{ $post['image_url'] }}" alt="{{ $post['title'] }}" class="aspect-video w-full object-cover">
                        @endif
                        <div class="space-y-2 p-5">
                            <p class="text-xs uppercase tracking-widest text-muted">{{ $post['category'] ?? 'Bài viết' }} · {{ $post['published_at'] ?? '' }}</p>
                            <h3 class="font-display text-lg font-semibold">{{ $post['title'] }}</h3>
                            <p class="text-sm text-muted">{{ $post['excerpt'] ?? '' }}</p>
                            <a href="{{ $post['url'] ?? route('posts.index') }}" class="inline-flex text-sm font-semibold text-accent">Đọc tiếp</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 lg:px-8" aria-labelledby="cong-cu-fc-online">
        <div class="mb-6">
            <p class="text-xs uppercase tracking-[0.2em] text-accent">Utilities</p>
            <h2 id="cong-cu-fc-online" class="mt-2 font-display text-2xl font-semibold">Công cụ FC Online</h2>
            <p class="mt-2 max-w-2xl text-sm text-muted">Các module được đăng ký trong cấu hình hệ thống để dễ bổ sung. Công thức và dữ liệu chỉ chạy khi có nguồn đáng tin cậy.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($tools as $tool)
                <a href="{{ route('tools.show', $tool['slug']) }}" class="rounded-3xl border border-line bg-surface p-5 transition hover:border-accent/50">
                    <p class="text-xs uppercase tracking-widest text-muted">{{ ($tool['requires_auth'] ?? false) ? 'Cần đăng nhập' : 'Dùng ngay' }}</p>
                    <h3 class="mt-3 font-display text-lg font-semibold">{{ $tool['name'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-muted">{{ $tool['summary'] }}</p>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.app>
