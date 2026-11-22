<x-layouts.app title="Quên mật khẩu" robots="noindex, nofollow">
    <div class="mx-auto max-w-md px-4 py-16">
        <h1 class="font-display text-3xl font-semibold">Quên mật khẩu</h1>
        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif
        <form method="post" action="{{ route('password.email') }}" class="mt-8 grid gap-4">
            @csrf
            <x-form-input name="email" type="email" label="Email" :value="old('email')" required />
            @error('email') <p class="text-sm text-primary">{{ $message }}</p> @enderror
            <button type="submit" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">Gửi hướng dẫn</button>
        </form>
    </div>
</x-layouts.app>
