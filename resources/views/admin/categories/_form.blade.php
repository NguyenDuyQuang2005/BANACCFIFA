@php
    $category = $category ?? null;
@endphp
<x-form-input name="name" label="Tên danh mục" :value="old('name', $category?->name)" required />
@error('name') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<x-form-input name="slug" label="Đường dẫn (để trống sẽ tự tạo)" :value="old('slug', $category?->slug)" />
@error('slug') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<label class="grid gap-2 text-sm">
    <span class="font-medium">Mô tả</span>
    <textarea name="description" rows="3" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5">{{ old('description', $category?->description) }}</textarea>
</label>
<div class="grid gap-2 rounded-2xl border border-line p-4">
    <label class="grid gap-2 text-sm">
        <span class="font-medium">Ảnh banner danh mục (jpg, png, webp · tối đa 20MB · nên dùng tỉ lệ ngang 16:9)</span>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="text-sm">
    </label>
    @if ($category?->imageUrl())
        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" class="aspect-video w-full max-w-sm rounded-xl border border-line object-cover">
        <label class="flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" name="remove_image" value="1">
            Gỡ ảnh hiện tại
        </label>
    @else
        <p class="text-xs text-muted">Chưa có ảnh. Khi chưa có, thẻ danh mục sẽ hiện nền màu thương hiệu với tên danh mục.</p>
    @endif
    @error('image') <p class="text-sm text-primary">{{ $message }}</p> @enderror
</div>
<x-form-input name="notice"label="Dòng thông báo (hiện dưới tiêu đề danh mục)" :value="old('notice', $category?->notice)" />
@error('notice') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<x-form-input name="notice_url" type="url" label="Link liên hệ (Zalo) — để trống sẽ dùng Zalo chung" :value="old('notice_url', $category?->notice_url)" placeholder="https://zalo.me/..." />
@error('notice_url') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<x-form-input name="sort_order" type="number" label="Thứ tự" :value="old('sort_order', $category?->sort_order ?? 0)" min="0" required />
<input type="hidden" name="is_visible" value="0">
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $category?->is_visible ?? true))>
    Hiện trên shop
</label>
