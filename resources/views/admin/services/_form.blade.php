@php
    $service = $service ?? null;
@endphp
<div class="grid gap-4 sm:grid-cols-[160px_1fr]">
    <div class="grid gap-2">
        <x-form-input name="code" label="Mã (vd: ACC279)" :value="old('code', $service?->code)" maxlength="30" />
        @error('code') <p class="text-sm text-primary">{{ $message }}</p> @enderror
    </div>
    <div class="grid gap-2">
        <x-form-input name="name" label="Tên dịch vụ" :value="old('name', $service?->name)" required maxlength="180" />
        @error('name') <p class="text-sm text-primary">{{ $message }}</p> @enderror
    </div>
</div>

<label class="grid gap-2 text-sm">
    <span class="font-medium">Mô tả (mỗi ý một dòng)</span>
    <textarea name="description" rows="5" maxlength="2000" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5">{{ old('description', $service?->description) }}</textarea>
</label>
@error('description') <p class="text-sm text-primary">{{ $message }}</p> @enderror

<div class="grid gap-2 rounded-2xl border border-line p-4">
    <label class="grid gap-2 text-sm">
        <span class="font-medium">Ảnh dịch vụ (jpg, png, webp · tối đa 20MB · nên dùng tỉ lệ 2:1)</span>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="text-sm">
    </label>
    @if ($service?->imageUrl())
        <img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}" class="aspect-[2/1] w-full max-w-sm rounded-xl border border-line object-cover">
        <label class="flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" name="remove_image" value="1">
            Gỡ ảnh hiện tại
        </label>
    @endif
    @error('image') <p class="text-sm text-primary">{{ $message }}</p> @enderror
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="grid gap-2">
        <x-form-input name="contact_label" label="Chữ nút liên hệ" :value="old('contact_label', $service?->contact_label)" placeholder="Liên hệ admin" maxlength="60" />
        @error('contact_label') <p class="text-sm text-primary">{{ $message }}</p> @enderror
    </div>
    <div class="grid gap-2">
        <x-form-input name="contact_url" label="Link liên hệ (để trống = Zalo chung)" :value="old('contact_url', $service?->contact_url)" placeholder="https://zalo.me/..." />
        @error('contact_url') <p class="text-sm text-primary">{{ $message }}</p> @enderror
    </div>
</div>

<x-form-input name="sort_order" type="number" label="Thứ tự" :value="old('sort_order', $service?->sort_order ?? 0)" min="0" required />
<input type="hidden" name="is_visible" value="0">
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $service?->is_visible ?? true))>
    Hiện trên web
</label>
