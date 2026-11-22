@props([
    'categories' => [],
    'tools' => [],
    'variant' => 'desktop',
])

@php
    $isMobile = $variant === 'mobile';
    $linkClass = $isMobile
        ? 'block rounded-xl px-3 py-3 text-ink hover:bg-surface-2'
        : 'whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-muted transition hover:bg-surface-2 hover:text-ink';
@endphp

<nav {{ $attributes->merge(['class' => $isMobile ? 'grid gap-1' : 'items-center gap-1']) }} aria-label="Điều hướng chính">
    <a href="{{ route('home') }}" class="{{ $linkClass }} {{ request()->routeIs('home') ? 'text-ink' : '' }}">Trang chủ</a>

    @if ($isMobile)
        <a href="{{ route('catalog.index') }}" class="{{ $linkClass }}">Danh mục tài khoản</a>
        @forelse ($categories as $category)
            <a href="{{ route('catalog.category', $category) }}" class="{{ $linkClass }} pl-6 text-sm text-muted">{{ $category->name }}</a>
        @empty
            <p class="px-3 py-2 text-xs text-muted">Danh mục sẽ hiển thị khi Admin thêm từ trang quản trị.</p>
        @endforelse
    @else
        <x-dropdown label="Danh mục tài khoản">
            <a href="{{ route('catalog.index') }}" class="block rounded-xl px-3 py-2 text-sm text-ink hover:bg-surface-2" role="menuitem">Tất cả danh mục</a>
            @forelse ($categories as $category)
                <a href="{{ route('catalog.category', $category) }}" class="block rounded-xl px-3 py-2 text-sm text-muted hover:bg-surface-2 hover:text-ink" role="menuitem">{{ $category->name }}</a>
            @empty
                <p class="px-3 py-2 text-xs text-muted">Chưa có danh mục từ Admin.</p>
            @endforelse
        </x-dropdown>
    @endif

    <a href="{{ route('services.index') }}" class="{{ $linkClass }}">Dịch vụ FCO</a>
    <a href="{{ route('guides') }}" class="{{ $linkClass }}">Hướng dẫn</a>
    <a href="{{ route('posts.index') }}" class="{{ $linkClass }}">Bài viết</a>

    @if ($isMobile)
        <a href="{{ route('tools.index') }}" class="{{ $linkClass }}">Công cụ FC Online</a>
        @foreach ($tools as $tool)
            <a href="{{ route('tools.show', $tool['slug']) }}" class="{{ $linkClass }} pl-6 text-sm text-muted">{{ $tool['name'] }}</a>
        @endforeach
    @else
        <x-dropdown label="Công cụ FC Online" align="right">
            <a href="{{ route('tools.index') }}" class="block rounded-xl px-3 py-2 text-sm text-ink hover:bg-surface-2" role="menuitem">Tất cả công cụ</a>
            @foreach ($tools as $tool)
                <a href="{{ route('tools.show', $tool['slug']) }}" class="block rounded-xl px-3 py-2 text-sm text-muted hover:bg-surface-2 hover:text-ink" role="menuitem">{{ $tool['name'] }}</a>
            @endforeach
        </x-dropdown>
    @endif

    <a href="{{ route('transactions.history') }}" class="{{ $linkClass }}">Lịch sử giao dịch</a>
    @guest
        <a href="{{ route('register') }}" class="{{ $linkClass }}">Đăng ký</a>
    @else
        @if (auth()->user()->isStaff())
            <a href="{{ route('admin.dashboard') }}" class="{{ $linkClass }}">Trang quản trị</a>
        @endif
        <a href="{{ route('orders.index') }}" class="{{ $linkClass }}">Đơn hàng của tôi</a>
        <a href="{{ route('affiliate.show') }}" class="{{ $linkClass }}">Giới thiệu bạn bè</a>
    @endguest
    <a href="{{ route('account') }}" class="{{ $linkClass }}">Tài khoản cá nhân</a>
</nav>
