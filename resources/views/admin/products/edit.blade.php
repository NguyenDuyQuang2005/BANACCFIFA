<x-layouts.admin title="Sửa acc {{ $product->code }}">
    <h1 class="font-display text-3xl font-semibold">Sửa {{ $product->code }}</h1>
    <form method="post" action="{{ route('admin.products.update', $product) }}" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @method('put')
        @include('admin.products._form', ['product' => $product, 'categories' => $categories])
        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Cập nhật</button>
    </form>

    <section class="mt-10 max-w-2xl rounded-3xl border border-line bg-surface p-6">
        <h2 class="font-display text-xl font-semibold">Ảnh sản phẩm</h2>
        <form method="post" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" class="mt-4 grid gap-3">
            @csrf
            <p class="text-xs text-muted">jpg, png, webp · tối đa 20MB</p>
            <input type="file" name="image" accept="image/*" required class="text-sm">
            <x-form-input name="alt" label="Mô tả ảnh" :value="$product->name" />
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_primary" value="1">
                Đặt làm ảnh bìa
            </label>
            <button class="rounded-full border border-line px-4 py-2 text-sm">Tải ảnh</button>
        </form>
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($product->images as $image)
                <figure class="overflow-hidden rounded-2xl border border-line">
                    <img src="{{ $image->url() }}" alt="{{ $image->alt }}" class="aspect-square w-full object-cover">
                    <figcaption class="flex items-center justify-between px-2 py-2 text-xs text-muted">
                        <span>{{ $image->is_primary ? 'Bìa' : 'Ảnh' }}</span>
                        <form method="post" action="{{ route('admin.products.images.destroy', [$product, $image]) }}">
                            @csrf
                            @method('delete')
                            <button class="text-primary">Xóa</button>
                        </form>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>
</x-layouts.admin>
