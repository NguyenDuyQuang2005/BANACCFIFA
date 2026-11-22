<x-layouts.admin title="Ranking kỹ năng HLV">
    <h1 class="font-display text-3xl font-semibold">Ranking kỹ năng HLV</h1>
    <p class="mt-2 text-sm text-muted">Hiển thị ở <a href="{{ route('tools.show', 'ranking') }}" class="text-accent hover:underline" target="_blank">/cong-cu/ranking</a>, tự xếp theo % sử dụng từ cao xuống thấp. Cập nhật % theo dữ liệu bạn tự thống kê.</p>

    <form method="post" action="{{ route('admin.tool-coach-skills.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-3 rounded-3xl border border-accent/40 bg-surface p-5 md:grid-cols-[1fr_140px_1fr_auto] md:items-end">
        @csrf
        <x-form-input name="name" label="Tên kỹ năng" :value="old('name')" required maxlength="150" />
        <x-form-input name="usage_percent" type="number" label="% sử dụng" :value="old('usage_percent')" step="0.01" min="0" max="100" required />
        <label class="grid gap-2 text-sm"><span class="font-medium">Icon (tuỳ chọn)</span><input type="file" name="icon" accept="image/png,image/jpeg,image/webp,image/gif" class="text-sm"></label>
        <input type="hidden" name="is_visible" value="1">
        <button class="rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white">Thêm</button>
        @foreach (['name', 'usage_percent', 'icon'] as $field)
            @error($field) <p class="text-sm text-primary md:col-span-4">{{ $message }}</p> @enderror
        @endforeach
    </form>

    <div class="mt-6 grid gap-2">
        @forelse ($coachSkills as $index => $coachSkill)
            <form method="post" action="{{ route('admin.tool-coach-skills.update', $coachSkill) }}" enctype="multipart/form-data" class="grid items-center gap-3 rounded-2xl border border-line bg-surface p-3 md:grid-cols-[40px_48px_1fr_120px_1fr_auto_auto]">
                @csrf
                @method('put')
                <span class="text-sm font-bold text-primary">#{{ $index + 1 }}</span>
                @if ($coachSkill->iconUrl())
                    <img src="{{ $coachSkill->iconUrl() }}" alt="" class="h-10 w-10 object-contain">
                @else
                    <span class="h-10 w-10 rounded-lg bg-surface-2"></span>
                @endif
                <input name="name" value="{{ $coachSkill->name }}" required maxlength="150" class="rounded-lg border border-line bg-canvas px-3 py-2 text-sm" aria-label="Tên kỹ năng">
                <input name="usage_percent" type="number" step="0.01" min="0" max="100" value="{{ (float) $coachSkill->usage_percent }}" required class="rounded-lg border border-line bg-canvas px-3 py-2 text-sm" aria-label="% sử dụng">
                <input type="file" name="icon" accept="image/png,image/jpeg,image/webp,image/gif" class="text-xs" aria-label="Đổi icon">
                <label class="flex items-center gap-1 text-xs text-muted">
                    <input type="hidden" name="is_visible" value="0">
                    <input type="checkbox" name="is_visible" value="1" @checked($coachSkill->is_visible)> Hiện
                </label>
                <span class="flex gap-2">
                    <button class="rounded-full border border-line px-3 py-1.5 text-xs">Lưu</button>
                    <button form="delete-coach-skill-{{ $coachSkill->id }}" class="rounded-full border border-line px-3 py-1.5 text-xs text-primary">Xóa</button>
                </span>
            </form>
            <form id="delete-coach-skill-{{ $coachSkill->id }}" method="post" action="{{ route('admin.tool-coach-skills.destroy', $coachSkill) }}" onsubmit="return confirm('Xóa kỹ năng này?')" class="hidden">
                @csrf
                @method('delete')
            </form>
        @empty
            <p class="text-sm text-muted">Chưa có kỹ năng HLV.</p>
        @endforelse
    </div>
</x-layouts.admin>
