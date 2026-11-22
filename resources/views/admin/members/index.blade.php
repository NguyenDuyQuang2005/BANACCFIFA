<x-layouts.admin title="Thành viên">
    <h1 class="font-display text-3xl font-semibold">Thành viên</h1>
    <form method="get" class="mt-6 flex max-w-xl gap-2">
        <input type="search" name="q" value="{{ $search }}" placeholder="Tìm email, tên, số điện thoại" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5 text-sm">
        <button class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Tìm</button>
    </form>
    <div class="mt-8 grid gap-3">
        @forelse ($members as $member)
            <a href="{{ route('admin.members.show', $member) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4 hover:border-accent/50">
                <div>
                    <p class="font-medium">{{ $member->name }} · {{ $member->email }}</p>
                    <p class="text-sm text-muted">{{ $member->role->label() }} · {{ $member->wallet?->formattedBalance() ?? '0₫' }}</p>
                </div>
                @if ($member->isBanned())
                    <span class="rounded-full bg-primary/15 px-3 py-1 text-xs text-primary">Đã khóa</span>
                @endif
            </a>
        @empty
            <p class="text-sm text-muted">Không có thành viên phù hợp.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$members" />
</x-layouts.admin>
