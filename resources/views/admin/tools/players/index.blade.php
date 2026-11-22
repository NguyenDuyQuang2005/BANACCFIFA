<x-layouts.admin title="Cầu thủ & giờ reset">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-semibold">Cầu thủ & giờ reset</h1>
        <a href="{{ route('admin.tool-players.create') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Thêm cầu thủ</a>
    </div>
    <p class="mt-2 text-sm text-muted">Dùng cho <a href="{{ route('tools.show', 'du-lieu-cau-thu') }}" target="_blank" class="text-accent hover:underline">Dữ liệu cầu thủ</a> và <a href="{{ route('tools.show', 'gio-reset') }}" target="_blank" class="text-accent hover:underline">Giờ reset cầu thủ</a>.</p>

    @if (session('importErrors'))
        <x-alert type="warning" class="mt-4">
            <p class="font-semibold">Các dòng bị bỏ qua:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach (session('importErrors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl border border-line bg-surface p-5">
            <h2 class="font-display text-lg font-semibold">Nhập nhiều cầu thủ từ file CSV</h2>
            <p class="mt-1 text-xs text-muted">
                Cột: <code>{{ implode(', ', \App\Actions\ImportPlayersFromCsvAction::COLUMNS) }}</code>. Bắt buộc: name, position, ovr.
                Trùng tên + mùa thẻ + vị trí sẽ được cập nhật. Ảnh cầu thủ thêm sau ở nút Sửa.
                <a href="{{ route('admin.tool-players.template') }}" class="text-accent hover:underline">Tải file mẫu</a>
            </p>
            <form method="post" action="{{ route('admin.tool-players.import') }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="file" accept=".csv,text/csv" required class="text-sm">
                <button class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Nhập CSV</button>
            </form>
            @error('file') <p class="mt-2 text-sm text-primary">{{ $message }}</p> @enderror
        </section>

        <section class="rounded-3xl border border-line bg-surface p-5">
            <h2 class="font-display text-lg font-semibold">Mùa thẻ</h2>
            <form method="post" action="{{ route('admin.tool-seasons.store') }}" enctype="multipart/form-data" class="mt-3 grid gap-2 sm:grid-cols-[100px_1fr_70px] sm:items-end">
                @csrf
                <x-form-input name="code" label="Mã" :value="old('code')" placeholder="ICON" required maxlength="20" />
                <x-form-input name="name" label="Tên mùa thẻ" :value="old('name')" placeholder="ICON" required maxlength="100" />
                <x-form-input name="sort_order" type="number" label="Thứ tự" :value="old('sort_order', 0)" min="0" required />
                <label class="grid gap-1 text-xs sm:col-span-2"><span class="text-muted">Icon mùa thẻ</span><input type="file" name="icon" accept="image/png,image/jpeg,image/webp,image/gif" class="text-xs"></label>
                <button class="rounded-full border border-line px-4 py-2 text-xs">Thêm mùa</button>
            </form>
            @error('code') <p class="mt-1 text-sm text-primary">{{ $message }}</p> @enderror
            <div class="mt-4 grid gap-2">
                @forelse ($seasons as $season)
                    <form method="post" action="{{ route('admin.tool-seasons.update', $season) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2 text-xs">
                        @csrf
                        @method('put')
                        @if ($season->iconUrl())<img src="{{ $season->iconUrl() }}" alt="" class="h-6 w-auto">@endif
                        <input name="code" value="{{ $season->code }}" class="w-20 rounded border border-line bg-canvas px-2 py-1" aria-label="Mã">
                        <input name="name" value="{{ $season->name }}" class="w-32 rounded border border-line bg-canvas px-2 py-1" aria-label="Tên">
                        <input name="sort_order" type="number" min="0" value="{{ $season->sort_order }}" class="w-14 rounded border border-line bg-canvas px-2 py-1" aria-label="Thứ tự">
                        <input type="file" name="icon" accept="image/png,image/jpeg,image/webp,image/gif" class="w-40" aria-label="Đổi icon">
                        <span class="text-muted">{{ $season->players_count }} cầu thủ</span>
                        <button class="rounded-full border border-line px-3 py-1">Lưu</button>
                        <button form="delete-season-{{ $season->id }}" class="rounded-full border border-line px-3 py-1 text-primary">Xóa</button>
                    </form>
                    <form id="delete-season-{{ $season->id }}" method="post" action="{{ route('admin.tool-seasons.destroy', $season) }}" class="hidden" onsubmit="return confirm('Xóa mùa thẻ này?')">@csrf @method('delete')</form>
                @empty
                    <p class="text-xs text-muted">Chưa có mùa thẻ.</p>
                @endforelse
            </div>
        </section>
    </div>

    <form method="get" class="mt-6 flex max-w-md gap-2">
        <input type="search" name="q" value="{{ $search }}" placeholder="Tìm tên cầu thủ" class="w-full rounded-xl border border-line bg-canvas px-3 py-2 text-sm">
        <button class="rounded-full border border-line px-4 py-2 text-sm">Tìm</button>
    </form>

    <div class="mt-4 overflow-x-auto rounded-3xl border border-line bg-surface">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="text-xs uppercase tracking-widest text-muted">
                <tr><th class="px-4 py-3">OVR</th><th class="px-4 py-3">Cầu thủ</th><th class="px-4 py-3">Tốc độ / Thể lực</th><th class="px-4 py-3">Giờ reset</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    <tr class="border-t border-line {{ $player->is_visible ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3"><span class="text-xs text-muted">{{ $player->position }}</span> <strong>{{ $player->ovr }}</strong></td>
                        <td class="px-4 py-3">@include('tools._player-identity')</td>
                        <td class="px-4 py-3">{{ $player->pace ?? '—' }} / {{ $player->stamina ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $player->resetLabel() ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.tool-players.edit', $player) }}" class="rounded-full border border-line px-3 py-1.5 text-xs">Sửa</a>
                            <form method="post" action="{{ route('admin.tool-players.destroy', $player) }}" class="inline" onsubmit="return confirm('Xóa cầu thủ này?')">
                                @csrf
                                @method('delete')
                                <button class="rounded-full border border-line px-3 py-1.5 text-xs text-primary">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-muted">Chưa có cầu thủ.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$players" />
</x-layouts.admin>
