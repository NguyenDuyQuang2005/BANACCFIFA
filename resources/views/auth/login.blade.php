<x-layouts.app title="Đăng nhập" robots="noindex, nofollow">
    <div class="mx-auto max-w-md px-4 py-16">
        <h1 class="font-display text-3xl font-semibold">Đăng nhập</h1>
        <p class="mt-2 text-sm text-muted">Chưa có tài khoản? <a href="{{ route('register') }}" class="text-accent hover:underline">Đăng ký</a></p>

        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif

        <form method="post" action="{{ route('login') }}" class="mt-8 grid gap-4">
            @csrf
            <x-form-input name="email" type="email" label="Email" :value="old('email')" required autocomplete="username" />
            @error('email') <p class="text-sm text-primary">{{ $message }}</p> @enderror
            <x-form-input name="password" type="password" label="Mật khẩu" required autocomplete="current-password" />
            <label class="flex items-center gap-2 text-sm text-muted">
                <input type="checkbox" name="remember" class="rounded border-line">
                Ghi nhớ đăng nhập
            </label>
            <button type="submit" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">Đăng nhập</button>
        </form>
        <a href="{{ route('password.request') }}" class="mt-4 inline-block text-sm text-muted hover:text-accent">Quên mật khẩu</a>
    </div>
</x-layouts.app>
