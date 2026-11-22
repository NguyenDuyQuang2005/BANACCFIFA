<x-layouts.admin title="Thêm banner">
    <h1 class="font-display text-3xl font-semibold">Thêm banner</h1>
    <form method="post" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="mt-8 grid max-w-xl gap-4">
        @csrf
        @include('admin.banners._form')
        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Tạo banner</button>
    </form>
</x-layouts.admin>
