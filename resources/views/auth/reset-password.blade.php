<x-layouts.app title="Đặt lại mật khẩu" robots="noindex, nofollow">
    <div class="mx-auto max-w-md px-4 py-16">
        <h1 class="font-display text-3xl font-semibold">Đặt lại mật khẩu</h1>
        <form method="post" action="{{ route('password.update') }}" class="mt-8 grid gap-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-form-input name="email" type="email" label="Email" :value="old('email', $email)" required />
            <x-form-input name="password" type="password" label="Mật khẩu mới" required />
            @error('password') <p class="text-sm text-primary">{{ $message }}</p> @enderror
            <x-form-input name="password_confirmation" type="password" label="Nhập lại mật khẩu" required />
            <button type="submit" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">Cập nhật mật khẩu</button>
        </form>
    </div>
</x-layouts.app>
