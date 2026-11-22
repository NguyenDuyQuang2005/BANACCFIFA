<x-layouts.app :title="$tool['name']" :meta-description="$tool['summary']">
    <div class="mx-auto max-w-3xl px-4 py-10 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Công cụ FC Online', 'url' => route('tools.index')],
            ['label' => $tool['name']],
        ]" />

        <h1 class="mt-6 font-display text-3xl font-semibold">{{ $tool['name'] }}</h1>
        <p class="mt-3 text-sm leading-6 text-muted">{{ $tool['summary'] }}</p>

        <x-alert type="warning" class="mt-6">
            Công cụ chưa tính toán vì chưa có công thức hoặc nguồn dữ liệu được xác nhận. Cần bổ sung: {{ $tool['needs'] ?? 'Thông tin nghiệp vụ từ bạn.' }}
        </x-alert>

        @if ($tool['requires_auth'] ?? false)
            <x-alert class="mt-4">
                Module này dự kiến yêu cầu đăng nhập. Quyền truy cập sẽ được kiểm soát ở backend, không chỉ ẩn nút trên giao diện.
            </x-alert>
        @endif
    </div>
</x-layouts.app>
