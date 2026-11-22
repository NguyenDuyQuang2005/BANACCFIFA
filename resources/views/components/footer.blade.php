@props([
    'site' => [],
    'tools' => [],
])

@php
    $social = $site['social'] ?? [];
    $contact = $site['contact'] ?? [];
@endphp

<footer class="mt-16 border-t border-line bg-surface">
    <div class="gold-rule"></div>
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div class="space-y-4">
            <x-logo />
            <p class="max-w-sm text-sm leading-6 text-muted">{{ $site['tagline'] ?? '' }}. Nền tảng được thiết kế riêng cho DINHTIENFCO, tập trung trải nghiệm tối và rõ ràng.</p>
            @if (! empty($contact['email']) || ! empty($contact['phone']))
                <p class="text-sm text-ink">
                    @if (! empty($contact['email'])) Email: {{ $contact['email'] }} @endif
                    @if (! empty($contact['phone'])) <span class="block">Điện thoại: {{ $contact['phone'] }}</span> @endif
                </p>
            @endif
        </div>

        <div>
            <h2 class="font-display text-sm font-semibold uppercase tracking-[0.2em] text-accent">Liên hệ & hướng dẫn</h2>
            <ul class="mt-4 grid gap-2 text-sm text-muted">
                <li><a class="hover:text-ink" href="{{ route('contact') }}">Liên hệ</a></li>
                <li><a class="hover:text-ink" href="{{ route('guides') }}">Hướng dẫn</a></li>
                <li><a class="hover:text-ink" href="{{ route('deposit') }}">Nạp tiền</a></li>
                @foreach (['zalo' => 'Zalo', 'telegram' => 'Telegram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok'] as $key => $label)
                    @if (! empty($social[$key]))
                        <li><a class="hover:text-ink" href="{{ $social[$key] }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a></li>
                    @endif
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="font-display text-sm font-semibold uppercase tracking-[0.2em] text-accent">Chính sách</h2>
            <ul class="mt-4 grid gap-2 text-sm text-muted">
                <li><a class="hover:text-ink" href="{{ route('policies') }}">Chính sách</a></li>
                <li><a class="hover:text-ink" href="{{ route('terms') }}">Điều khoản</a></li>
                <li><a class="hover:text-ink" href="{{ route('privacy') }}">Bảo mật</a></li>
            </ul>
        </div>

        <div>
            <h2 class="font-display text-sm font-semibold uppercase tracking-[0.2em] text-accent">Công cụ</h2>
            <ul class="mt-4 grid gap-2 text-sm text-muted">
                <li><a class="hover:text-ink" href="{{ route('tools.index') }}">Công cụ FC Online</a></li>
                @foreach ($tools as $tool)
                    <li><a class="hover:text-ink" href="{{ route('tools.show', $tool['slug']) }}">{{ $tool['name'] }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="border-t border-line px-4 py-6 text-center text-xs text-muted">
        © {{ now()->year }} {{ $site['name'] ?? config('app.name') }}. Mọi giao dịch và danh mục chỉ hiển thị khi có dữ liệu thật.
    </div>
</footer>
