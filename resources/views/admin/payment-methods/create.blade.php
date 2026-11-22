<x-layouts.admin title="Thêm ngân hàng">
    <h1 class="font-display text-3xl font-semibold">Thêm số tài khoản</h1>
    <form method="post" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data" class="mt-8 grid max-w-xl gap-4">
        @csrf
        @include('admin.payment-methods._form')
        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Lưu</button>
    </form>
</x-layouts.admin>
