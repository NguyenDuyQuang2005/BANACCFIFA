<x-layouts.admin title="Tài khoản game">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-semibold">Tài khoản game</h1>
        <a href="{{ route('admin.products.create') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Thêm acc</a>
    </div>
    <form method="get" class="mt-6 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('admin.products.index') }}" class="rounded-full border border-line px-3 py-1 {{ $status === '' ? 'border-accent text-accent' : '' }}">Tất cả</a>
        @foreach (\App\Enums\ProductStatus::cases() as $item)
            <a href="{{ route('admin.products.index', ['status' => $item->value]) }}" class="rounded-full border border-line px-3 py-1 {{ $status === $item->value ? 'border-accent text-accent' : '' }}">{{ $item->label() }}</a>
        @endforeach
    </form>
    @error('product') <x-alert type="danger" class="mt-6">{{ $message }}</x-alert> @enderror
    <div class="mt-6 grid gap-3">
        @forelse ($products as $product)
            <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4">
                <div>
                    <p class="font-medium">{{ $product->code }} · {{ $product->name }}</p>
                    <p class="text-sm text-muted">{{ $product->category?->name }} · {{ $product->formattedPrice() }} · {{ $product->status->label() }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.products.edit', $product) }}" class="rounded-full border border-line px-4 py-2 text-xs">Sửa</a>
                    <form method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Xóa tài khoản này?')">
                        @csrf
                        @method('delete')
                        <button class="rounded-full border border-line px-4 py-2 text-xs text-primary">Xóa</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="text-sm text-muted">Chưa có tài khoản game.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$products" />
</x-layouts.admin>
