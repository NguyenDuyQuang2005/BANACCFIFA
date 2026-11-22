<x-layouts.admin title="Liên hệ">
    <h1 class="font-display text-3xl font-semibold">Liên hệ từ khách</h1>
    <nav class="mt-6 flex gap-2 text-sm">
        @foreach (['open' => 'Chưa xử lý', 'handled' => 'Đã xử lý', 'all' => 'Tất cả'] as $key => $label)
            <a href="{{ route('admin.contact-messages.index', ['status' => $key]) }}" class="rounded-full border px-4 py-2 {{ $status === $key ? 'border-accent text-accent' : 'border-line text-muted' }}">{{ $label }}</a>
        @endforeach
    </nav>
    <div class="mt-8 grid gap-3">
        @forelse ($messages as $contact)
            <article class="rounded-2xl border border-line bg-surface p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-medium">{{ $contact->subject }}</p>
                        <p class="text-sm text-muted">
                            {{ $contact->name }}
                            @if ($contact->email) · <a href="mailto:{{ $contact->email }}" class="hover:text-accent">{{ $contact->email }}</a> @endif
                            @if ($contact->phone) · {{ $contact->phone }} @endif
                            · {{ $contact->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <form method="post" action="{{ route('admin.contact-messages.handle', $contact) }}">
                            @csrf
                            <button class="rounded-full border border-line px-4 py-2 text-xs">{{ $contact->isHandled() ? 'Mở lại' : 'Đánh dấu đã xử lý' }}</button>
                        </form>
                        <form method="post" action="{{ route('admin.contact-messages.destroy', $contact) }}" onsubmit="return confirm('Xóa liên hệ này?')">
                            @csrf
                            @method('delete')
                            <button class="rounded-full border border-line px-4 py-2 text-xs text-primary">Xóa</button>
                        </form>
                    </div>
                </div>
                <p class="mt-3 whitespace-pre-line text-sm">{{ $contact->message }}</p>
                @if ($contact->isHandled())
                    <p class="mt-3 text-xs text-muted">Đã xử lý bởi {{ $contact->handler?->email ?? 'admin' }} lúc {{ $contact->handled_at->format('d/m/Y H:i') }}</p>
                @endif
            </article>
        @empty
            <p class="text-sm text-muted">Không có liên hệ.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$messages" />
</x-layouts.admin>
