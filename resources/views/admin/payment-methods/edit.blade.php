<x-layouts.admin title="Sửa ngân hàng">
    <h1 class="font-display text-3xl font-semibold">Sửa phương thức nạp</h1>
    <form method="post" action="{{ route('admin.payment-methods.update', $method) }}" enctype="multipart/form-data" class="mt-8 grid max-w-xl gap-4">
        @csrf
        @method('put')
        @include('admin.payment-methods._form', ['method' => $method])
        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Cập nhật</button>
    </form>
</x-layouts.admin>
