@php
    $money = fn (float $value): string => number_format($value, 0, ',', '.').'₫';
    $maskName = fn (?string $name): string => $name ? mb_substr($name, 0, 2).str_repeat('*', max(2, min(6, mb_strlen($name) - 2))) : 'Thành viên';
    $statCards = [
        ['Số dư hoa hồng', $money($stats['balance']), 'text-accent'],
        ['Hoa hồng đã rút', $money($stats['withdrawn']), 'text-ink'],
        ['Đang chờ rút', $money($stats['pending']), 'text-ink'],
        ['Tổng hoa hồng đã nhận', $money($stats['earned']), 'text-ink'],
        ['Lượt click link', number_format($stats['clicks']), 'text-ink'],
        ['Người đăng ký qua link', number_format($stats['referrals']), 'text-ink'],
        ['Acc đã bán qua giới thiệu', number_format($stats['orders']), 'text-ink'],
        ['Doanh số từ người giới thiệu', $money($stats['sales']), 'text-ink'],
    ];
@endphp

<x-layouts.app title="Giới thiệu bạn bè" robots="noindex, nofollow">
    <div class="mx-auto max-w-6xl px-4 py-10 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Tài khoản', 'url' => route('account')],
            ['label' => 'Giới thiệu bạn bè'],
        ]" />
        <h1 class="mt-6 font-display text-3xl font-semibold">Giới thiệu bạn bè · Affiliate</h1>

        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif

        @unless ($settings['enabled'])
            <x-alert type="warning" class="mt-6">Chương trình giới thiệu đang tạm dừng. Link giới thiệu và rút hoa hồng tạm thời không hoạt động.</x-alert>
        @endunless

        <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($statCards as [$label, $value, $color])
                <section class="rounded-3xl border border-line bg-surface p-5">
                    <p class="text-xs text-muted">{{ $label }}</p>
                    <p class="mt-2 font-display text-2xl font-semibold {{ $color }}">{{ $value }}</p>
                </section>
            @endforeach
        </div>

        <section class="mt-6 rounded-3xl border border-primary/40 bg-surface p-6">
            <h2 class="font-display text-xl font-semibold">Link giới thiệu của bạn</h2>
            <p class="mt-1 text-sm text-muted">Chia sẻ link này. Người đăng ký qua link (hoặc nhập mã <strong class="text-accent">{{ $user->affiliate_code }}</strong> khi đăng ký) sẽ là người bạn giới thiệu.</p>
            <div class="mt-4 rounded-2xl border border-accent/30 bg-accent/10 px-4 py-3 text-sm">
                Nhận <strong class="text-accent">{{ rtrim(rtrim(number_format($rate, 2, ',', '.'), '0'), ',') }}%</strong> hoa hồng trên mỗi tài khoản người được giới thiệu mua trên web.
            </div>
            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                <input type="text" readonly value="{{ $user->affiliateLink() }}" class="min-w-0 flex-1 rounded-xl border border-line bg-canvas px-3 py-2.5 text-sm" aria-label="Link giới thiệu" onclick="this.select()">
                <button type="button" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark" data-copy="{{ $user->affiliateLink() }}">Sao chép</button>
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="rounded-3xl border border-line bg-surface p-6">
                <h2 class="font-display text-xl font-semibold">Rút hoa hồng</h2>
                <div class="mt-4 grid grid-cols-2 rounded-2xl border border-line text-center text-sm">
                    <div class="p-3"><p class="text-xs text-muted">Tối thiểu</p><p class="font-semibold text-accent">{{ $money($settings['min_withdraw']) }}</p></div>
                    <div class="border-l border-line p-3"><p class="text-xs text-muted">Tối đa</p><p class="font-semibold text-accent">{{ $money($settings['max_withdraw']) }}</p></div>
                </div>
                <form method="post" action="{{ route('affiliate.withdraw') }}" class="mt-4 grid gap-3" data-withdraw-form>
                    @csrf
                    <x-form-input name="amount" type="number" label="Số tiền rút" :value="old('amount', $settings['min_withdraw'])" :min="$settings['min_withdraw']" :max="$settings['max_withdraw']" step="1000" required />
                    @error('amount') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                    <label class="grid gap-2 text-sm">
                        <span class="font-medium">Rút về</span>
                        <select name="method" class="rounded-xl border border-line bg-canvas px-3 py-2.5" data-withdraw-method>
                            <option value="wallet" @selected(old('method', 'wallet') === 'wallet')>Ví trên web (cộng ngay, dùng để mua acc)</option>
                            <option value="bank" @selected(old('method') === 'bank')>Ngân hàng (admin duyệt và chuyển khoản)</option>
                        </select>
                    </label>
                    <div class="grid gap-3 {{ old('method') === 'bank' ? '' : 'hidden' }}" data-bank-fields>
                        <x-form-input name="bank_name" label="Ngân hàng" :value="old('bank_name')" placeholder="VD: MB Bank" />
                        @error('bank_name') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                        <x-form-input name="bank_account_number" label="Số tài khoản" :value="old('bank_account_number')" inputmode="numeric" />
                        @error('bank_account_number') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                        <x-form-input name="bank_account_name" label="Chủ tài khoản" :value="old('bank_account_name')" />
                        @error('bank_account_name') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                    </div>
                    <button class="justify-self-start rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-white disabled:opacity-50" @disabled(! $settings['enabled'] || $stats['balance'] < $settings['min_withdraw'])>Gửi yêu cầu rút</button>
                    @if ($stats['balance'] < $settings['min_withdraw'])
                        <p class="text-xs text-muted">Cần tối thiểu {{ $money($settings['min_withdraw']) }} hoa hồng để rút.</p>
                    @endif
                </form>
            </section>

            <section class="rounded-3xl border border-line bg-surface p-6">
                <h2 class="font-display text-xl font-semibold">Hoa hồng gần đây</h2>
                @forelse ($commissions as $commission)
                    <div class="mt-3 flex justify-between gap-3 border-b border-line pb-3 text-sm last:border-0">
                        <span>{{ $maskName($commission->referredUser?->name) }} mua acc {{ $money((float) $commission->order_total) }}
                            <span class="block text-xs text-muted">{{ $commission->created_at->format('d/m/Y H:i') }} · {{ rtrim(rtrim(number_format((float) $commission->rate, 2, ',', '.'), '0'), ',') }}%</span>
                        </span>
                        <span class="font-semibold text-emerald-400">+{{ $commission->formattedAmount() }}</span>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-muted">Chưa có hoa hồng. Chia sẻ link để bắt đầu.</p>
                @endforelse

                <h3 class="mt-6 font-display text-lg font-semibold">Người bạn đã giới thiệu</h3>
                @forelse ($referrals as $referral)
                    <p class="mt-2 text-sm">{{ $maskName($referral->name) }} <span class="text-xs text-muted">· tham gia {{ $referral->created_at->format('d/m/Y') }}</span></p>
                @empty
                    <p class="mt-2 text-sm text-muted">Chưa có ai đăng ký qua link của bạn.</p>
                @endforelse
            </section>
        </div>

        <section class="mt-6 rounded-3xl border border-line bg-surface p-6">
            <h2 class="font-display text-xl font-semibold">Lịch sử rút hoa hồng</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead class="text-xs uppercase tracking-widest text-muted">
                        <tr><th class="py-2">Thời gian</th><th class="py-2">Số tiền</th><th class="py-2">Rút về</th><th class="py-2">Trạng thái</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawalHistory as $withdrawal)
                            <tr class="border-t border-line">
                                <td class="py-2 text-muted">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2 font-semibold">{{ $withdrawal->formattedAmount() }}</td>
                                <td class="py-2">{{ $withdrawal->methodLabel() }}@if ($withdrawal->bank_name) · {{ $withdrawal->bank_name }} {{ $withdrawal->maskedAccountNumber() }}@endif</td>
                                <td class="py-2">{{ $withdrawal->status->label() }}@if ($withdrawal->note)<span class="block text-xs text-muted">{{ $withdrawal->note }}</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-muted">Chưa có yêu cầu rút.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <x-pagination :paginator="$withdrawalHistory" />
        </section>
    </div>
</x-layouts.app>
