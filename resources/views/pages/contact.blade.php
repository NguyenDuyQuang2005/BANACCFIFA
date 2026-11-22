<x-layouts.app title="Liên hệ" meta-description="Liên hệ DINHTIENFCO để được hỗ trợ mua tài khoản FC Online, nạp tiền và các vấn đề giao dịch.">
    <div class="mx-auto max-w-5xl px-4 py-12 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Liên hệ'],
        ]" />
        <h1 class="mt-6 font-display text-3xl font-semibold">Liên hệ {{ config('site.name') }}</h1>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px]">
            <section class="rounded-3xl border border-line bg-surface p-6">
                @if (session('status'))
                    <x-alert type="success" class="mb-6">{{ session('status') }}</x-alert>
                @endif
                <form method="post" action="{{ route('contact.store') }}" class="grid gap-4">
                    @csrf
                    <div class="hidden" aria-hidden="true">
                        <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>
                    <x-form-input name="name" label="Họ tên" :value="old('name', $user?->name)" required maxlength="120" />
                    @error('name') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <x-form-input name="email" type="email" label="Email" :value="old('email', $user?->email)" />
                            @error('email') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-2">
                            <x-form-input name="phone" type="tel" label="Số điện thoại" :value="old('phone', $user?->phone)" />
                            @error('phone') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <x-form-input name="subject" label="Chủ đề" :value="old('subject')" required maxlength="180" />
                    @error('subject') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                    <label class="grid gap-2 text-sm">
                        <span class="font-medium">Nội dung</span>
                        <textarea name="message" rows="6" required minlength="10" maxlength="3000" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5">{{ old('message') }}</textarea>
                    </label>
                    @error('message') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                    <p class="text-xs text-muted">Không gửi mật khẩu, mã OTP hay thông tin đăng nhập tài khoản game qua form này.</p>
                    <button class="justify-self-start rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white">Gửi liên hệ</button>
                </form>
            </section>

            <aside class="grid content-start gap-3 rounded-3xl border border-line bg-surface p-6 text-sm">
                <p class="font-display text-lg font-semibold">Kênh hỗ trợ nhanh</p>
                @foreach (['zalo' => 'Zalo', 'telegram' => 'Telegram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok'] as $key => $label)
                    @if (! empty(config('site.social.'.$key)))
                        <a href="{{ config('site.social.'.$key) }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-line px-4 py-3 hover:border-accent hover:text-accent">{{ $label }}</a>
                    @endif
                @endforeach
                @if (config('site.contact.phone'))
                    <p class="text-muted">Điện thoại: <a href="tel:{{ config('site.contact.phone') }}" class="text-ink">{{ config('site.contact.phone') }}</a></p>
                @endif
                @if (config('site.contact.email'))
                    <p class="text-muted">Email: <a href="mailto:{{ config('site.contact.email') }}" class="text-ink">{{ config('site.contact.email') }}</a></p>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.app>
