<x-layouts.admin title="Voucher">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-semibold">Voucher · mã giảm giá</h1>
        <a href="{{ route('admin.vouchers.create') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Tạo voucher</a>
    </div>
    <p class="mt-2 text-sm text-muted">Khách nhập mã trong popup "Mua ngay". Giảm theo % (có thể giới hạn mức tối đa) hoặc số tiền cố định; có thể chỉ định riêng cho 1 thành viên.</p>

    <form method="get" class="mt-6 flex max-w-md gap-2">
        <input type="search" name="q" value="{{ $search }}" placeholder="Tìm mã" class="w-full rounded-xl border border-line bg-canvas px-3 py-2 text-sm uppercase">
        <button class="rounded-full border border-line px-4 py-2 text-sm">Tìm</button>
    </form>

    <div class="mt-6 overflow-x-auto rounded-3xl border border-line bg-surface">
        <table class="w-full min-w-[880px] text-left text-sm">
            <thead class="text-xs uppercase tracking-widest text-muted">
                <tr>
                    <th class="px-4 py-3">Mã</th>
                    <th class="px-4 py-3">Ưu đãi</th>
                    <th class="px-4 py-3">Điều kiện</th>
                    <th class="px-4 py-3">Đã dùng</th>
                    <th class="px-4 py-3">Hiệu lực</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vouchers as $voucher)
                    @php
                        $expired = $voucher->ends_at?->isPast();
                        $usedUp = $voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit;
                        $state = ! $voucher->is_active ? ['Tắt', 'text-muted'] : ($expired ? ['Hết hạn', 'text-muted'] : ($usedUp ? ['Hết lượt', 'text-muted'] : ['Đang chạy', 'text-emerald-400']));
                    @endphp
                    <tr class="border-t border-line align-top">
                        <td class="px-4 py-3">
                            <span class="font-mono font-semibold text-accent">{{ $voucher->code }}</span>
                            <button type="button" class="ml-1 text-xs text-muted hover:text-accent" data-copy="{{ $voucher->code }}">Sao chép</button>
                            @if ($voucher->description)<p class="text-xs text-muted">{{ $voucher->description }}</p>@endif
                        </td>
                        <td class="px-4 py-3">{{ $voucher->label() }}</td>
                        <td class="px-4 py-3 text-xs text-muted">
                            @if ($voucher->min_order) Đơn từ {{ number_format((float) $voucher->min_order, 0, ',', '.') }}₫<br>@endif
                            {{ $voucher->per_user_limit ? $voucher->per_user_limit.' lượt/người' : 'Không giới hạn/người' }}<br>
                            @if ($voucher->user) Chỉ cho {{ $voucher->user->email }} @else Mọi thành viên @endif
                        </td>
                        <td class="px-4 py-3">
                            {{ $voucher->used_count }}{{ $voucher->usage_limit ? ' / '.$voucher->usage_limit : '' }}
                            <span class="block text-xs text-muted">Đã giảm {{ number_format((float) $voucher->discount_given, 0, ',', '.') }}₫</span>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <span class="font-semibold {{ $state[1] }}">{{ $state[0] }}</span>
                            <span class="block text-muted">{{ $voucher->starts_at?->format('d/m/Y H:i') ?? 'Ngay' }} → {{ $voucher->ends_at?->format('d/m/Y H:i') ?? 'Không hạn' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="rounded-full border border-line px-3 py-1.5 text-xs">Sửa</a>
                            <form method="post" action="{{ route('admin.vouchers.destroy', $voucher) }}" class="inline" onsubmit="return confirm('Xóa voucher này?')">
                                @csrf
                                @method('delete')
                                <button class="rounded-full border border-line px-3 py-1.5 text-xs text-primary">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-muted">Chưa có voucher.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$vouchers" />
</x-layouts.admin>
