<x-layouts.admin :title="$skill ? 'Sửa kỹ thuật' : 'Thêm kỹ thuật'">
    <a href="{{ route('admin.tool-skills.index') }}" class="text-sm text-muted hover:text-accent">← Kỹ thuật</a>
    <h1 class="mt-3 font-display text-3xl font-semibold">{{ $skill ? 'Sửa kỹ thuật' : 'Thêm kỹ thuật' }}</h1>
    <form method="post" action="{{ $skill ? route('admin.tool-skills.update', $skill) : route('admin.tool-skills.store') }}" enctype="multipart/form-data" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @if ($skill) @method('put') @endif
        <div class="grid gap-4 sm:grid-cols-[1fr_140px]">
            <x-form-input name="name" label="Tên kỹ thuật" :value="old('name', $skill?->name)" required maxlength="120" />
            <x-form-input name="level" type="number" label="Cấp" :value="old('level', $skill?->level ?? 1)" min="1" max="10" required />
        </div>
        @error('name') <p class="text-sm text-primary">{{ $message }}</p> @enderror
        <x-form-input name="description" label="Mô tả ngắn" :value="old('description', $skill?->description)" maxlength="255" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form-input name="keyboard_keys" label="Phím bàn phím (VD: Giữ SHIFT + ↑ + →)" :value="old('keyboard_keys', $skill?->keyboard_keys)" maxlength="120" />
            <x-form-input name="controller_keys" label="Tay cầm (VD: RB + R↑ R→)" :value="old('controller_keys', $skill?->controller_keys)" maxlength="120" />
        </div>
        <div class="grid gap-2 rounded-2xl border border-line p-4">
            <label class="grid gap-2 text-sm">
                <span class="font-medium">Ảnh GIF / video minh họa (gif, jpg, png, webp, mp4, webm · tối đa 20MB)</span>
                <input type="file" name="media" accept="image/gif,image/jpeg,image/png,image/webp,video/mp4,video/webm" class="text-sm">
            </label>
            @if ($skill?->mediaUrl())
                @if ($skill->isVideo())
                    <video src="{{ $skill->mediaUrl() }}" class="max-h-48 rounded-xl bg-black" autoplay muted loop playsinline></video>
                @else
                    <img src="{{ $skill->mediaUrl() }}" alt="" class="max-h-48 rounded-xl bg-black object-contain">
                @endif
                <label class="flex items-center gap-2 text-sm text-muted"><input type="checkbox" name="remove_media" value="1"> Gỡ file hiện tại</label>
            @endif
            @error('media') <p class="text-sm text-primary">{{ $message }}</p> @enderror
        </div>
        <x-form-input name="sort_order" type="number" label="Thứ tự trong cùng cấp" :value="old('sort_order', $skill?->sort_order ?? 0)" min="0" required />
        <input type="hidden" name="is_visible" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $skill?->is_visible ?? true))> Hiện trên web</label>
        <button class="justify-self-start rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Lưu</button>
    </form>
</x-layouts.admin>
