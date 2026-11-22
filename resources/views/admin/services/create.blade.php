<x-layouts.admin title="Thêm dịch vụ">
    <h1 class="font-display text-3xl font-semibold">Thêm dịch vụ</h1>
    <form method="post" action="{{ route('admin.services.store') }}" enctype="multipart/form-data" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @include('admin.services._form')
        <button class="justify-self-start rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Tạo dịch vụ</button>
    </form>
</x-layouts.admin>
