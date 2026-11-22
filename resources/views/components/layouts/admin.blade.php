@props([
    'title' => 'Quản trị',
])

@php
    $pageTitle = $title.' | Admin '.config('site.name');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $pageTitle }}</title>
        <meta name="robots" content="noindex, nofollow">
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-canvas text-ink antialiased">
        <div class="lg:grid lg:grid-cols-[240px_1fr]">
            <aside class="border-b border-line bg-surface lg:min-h-screen lg:border-b-0 lg:border-r">
                <div class="flex items-center justify-between px-4 py-4">
                    <a href="{{ route('admin.dashboard') }}" class="font-display text-sm font-semibold tracking-widest text-accent">ADMIN</a>
                    <a href="{{ route('home') }}" class="text-xs text-muted hover:text-accent">Về shop</a>
                </div>
                @auth
                    <div class="mx-3 mb-4 rounded-xl border border-line px-3 py-2 text-xs">
                        <p class="truncate text-ink">{{ auth()->user()->email }}</p>
                        <p class="mt-0.5 {{ auth()->user()->isAdmin() ? 'text-primary' : 'text-accent' }}">{{ auth()->user()->role->label() }}</p>
                    </div>
                @endauth
                @php
                    $canManageSystem = auth()->user()?->can('manage-system') === true;
                    $devOnlyRoutes = ['admin.payment-methods.index', 'admin.settings.edit', 'admin.activity-logs.index'];
                    $adminNav = [
                        'Tổng quan' => [
                            ['admin.dashboard', 'admin.dashboard', 'Tổng quan'],
                            ['admin.revenue', 'admin.revenue', 'Doanh thu & biểu đồ'],
                        ],
                        'Bán hàng' => [
                            ['admin.categories.index', 'admin.categories.*', 'Danh mục'],
                            ['admin.products.index', 'admin.products.*', 'Tài khoản game'],
                            ['admin.services.index', 'admin.services.*', 'Dịch vụ FCO'],
                            ['admin.orders.index', 'admin.orders.*', 'Đơn hàng'],
                        ],
                        'Tài chính' => [
                            ['admin.deposits.index', 'admin.deposits.*', 'Duyệt nạp tiền'],
                            ['admin.bank-transactions.index', 'admin.bank-transactions.*', 'Giao dịch ngân hàng'],
                            ['admin.wallet-transactions.index', 'admin.wallet-transactions.*', 'Giao dịch ví'],
                            ['admin.payment-methods.index', 'admin.payment-methods.*', 'Ngân hàng nạp tiền'],
                        ],
                        'Thành viên' => [
                            ['admin.members.index', 'admin.members.*', 'Thành viên & số dư'],
                            ['admin.affiliates.index', 'admin.affiliates.*', 'Affiliate · Giới thiệu'],
                            ['admin.contact-messages.index', 'admin.contact-messages.*', 'Liên hệ'],
                        ],
                        'Hệ thống' => [
                            ['admin.banners.index', 'admin.banners.*', 'Banner'],
                            ['admin.popups.index', 'admin.popups.*', 'Popup'],
                            ['admin.settings.edit', 'admin.settings.*', 'Cấu hình & SEO'],
                            ['admin.activity-logs.index', 'admin.activity-logs.*', 'Nhật ký hoạt động'],
                        ],
                    ];
                @endphp
                <nav class="grid gap-4 px-3 pb-6 text-sm" aria-label="Điều hướng quản trị">
                    @foreach ($adminNav as $group => $links)
                        <div class="grid gap-1">
                            <p class="px-3 text-[11px] uppercase tracking-widest text-muted/70">{{ $group }}</p>
                            @foreach ($links as [$routeName, $pattern, $label])
                                @continue(! $canManageSystem && in_array($routeName, $devOnlyRoutes, true))
                                <a href="{{ route($routeName) }}" class="rounded-xl px-3 py-2 {{ request()->routeIs($pattern) ? 'bg-surface-2 text-accent' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">{{ $label }}</a>
                            @endforeach
                        </div>
                    @endforeach
                </nav>
                <form method="post" action="{{ route('logout') }}" class="px-3 pb-6">
                    @csrf
                    <button class="text-xs text-muted hover:text-primary">Đăng xuất</button>
                </form>
            </aside>
            <main class="px-4 py-8 lg:px-10">
                @if (session('status'))
                    <x-alert type="success" class="mb-6">{{ session('status') }}</x-alert>
                @endif
                @if ($errors->any() && ! request()->routeIs('admin.members.show'))
                    <x-alert type="danger" class="mb-6">{{ $errors->first() }}</x-alert>
                @endif
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
