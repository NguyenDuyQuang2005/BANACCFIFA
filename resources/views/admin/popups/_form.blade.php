@php
    $popup = $popup ?? null;
    $mediaType = old('media_type', $popup?->media_type ?? 'none');
@endphp
<x-form-input name="title" label="Tiêu đề" :value="old('title', $popup?->title)" required maxlength="150" />
@error('title') <p class="text-sm text-primary">{{ $message }}</p> @enderror

<label class="grid gap-2 text-sm">
    <span class="font-medium">Nội dung</span>
    <textarea name="content" rows="4" maxlength="2000" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5">{{ old('content', $popup?->content) }}</textarea>
</label>
@error('content') <p class="text-sm text-primary">{{ $message }}</p> @enderror

<fieldset class="grid gap-3 rounded-2xl border border-line p-4">
    <legend class="px-1 text-sm font-medium">Ảnh / video</legend>
    <div class="flex flex-wrap gap-4 text-sm">
        @foreach (['none' => 'Không có', 'image' => 'Ảnh', 'video' => 'Video'] as $value => $label)
            <label class="flex items-center gap-2">
                <input type="radio" name="media_type" value="{{ $value }}" @checked($mediaType === $value)>
                {{ $label }}
            </label>
        @endforeach
    </div>
    <label class="grid gap-2 text-sm">
        <span class="text-muted">Ảnh (jpg, png, webp, gif · tối đa 20MB)</span>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" class="text-sm">
    </label>
    @error('image') <p class="text-sm text-primary">{{ $message }}</p> @enderror
    <label class="grid gap-2 text-sm">
        <span class="text-muted">Video (mp4, webm · tối đa 20MB)</span>
        <input type="file" name="video" accept="video/mp4,video/webm" class="text-sm">
    </label>
    @error('video') <p class="text-sm text-primary">{{ $message }}</p> @enderror
    @if ($popup?->mediaUrl())
        <div>
            <p class="mb-2 text-xs text-muted">Đang dùng (chọn file mới để thay, hoặc chọn "Không có" để gỡ):</p>
            @if ($popup->media_type === 'video')
                <video src="{{ $popup->mediaUrl() }}" controls muted class="max-h-48 rounded-xl border border-line"></video>
            @else
                <img src="{{ $popup->mediaUrl() }}" alt="{{ $popup->title }}" class="max-h-48 rounded-xl border border-line">
            @endif
        </div>
    @endif
</fieldset>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="grid gap-2">
        <x-form-input name="button_label" label="Chữ trên nút (vd: Vào nhóm Zalo)" :value="old('button_label', $popup?->button_label)" maxlength="60" />
        @error('button_label') <p class="text-sm text-primary">{{ $message }}</p> @enderror
    </div>
    <div class="grid gap-2">
        <x-form-input name="button_url" label="Link nút (https://zalo.me/g/...)" :value="old('button_url', $popup?->button_url)" />
        @error('button_url') <p class="text-sm text-primary">{{ $message }}</p> @enderror
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-form-input name="dismiss_minutes" type="number" label="Ẩn sau khi đóng (phút)" :value="old('dismiss_minutes', $popup?->dismiss_minutes ?? 60)" min="1" max="10080" required />
    <x-form-input name="sort_order" type="number" label="Ưu tiên (số nhỏ hiện trước)" :value="old('sort_order', $popup?->sort_order ?? 0)" min="0" required />
</div>
@error('dismiss_minutes') <p class="text-sm text-primary">{{ $message }}</p> @enderror

<div class="grid gap-4 sm:grid-cols-2">
    <x-form-input name="starts_at" type="datetime-local" label="Bắt đầu hiển thị" :value="old('starts_at', $popup?->starts_at?->format('Y-m-d\TH:i'))" />
    <x-form-input name="ends_at" type="datetime-local" label="Kết thúc hiển thị" :value="old('ends_at', $popup?->ends_at?->format('Y-m-d\TH:i'))" />
</div>
@error('ends_at') <p class="text-sm text-primary">{{ $message }}</p> @enderror

<input type="hidden" name="is_active" value="0">
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $popup?->is_active ?? true))>
    Đang bật
</label>
