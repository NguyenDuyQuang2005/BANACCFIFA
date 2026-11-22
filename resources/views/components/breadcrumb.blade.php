@props([
    'items' => [],
])

<nav {{ $attributes->merge(['class' => 'text-sm text-muted']) }} aria-label="Đường dẫn">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                @if (! $loop->first)
                    <span aria-hidden="true">/</span>
                @endif
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="hover:text-accent">{{ $item['label'] }}</a>
                @else
                    <span class="text-ink" @if($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
