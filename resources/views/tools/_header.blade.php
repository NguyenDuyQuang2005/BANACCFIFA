<div class="border-b border-line bg-gradient-to-b from-primary/10 to-transparent">
    <div class="mx-auto max-w-7xl px-4 pb-6 pt-8 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Công cụ FC Online', 'url' => route('tools.index')],
            ['label' => $tool['name']],
        ]" />
        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-primary">Công cụ FC Online</p>
        <h1 class="mt-1 font-display text-3xl font-semibold">{{ $tool['name'] }}</h1>
        <p class="mt-2 text-sm text-muted">{{ $tool['summary'] }}</p>
    </div>
</div>
