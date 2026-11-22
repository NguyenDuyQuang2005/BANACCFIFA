<div
    class="fixed inset-0 z-[70] hidden place-items-center bg-black/70 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="buy-modal-title"
    data-buy-modal
    data-auth="{{ auth()->check() ? '1' : '0' }}"
>
    <div class="relative max-h-[92vh] w-full max-w-md overflow-y-auto rounded-3xl border border-line bg-surface shadow-2xl">
        <button type="button" class="absolute right-3 top-3 z-10 grid h-9 w-9 place-items-center rounded-full bg-canvas/80 text-lg text-ink hover:bg-primary hover:text-white" aria-label="Đóng" data-buy-close>×</button>

        <div class="flex items-center gap-4 border-b border-line p-5 pr-14">
            <div class="relative h-16 w-24 shrink-0 overflow-hidden rounded-xl bg-surface-2">
                <img alt="" class="absolute inset-0 hidden h-full w-full object-cover" data-buy-image>
            </div>
            <div class="min-w-0">
                <p class="text-xs uppercase tracking-widest text-muted" data-buy-code></p>
                <h2 id="buy-modal-title" class="truncate font-display text-lg font-semibold" data-buy-name>Mua tài khoản</h2>
            </div>
        </div>

        {{-- Guest --}}
        <section class="hidden p-5" data-buy-step="guest">
            <p class="text-sm text-muted">Bạn cần đăng nhập để mua tài khoản và nhận thông tin đăng nhập game.</p>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <a href="{{ route('login') }}" class="rounded-full bg-primary px-4 py-3 text-center text-sm font-semibold text-white">Đăng nhập</a>
                <a href="{{ route('register') }}" class="rounded-full border border-line px-4 py-3 text-center text-sm font-semibold hover:border-accent">Đăng ký</a>
            </div>
        </section>

        {{-- Loading --}}
        <section class="hidden p-8 text-center text-sm text-muted" data-buy-step="loading">
            <span class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-line border-t-accent" aria-hidden="true"></span>
            <p class="mt-3" data-buy-loading-text>Đang tải…</p>
        </section>

        {{-- Checkout --}}
        <section class="hidden p-5" data-buy-step="checkout">
            <form class="flex gap-2" data-buy-voucher-form>
                <label class="sr-only" for="buy-voucher">Mã giảm giá</label>
                <input id="buy-voucher" type="text" maxlength="40" autocomplete="off" placeholder="Nhập mã giảm giá" class="min-w-0 flex-1 rounded-xl border border-line bg-canvas px-3 py-2.5 text-sm uppercase" data-buy-voucher>
                <button class="rounded-xl border border-accent px-4 py-2.5 text-sm font-semibold text-accent hover:bg-accent hover:text-canvas">Áp dụng</button>
            </form>
            <p class="mt-2 hidden text-xs" data-buy-voucher-note></p>

            <dl class="mt-5 grid gap-2 text-sm">
                <div class="flex justify-between"><dt class="text-muted">Giá acc</dt><dd data-buy-subtotal>—</dd></div>
                <div class="hidden justify-between text-emerald-400" data-buy-discount-row><dt>Giảm giá</dt><dd data-buy-discount>—</dd></div>
                <div class="flex justify-between border-t border-line pt-3 text-base"><dt class="font-semibold">Thanh toán</dt><dd class="font-display text-xl font-semibold text-accent" data-buy-total>—</dd></div>
                <div class="flex justify-between"><dt class="text-muted">Số dư ví</dt><dd data-buy-balance>—</dd></div>
            </dl>

            <div class="mt-4 hidden rounded-2xl border border-primary/40 bg-primary/10 p-4 text-sm" data-buy-shortfall-box>
                Số dư không đủ, còn thiếu <strong data-buy-shortfall></strong>.
                <a href="{{ route('deposit') }}" class="mt-3 block rounded-full bg-primary px-4 py-2.5 text-center font-semibold text-white">Nạp tiền</a>
            </div>

            <p class="mt-4 hidden rounded-2xl border border-primary/40 bg-primary/10 p-3 text-sm text-primary" data-buy-error role="alert"></p>

            <button type="button" class="mt-5 w-full rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark disabled:cursor-not-allowed disabled:opacity-40" data-buy-confirm>
                Xác nhận thanh toán
            </button>
            <p class="mt-2 text-center text-xs text-muted">Số tiền được trừ từ ví trên web.</p>
        </section>

        {{-- Unavailable --}}
        <section class="hidden p-5 text-center" data-buy-step="unavailable">
            <p class="text-sm text-muted" data-buy-unavailable-text>Tài khoản này đã được bán hoặc không còn khả dụng.</p>
            <button type="button" class="mt-5 rounded-full border border-line px-5 py-2.5 text-sm" onclick="window.location.reload()">Tải lại trang</button>
        </section>

        {{-- Success: account info --}}
        <section class="hidden p-5" data-buy-step="success">
            <div class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-4 text-center">
                <p class="font-display text-lg font-semibold text-emerald-400">✓ Mua thành công</p>
                <p class="mt-1 text-sm text-muted">Đơn <strong class="text-ink" data-buy-order-code></strong> · Đã thanh toán <strong class="text-ink" data-buy-paid></strong></p>
            </div>
            <h3 class="mt-5 font-display text-base font-semibold">Thông tin tài khoản game</h3>
            <div class="mt-2 grid gap-3" data-buy-credentials></div>
            <p class="mt-3 text-xs text-muted">Hãy đổi mật khẩu và thông tin bảo mật ngay sau khi đăng nhập. Thông tin này luôn xem lại được trong "Đơn hàng của tôi".</p>
            <p class="mt-2 text-xs text-muted">Số dư còn lại: <strong class="text-ink" data-buy-new-balance></strong></p>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <a href="#" class="rounded-full border border-line px-4 py-2.5 text-center text-sm hover:border-accent" data-buy-order-link>Xem đơn hàng</a>
                <button type="button" class="rounded-full bg-primary px-4 py-2.5 text-sm font-semibold text-white" onclick="window.location.reload()">Xong</button>
            </div>
        </section>
    </div>
</div>
