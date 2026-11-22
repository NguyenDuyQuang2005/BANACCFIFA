<x-layouts.app
    title="Dịch vụ FC Online"
    meta-description="Dịch vụ FC Online tại DINHTIENFCO: bơm BP và các dịch vụ hỗ trợ khác. Liên hệ admin qua Zalo để được tư vấn."
>
    <div class="mx-auto max-w-7xl px-4 py-10 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Dịch vụ FCO'],
        ]" />
        <h1 class="mt-6 font-display text-3xl font-semibold text-primary">Dịch vụ FCO</h1>

        @if (config('site.services.notice'))
            <x-zalo-notice class="mt-6" :text="config('site.services.notice')" :url="config('site.social.zalo')" />
        @endif

        @if ($services->isEmpty())
            <x-empty-state class="mt-8" title="Chưa có dịch vụ">
                Dịch vụ sẽ hiện ở đây khi được thêm trong trang quản trị.
            </x-empty-state>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($services as $service)
                    <x-service-card :service="$service" />
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
