@php
    $banner = $banner ?? null;
@endphp
<x-form-input name="title" label="Tiêu đề" :value="old('title', $banner?->title)" required maxlength="120" />
@error('title') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<x-form-input name="subtitle" label="Mô tả ngắn" :value="old('subtitle', $banner?->subtitle)" maxlength="255" />
@error('subtitle') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<x-form-input name="link_url" label="Link khi bấm (https://... hoặc /duong-dan)" :value="old('link_url', $banner?->link_url)" />
@error('link_url') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<label class="grid gap-2 text-sm">
    <span class="font-medium">Ảnh banner (jpg, png, webp · tối đa 20MB)</span>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="text-sm">
</label>
@if ($banner?->imageUrl())
    <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="max-h-40 rounded-2xl border border-line object-cover">
@endif
@error('image') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<div class="grid gap-4 sm:grid-cols-2">
    <x-form-input name="starts_at" type="datetime-local" label="Bắt đầu hiển thị" :value="old('starts_at', $banner?->starts_at?->format('Y-m-d\TH:i'))" />
    <x-form-input name="ends_at" type="datetime-local" label="Kết thúc hiển thị" :value="old('ends_at', $banner?->ends_at?->format('Y-m-d\TH:i'))" />
</div>
@error('ends_at') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<x-form-input name="sort_order" type="number" label="Thứ tự" :value="old('sort_order', $banner?->sort_order ?? 0)" min="0" required />
<input type="hidden" name="is_active" value="0">
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner?->is_active ?? true))>
    Đang bật
</label>
