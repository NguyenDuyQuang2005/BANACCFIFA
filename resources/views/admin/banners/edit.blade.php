<x-layouts.admin title="Sửa banner">
    <h1 class="font-display text-3xl font-semibold">Sửa banner</h1>
    <form method="post" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="mt-8 grid max-w-xl gap-4">
        @csrf
        @method('put')
        @include('admin.banners._form', ['banner' => $banner])
        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Cập nhật</button>
    </form>
</x-layouts.admin>
