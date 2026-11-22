<x-layouts.admin title="Nhật ký hoạt động">
    <h1 class="font-display text-3xl font-semibold">Nhật ký hoạt động</h1>
    <p class="mt-2 text-sm text-muted">Ghi lại thao tác quản trị: duyệt nạp, điều chỉnh số dư, đổi quyền, khóa tài khoản, sửa danh mục/sản phẩm/ngân hàng/banner, cấu hình.</p>
    <form method="get" class="mt-6 flex max-w-xl gap-2">
        <select name="action" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5 text-sm">
            <option value="">Tất cả hành động</option>
            @foreach ($actions as $item)
                <option value="{{ $item }}" @selected($action === $item)>{{ $item }}</option>
            @endforeach
        </select>
        <button class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Lọc</button>
    </form>
    <div class="mt-8 overflow-x-auto rounded-3xl border border-line bg-surface">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="text-xs uppercase tracking-widest text-muted">
                <tr>
                    <th class="px-4 py-3">Thời gian</th>
                    <th class="px-4 py-3">Người thực hiện</th>
                    <th class="px-4 py-3">Hành động</th>
                    <th class="px-4 py-3">Mô tả</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t border-line align-top">
                        <td class="whitespace-nowrap px-4 py-3 text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $log->user?->email ?? 'Hệ thống' }}</td>
                        <td class="px-4 py-3"><code class="text-accent">{{ $log->action }}</code></td>
                        <td class="px-4 py-3">
                            {{ $log->description }}
                            @if ($log->properties)
                                <p class="mt-1 text-xs text-muted">{{ json_encode($log->properties, JSON_UNESCAPED_UNICODE) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-muted">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-muted">Chưa có nhật ký.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$logs" />
</x-layouts.admin>
