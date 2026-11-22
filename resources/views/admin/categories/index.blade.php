<x-layouts.admin title="Danh mục">
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-semibold">Danh mục</h1>
        <a href="{{ route('admin.categories.create') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Thêm danh mục</a>
    </div>
    @error('category') <x-alert type="danger" class="mt-6">{{ $message }}</x-alert> @enderror
    <div class="mt-8 grid gap-3">
        @forelse ($categories as $category)
            <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4">
                <div>
                    <p class="font-medium">{{ $category->name }}</p>
                    <p class="text-sm text-muted">{{ $category->products_count }} sản phẩm · {{ $category->is_visible ? 'Hiện' : 'Ẩn' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-full border border-line px-4 py-2 text-xs">Sửa</a>
                    <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Xóa danh mục này?')">
                        @csrf
                        @method('delete')
                        <button class="rounded-full border border-line px-4 py-2 text-xs text-primary">Xóa</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="text-sm text-muted">Chưa có danh mục.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$categories" />
</x-layouts.admin>
