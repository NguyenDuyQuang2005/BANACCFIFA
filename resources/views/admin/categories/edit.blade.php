<x-layouts.admin title="Sửa danh mục">
    <h1 class="font-display text-3xl font-semibold">Sửa danh mục</h1>
    <form method="post" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="mt-8 grid max-w-xl gap-4">
        @csrf
        @method('put')
        @include('admin.categories._form', ['category' => $category])
        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Cập nhật</button>
    </form>
</x-layouts.admin>
