<x-layouts.app title="Công cụ FC Online" meta-description="Bộ công cụ FC Online của DINHTIENFCO: tính thuế cầu thủ, đào tạo OVR, ranking, dữ liệu cầu thủ, giờ reset và kỹ thuật.">
    <div class="mx-auto max-w-7xl px-4 py-10 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Công cụ FC Online'],
        ]" />
        <h1 class="mt-6 font-display text-3xl font-semibold">Công cụ FC Online</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Tính thuế, đào tạo OVR, tra cứu kỹ thuật, ranking kỹ năng HLV, dữ liệu cầu thủ và giờ reset trên TTCN.</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($tools as $tool)
                <a href="{{ route('tools.show', $tool['slug']) }}" class="rounded-3xl border border-line bg-surface p-6 transition hover:border-accent/50">
                    <p class="text-xs uppercase tracking-widest text-primary">Công cụ</p>
                    <h2 class="mt-3 font-display text-xl font-semibold">{{ $tool['name'] }}</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">{{ $tool['summary'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.app>
