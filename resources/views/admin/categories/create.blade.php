<x-layouts.admin title="Thêm danh mục">
    <h1 class="font-display text-3xl font-semibold">Thêm danh mục</h1>
    <form method="post" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="mt-8 grid max-w-xl gap-4">
        @csrf
        @include('admin.categories._form')
        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Lưu danh mục</button>
    </form>
</x-layouts.admin>
