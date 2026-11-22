<x-layouts.admin title="Sửa popup">
    <h1 class="font-display text-3xl font-semibold">Sửa popup</h1>
    <p class="mt-2 text-sm text-muted">Sau khi lưu, popup sẽ hiện lại cho cả những người đã đóng phiên bản cũ.</p>
    <form method="post" action="{{ route('admin.popups.update', $popup) }}" enctype="multipart/form-data" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @method('put')
        @include('admin.popups._form', ['popup' => $popup])
        <button class="justify-self-start rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Cập nhật</button>
    </form>
</x-layouts.admin>
