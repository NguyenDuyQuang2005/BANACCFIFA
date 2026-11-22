@props([
    'site' => [],
    'categories' => [],
    'tools' => [],
])

@php
    $social = $site['social'] ?? [];
@endphp

<header class="sticky top-0 z-40 border-b border-line bg-canvas/90 backdrop-blur">
    <div class="h-0.5 bg-linear-to-r from-transparent via-accent to-primary"></div>
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 lg:px-8">
        <x-logo />

        <div class="flex items-center gap-2">
            @foreach (['zalo' => 'Zalo', 'telegram' => 'Telegram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok'] as $key => $label)
                @if (! empty($social[$key]))
                    <a href="{{ $social[$key] }}" class="hidden rounded-full border border-line px-3 py-1.5 text-xs font-medium text-muted hover:text-accent sm:inline-flex" target="_blank" rel="noopener noreferrer">{{ $label }}</a>
                @endif
            @endforeach
            <a href="{{ route('deposit') }}" class="hidden rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark md:inline-flex">Nạp tiền</a>
            @auth
                <a href="{{ route('account') }}" class="hidden rounded-full border border-line px-4 py-2 text-sm font-medium text-ink hover:border-accent sm:inline-flex">
                    {{ auth()->user()->name }} · {{ auth()->user()->wallet?->formattedBalance() ?? '0₫' }}
                </a>
                <form method="post" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="rounded-full border border-line px-4 py-2 text-sm font-medium text-ink hover:border-accent">Đăng xuất</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-full border border-line px-4 py-2 text-sm font-medium text-ink hover:border-accent">Đăng nhập</a>
            @endauth
            <button type="button" class="inline-flex rounded-xl border border-line p-2 text-ink lg:hidden" data-mobile-toggle aria-expanded="false" aria-controls="menu-mobile">
                <span class="sr-only">Mở menu</span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <div class="hidden border-t border-line lg:block">
        <div class="mx-auto flex max-w-7xl justify-center px-4 py-1 lg:px-8">
            <x-navigation :categories="$categories" :tools="$tools" class="flex flex-wrap justify-center" />
        </div>
    </div>

    <div id="menu-mobile" class="hidden lg:hidden" data-mobile-nav>
        <div class="border-t border-line bg-surface px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <p class="font-display text-sm uppercase tracking-widest text-muted">Menu</p>
                <button type="button" class="rounded-lg border border-line px-3 py-1 text-sm" data-mobile-close>Đóng</button>
            </div>
            <x-navigation :categories="$categories" :tools="$tools" variant="mobile" />
        </div>
    </div>
</header>
