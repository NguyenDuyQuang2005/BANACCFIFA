<x-layouts.admin title="Sửa dịch vụ">
    <h1 class="font-display text-3xl font-semibold">Sửa dịch vụ</h1>
    <form method="post" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @method('put')
        @include('admin.services._form', ['service' => $service])
        <button class="justify-self-start rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Cập nhật</button>
    </form>
</x-layouts.admin>
