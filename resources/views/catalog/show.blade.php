<x-layouts.app :title="$product->name" :meta-description="Str::limit(strip_tags($product->description ?? $product->name), 150)">
    <div class="mx-auto max-w-7xl px-4 py-10 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Danh mục tài khoản', 'url' => route('catalog.index')],
            ['label' => $product->category->name, 'url' => route('catalog.category', $product->category)],
            ['label' => $product->code],
        ]" />

        <div class="mt-8 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-4">
                <div class="relative aspect-video overflow-hidden rounded-3xl border border-line bg-surface-2">
                    @if ($product->coverUrl())
                        <img src="{{ $product->coverUrl() }}" alt="{{ $product->name }}" class="absolute inset-0 h-full w-full object-cover" data-gallery-main>
                    @else
                        <div class="absolute inset-0 grid place-items-center text-muted">Chưa có hình chi tiết</div>
                    @endif
                </div>
                @if ($product->images->count() > 1)
                    <div class="grid grid-cols-4 gap-3 sm:grid-cols-5">
                        @foreach ($product->images as $image)
                            <button type="button" class="relative aspect-[4/3] overflow-hidden rounded-2xl border {{ $image->is_primary ? 'border-accent' : 'border-line' }} hover:border-accent" data-gallery-thumb="{{ $image->url() }}" aria-label="Xem ảnh {{ $loop->iteration }}">
                                <img src="{{ $image->url() }}" alt="{{ $image->alt ?? $product->name }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-line bg-surface p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-muted">{{ $product->code }}</p>
                <h1 class="mt-2 font-display text-3xl font-semibold">{{ $product->name }}</h1>
                <p class="mt-3 text-sm text-accent">{{ $product->status->label() }}</p>
                @if ($product->formattedOriginalPrice())
                    <p class="mt-4 flex items-center gap-2 text-sm">
                        <span class="text-muted line-through">{{ $product->formattedOriginalPrice() }}</span>
                        @if ($product->discountPercent())
                            <span class="rounded-full bg-primary px-2.5 py-0.5 text-xs font-bold text-white">-{{ $product->discountPercent() }}%</span>
                        @endif
                    </p>
                @endif
                <p class="font-display text-4xl font-semibold text-accent">{{ $product->formattedPrice() }}</p>

                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-muted">BP</dt>
                        <dd class="text-ink">{{ $product->formattedBp() }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Đội hình</dt>
                        <dd class="text-ink">{{ $product->squad ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">OVR</dt>
                        <dd class="text-ink">{{ $product->ovr ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Danh mục</dt>
                        <dd><a href="{{ route('catalog.category', $product->category) }}" class="text-accent hover:underline">{{ $product->category->name }}</a></dd>
                    </div>
                </dl>

                @if (! empty($product->featured_players))
                    <div class="mt-6">
                        <h2 class="font-display text-lg font-semibold">Cầu thủ nổi bật</h2>
                        <ul class="mt-2 grid gap-1 text-sm text-muted">
                            @foreach ($product->featured_players as $player)
                                <li>{{ $player }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($product->description)
                    <div class="mt-6">
                        <h2 class="font-display text-lg font-semibold">Mô tả</h2>
                        <p class="mt-2 text-sm leading-7 text-muted">{{ $product->description }}</p>
                    </div>
                @endif

                @error('product')
                    <x-alert type="danger" class="mt-6">{{ $message }}</x-alert>
                @enderror
                @if ($product->isAvailable())
                    <x-buy-button :product="$product" size="lg" class="mt-6 inline-flex" />
                @else
                    <x-alert type="warning" class="mt-6">
                        Tài khoản này đã bán hoặc không còn khả dụng. Thông tin đăng nhập game không được hiển thị công khai.
                    </x-alert>
                @endif
            </div>
        </div>

        <section class="mt-14" aria-labelledby="san-pham-lien-quan">
            <h2 id="san-pham-lien-quan" class="font-display text-2xl font-semibold">Sản phẩm liên quan</h2>
            @if ($relatedProducts->isEmpty())
                <x-empty-state class="mt-6" title="Chưa có sản phẩm liên quan">
                    Khi có thêm tài khoản cùng danh mục còn hàng, chúng sẽ xuất hiện tại đây.
                </x-empty-state>
            @else
                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
