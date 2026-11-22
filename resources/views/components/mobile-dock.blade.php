<nav class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-5 border-t border-line bg-surface/95 px-1 py-2 backdrop-blur lg:hidden" aria-label="Điều hướng nhanh">
    <a href="{{ route('home') }}" class="grid place-items-center gap-1 text-[11px] {{ request()->routeIs('home') ? 'text-accent' : 'text-muted' }}">
        <span class="font-display text-base">DT</span>
        Trang chủ
    </a>
    <a href="{{ route('catalog.index') }}" class="grid place-items-center gap-1 text-[11px] {{ request()->routeIs('catalog.*') ? 'text-accent' : 'text-muted' }}">
        <span class="font-display text-base">Acc</span>
        Danh mục
    </a>
    <a href="{{ route('tools.index') }}" class="grid place-items-center gap-1 text-[11px] {{ request()->routeIs('tools.*') ? 'text-accent' : 'text-muted' }}">
        <span class="font-display text-base">Tool</span>
        Công cụ
    </a>
    <a href="{{ route('deposit') }}" class="grid place-items-center gap-1 text-[11px] {{ request()->routeIs('deposit') ? 'text-accent' : 'text-muted' }}">
        <span class="font-display text-base">+</span>
        Nạp tiền
    </a>
    <a href="{{ route('account') }}" class="grid place-items-center gap-1 text-[11px] {{ request()->routeIs('account') ? 'text-accent' : 'text-muted' }}">
        <span class="font-display text-base">Me</span>
        Tài khoản
    </a>
</nav>
