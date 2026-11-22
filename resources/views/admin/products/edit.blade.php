<x-layouts.admin title="Sửa acc {{ $product->code }}">
    <h1 class="font-display text-3xl font-semibold">Sửa {{ $product->code }}</h1>
    <form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @method('put')
        @include('admin.products._form', ['product' => $product, 'categories' => $categories])
        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Cập nhật</button>
    </form>

    <section class="mt-10 max-w-2xl rounded-3xl border border-line bg-surface p-6">
        <h2 class="font-display text-xl font-semibold">Ảnh acc ({{ $product->images->count() }})</h2>
        <p class="mt-1 text-xs text-muted">Thêm ảnh bằng ô "Thêm ảnh acc" trong form phía trên rồi bấm Cập nhật. Bấm "Đặt bìa" để chọn ảnh hiện ngoài danh sách.</p>
        @if ($product->images->isEmpty())
            <p class="mt-4 rounded-2xl border border-dashed border-line px-4 py-6 text-center text-sm text-muted">Chưa có ảnh.</p>
        @else
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($product->images as $image)
                    <figure class="overflow-hidden rounded-2xl border {{ $image->is_primary ? 'border-accent' : 'border-line' }}">
                        <a href="{{ $image->url() }}" target="_blank"><img src="{{ $image->url() }}" alt="{{ $image->alt }}" class="aspect-square w-full object-cover"></a>
                        <figcaption class="flex items-center justify-between gap-2 px-2 py-2 text-xs text-muted">
                            @if ($image->is_primary)
                                <span class="font-semibold text-accent">★ Ảnh bìa</span>
                            @else
                                <form method="post" action="{{ route('admin.products.images.primary', [$product, $image]) }}">
                                    @csrf
                                    @method('put')
                                    <button class="hover:text-accent">Đặt bìa</button>
                                </form>
                            @endif
                            <form method="post" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" onsubmit="return confirm('Xóa ảnh này?')">
                                @csrf
                                @method('delete')
                                <button class="text-primary">Xóa</button>
                            </form>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.admin>
