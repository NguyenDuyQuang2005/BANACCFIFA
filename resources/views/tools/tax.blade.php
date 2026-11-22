<x-layouts.app :title="$tool['name']" :meta-description="$tool['summary']">
    @include('tools._header')

    <div class="mx-auto max-w-3xl px-4 py-8 lg:px-8">
        <section class="rounded-3xl border border-line bg-surface p-6 sm:p-8" data-tax-calculator data-rate="{{ $tax['rate'] }}">
            <label class="grid gap-2 text-sm">
                <span class="font-semibold">Nhập giá cầu thủ (BP)</span>
                <input type="text" inputmode="numeric" autocomplete="off" placeholder="VD: 1.000.000" class="w-full rounded-2xl border border-line bg-canvas px-4 py-4 text-lg outline-none focus:border-accent" data-tax-price>
            </label>

            <div class="mt-6 flex items-center justify-between text-base">
                <span>Thuế {{ $tax['rate'] }}%</span>
                <strong class="text-primary" data-tax-out="tax">-0 BP</strong>
            </div>

            <p class="mt-5 text-sm font-semibold">Ưu đãi tài khoản</p>
            <div class="mt-3 flex flex-wrap gap-2" role="radiogroup" aria-label="Ưu đãi tài khoản">
                @foreach ($tax['perks'] as $perk)
                    <button type="button" role="radio" aria-checked="{{ $perk['percent'] === 0 ? 'true' : 'false' }}" data-tax-perk="{{ $perk['percent'] }}" data-tax-perk-key="{{ $perk['key'] }}"
                        class="rounded-full border px-5 py-2.5 text-sm font-semibold transition {{ $perk['percent'] === 0 ? 'border-primary bg-primary/10 text-primary' : 'border-line bg-canvas text-ink hover:border-accent' }}">
                        {{ $perk['label'] }}@if ($perk['percent'] > 0) -{{ $perk['percent'] }}%@endif
                    </button>
                @endforeach
            </div>

            <p class="mt-5 text-sm font-semibold">Thẻ giảm giá (Coupon)</p>
            <div class="mt-3 flex flex-wrap gap-2" role="radiogroup" aria-label="Thẻ giảm giá">
                @foreach ($tax['coupons'] as $coupon)
                    <button type="button" role="radio" aria-checked="{{ $coupon === 0 ? 'true' : 'false' }}" data-tax-coupon="{{ $coupon }}"
                        class="rounded-full border px-5 py-2.5 text-sm font-semibold transition {{ $coupon === 0 ? 'border-primary bg-primary/10 text-primary' : 'border-line bg-canvas text-ink hover:border-accent' }}">
                        {{ $coupon }}%
                    </button>
                @endforeach
            </div>

            <dl class="mt-6 grid gap-3 border-t border-dashed border-line pt-5 text-base">
                @foreach ($tax['perks'] as $perk)
                    @continue($perk['percent'] === 0)
                    <div class="flex justify-between text-muted"><dt>Giảm từ {{ $perk['label'] }}</dt><dd data-tax-out="perk-{{ $perk['key'] }}">0 BP</dd></div>
                @endforeach
                <div class="flex justify-between text-muted"><dt>Giảm từ Coupon</dt><dd data-tax-out="coupon">+0 BP</dd></div>
            </dl>

            <div class="mt-6 flex items-center justify-between rounded-2xl bg-primary/10 px-6 py-5">
                <span class="text-lg font-semibold">Thu nhập thực nhận</span>
                <strong class="font-display text-2xl text-primary" data-tax-out="net" aria-live="polite">0 BP</strong>
            </div>

            <p class="mt-4 text-xs text-muted">Cách tính: thuế = giá × {{ $tax['rate'] }}%. VIP/SVIP và coupon mỗi loại hoàn lại % tương ứng của tiền thuế. Thực nhận = giá − thuế + phần được giảm. Ví dụ 1.000.000 BP không ưu đãi nhận {{ number_format($example['net'], 0, ',', '.') }} BP.</p>
        </section>
    </div>
</x-layouts.app>
