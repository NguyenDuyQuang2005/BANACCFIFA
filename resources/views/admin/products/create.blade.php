<x-layouts.admin title="Thêm acc">
    <h1 class="font-display text-3xl font-semibold">Thêm tài khoản game</h1>
    <form method="post" action="{{ route('admin.products.store') }}" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @include('admin.products._form')
        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Tạo acc</button>
    </form>
</x-layouts.admin>
