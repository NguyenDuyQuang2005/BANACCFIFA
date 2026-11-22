<x-layouts.admin title="Thêm popup">
    <h1 class="font-display text-3xl font-semibold">Thêm popup</h1>
    <form method="post" action="{{ route('admin.popups.store') }}" enctype="multipart/form-data" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @include('admin.popups._form')
        <button class="justify-self-start rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Tạo popup</button>
    </form>
</x-layouts.admin>
