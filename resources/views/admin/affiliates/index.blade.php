@php
    $money = fn (float $value): string => number_format($value, 0, ',', '.').'₫';
@endphp

<x-layouts.admin title="Affiliate">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-semibold">Affiliate · Giới thiệu bạn bè</h1>
            <p class="mt-2 text-sm text-muted">Người giới thiệu nhận hoa hồng trên mỗi đơn mua acc hoàn tất của người họ giới thiệu. Hoa hồng nằm ở số dư riêng, rút về ví hoặc ngân hàng.</p>
        </div>
        <a href="{{ route('admin.affiliates.withdrawals') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Yêu cầu rút ({{ $totals['pending_count'] }} chờ duyệt)</a>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Tổng hoa hồng đã phát sinh', $money($totals['commissions'])],
            ['Hoa hồng thành viên đang giữ', $money($totals['outstanding'])],
            ['Đã chi trả (ví + ngân hàng)', $money($totals['paid'])],
            ['Thành viên đến từ giới thiệu', number_format($totals['referred_users'])],
        ] as [$label, $value])
            <section class="rounded-3xl border border-line bg-surface p-5">
                <p class="text-xs uppercase tracking-widest text-muted">{{ $label }}</p>
                <p class="mt-2 font-display text-2xl text-accent">{{ $value }}</p>
            </section>
        @endforeach
    </div>

    <section class="mt-6 rounded-3xl border border-line bg-surface p-6">
        <h2 class="font-display text-xl font-semibold">Cấu hình</h2>
        @can('manage-system')
            <form method="post" action="{{ route('admin.affiliates.settings') }}" class="mt-4 grid gap-4 md:grid-cols-5">
                @csrf
                @method('put')
                <label class="grid gap-2 text-sm">
                    <span class="font-medium">Trạng thái</span>
                    <select name="enabled" class="rounded-xl border border-line bg-canvas px-3 py-2.5">
                        <option value="1" @selected(old('enabled', $settings['enabled'] ? '1' : '0') === '1')>Đang bật</option>
                        <option value="0" @selected(old('enabled', $settings['enabled'] ? '1' : '0') === '0')>Tạm dừng</option>
                    </select>
                </label>
                <x-form-input name="rate" type="number" label="Hoa hồng mặc định (%)" :value="old('rate', $settings['rate'])" min="0" max="50" step="0.1" required />
                <x-form-input name="min_withdraw" type="number" label="Rút tối thiểu (₫)" :value="old('min_withdraw', $settings['min_withdraw'])" min="1000" step="1000" required />
                <x-form-input name="max_withdraw" type="number" label="Rút tối đa (₫)" :value="old('max_withdraw', $settings['max_withdraw'])" min="1000" step="1000" required />
                <x-form-input name="cookie_days" type="number" label="Ghi nhớ link (ngày)" :value="old('cookie_days', $settings['cookie_days'])" min="1" max="365" required />
                @foreach (['rate', 'min_withdraw', 'max_withdraw', 'cookie_days'] as $field)
                    @error($field) <p class="text-sm text-primary md:col-span-5">{{ $message }}</p> @enderror
                @endforeach
                <button class="justify-self-start rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white md:col-span-5">Lưu cấu hình</button>
            </form>
        @else
            <p class="mt-3 text-sm text-muted">
                {{ $settings['enabled'] ? 'Đang bật' : 'Tạm dừng' }} · Hoa hồng mặc định {{ $settings['rate'] }}% · Rút {{ $money($settings['min_withdraw']) }} – {{ $money($settings['max_withdraw']) }} · Ghi nhớ link {{ $settings['cookie_days'] }} ngày.
                Chỉ Admin dev được sửa cấu hình.
            </p>
        @endcan
    </section>

    <section class="mt-6 rounded-3xl border border-line bg-surface p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-display text-xl font-semibold">Người giới thiệu</h2>
            <form method="get" class="flex gap-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="Email, tên hoặc mã giới thiệu" class="rounded-xl border border-line bg-canvas px-3 py-2 text-sm">
                <button class="rounded-full border border-line px-4 py-2 text-sm">Tìm</button>
            </form>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="text-xs uppercase tracking-widest text-muted">
                    <tr>
                        <th class="py-2">Thành viên</th>
                        <th class="py-2">Click</th>
                        <th class="py-2">Đăng ký</th>
                        <th class="py-2">Đơn</th>
                        <th class="py-2">Tổng hoa hồng</th>
                        <th class="py-2">Số dư HH</th>
                        <th class="py-2">Mã · % riêng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($affiliates as $affiliate)
                        <tr class="border-t border-line align-top">
                            <td class="py-3"><a href="{{ route('admin.members.show', $affiliate) }}" class="hover:text-accent">{{ $affiliate->email }}</a></td>
                            <td class="py-3">{{ number_format($affiliate->affiliate_clicks) }}</td>
                            <td class="py-3">{{ number_format($affiliate->referrals_count) }}</td>
                            <td class="py-3">{{ number_format($affiliate->commissions_count) }}</td>
                            <td class="py-3">{{ $money((float) $affiliate->commissions_total) }}</td>
                            <td class="py-3 text-accent">{{ $money((float) $affiliate->commission_balance) }}</td>
                            <td class="py-3">
                                @can('manage-system')
                                    <form method="post" action="{{ route('admin.affiliates.member', $affiliate) }}" class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        @method('put')
                                        <input name="affiliate_code" value="{{ $affiliate->affiliate_code }}" class="w-28 rounded-lg border border-line bg-canvas px-2 py-1 text-xs uppercase" aria-label="Mã giới thiệu" required>
                                        <input name="affiliate_rate" type="number" step="0.1" min="0" max="50" value="{{ $affiliate->affiliate_rate !== null ? (float) $affiliate->affiliate_rate : '' }}" placeholder="{{ $settings['rate'] }} (mặc định)" class="w-28 rounded-lg border border-line bg-canvas px-2 py-1 text-xs" aria-label="% hoa hồng riêng">
                                        <button class="rounded-full border border-line px-3 py-1 text-xs">Lưu</button>
                                    </form>
                                @else
                                    {{ $affiliate->affiliate_code }} · {{ $affiliate->affiliate_rate !== null ? (float) $affiliate->affiliate_rate.'%' : 'mặc định' }}
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-muted">Chưa có ai giới thiệu thành viên.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @error('affiliate_code') <p class="mt-2 text-sm text-primary">{{ $message }}</p> @enderror
        <x-pagination :paginator="$affiliates" />
    </section>

    <section class="mt-6 rounded-3xl border border-line bg-surface p-6">
        <h2 class="font-display text-xl font-semibold">Hoa hồng gần đây</h2>
        @forelse ($recentCommissions as $commission)
            <p class="mt-3 text-sm">
                <span class="text-muted">{{ $commission->created_at->format('d/m H:i') }}</span>
                · {{ $commission->referrer?->email }} nhận <strong class="text-emerald-400">{{ $commission->formattedAmount() }}</strong>
                ({{ (float) $commission->rate }}% đơn {{ $commission->order?->code }} của {{ $commission->referredUser?->email }})
            </p>
        @empty
            <p class="mt-3 text-sm text-muted">Chưa có hoa hồng.</p>
        @endforelse
    </section>
</x-layouts.admin>
