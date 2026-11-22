<x-layouts.app title="Đăng ký" robots="noindex, nofollow">
    <div class="mx-auto max-w-md px-4 py-16">
        <h1 class="font-display text-3xl font-semibold">Đăng ký thành viên</h1>
        <p class="mt-2 text-sm text-muted">Đã có tài khoản? <a href="{{ route('login') }}" class="text-accent hover:underline">Đăng nhập</a></p>

        <form method="post" action="{{ route('register') }}" class="mt-8 grid gap-4">
            @csrf
            <x-form-input name="name" label="Họ tên" :value="old('name')" required />
            @error('name') <p class="text-sm text-primary">{{ $message }}</p> @enderror
            <x-form-input name="email" type="email" label="Email" :value="old('email')" required />
            @error('email') <p class="text-sm text-primary">{{ $message }}</p> @enderror
            <x-form-input name="phone" label="Số điện thoại" :value="old('phone')" />
            <x-form-input name="password" type="password" label="Mật khẩu" required autocomplete="new-password" />
            @error('password') <p class="text-sm text-primary">{{ $message }}</p> @enderror
            <x-form-input name="password_confirmation" type="password" label="Nhập lại mật khẩu" required />
            @if ($affiliateEnabled)
                <x-form-input name="referral_code" label="Mã giới thiệu (nếu có)" :value="old('referral_code', $referralCode)" maxlength="16" />
                @error('referral_code') <p class="text-sm text-primary">{{ $message }}</p> @enderror
            @endif
            <button type="submit" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">Tạo tài khoản</button>
        </form>
    </div>
</x-layouts.app>
