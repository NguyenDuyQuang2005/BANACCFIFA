<x-layouts.app :title="$title" :meta-description="$message" :robots="$robots ?? 'index, follow'">
    <div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => $heading],
        ]" />
        <h1 class="mt-6 font-display text-3xl font-semibold">{{ $heading }}</h1>
        <x-empty-state class="mt-8" :title="$heading">
            {{ $message }}
        </x-empty-state>
    </div>
</x-layouts.app>
