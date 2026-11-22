@php
    $product = $product ?? null;
    $players = old('featured_players_text', $product?->featured_players ? implode("\n", $product->featured_players) : '');
@endphp
<label class="grid gap-2 text-sm">
    <span class="font-medium">Danh mục</span>
    <select name="category_id" class="rounded-xl border border-line bg-canvas px-3 py-2.5" required>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
</label>
<x-form-input name="name" label="Tên hiển thị" :value="old('name', $product?->name)" required />
@error('name') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<x-form-input name="code" label="Mã acc (để trống sẽ tự tạo)" :value="old('code', $product?->code)" />
@error('code') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<x-form-input name="price" type="number" label="Giá bán" :value="old('price', $product?->price)" min="0" step="1000" required />
<x-form-input name="original_price" type="number" label="Giá gốc (nếu giảm giá)" :value="old('original_price', $product?->original_price)" min="0" step="1000" />
<div class="grid gap-4 md:grid-cols-3">
    <x-form-input name="bp" type="number" label="BP" :value="old('bp', $product?->bp)" min="0" step="0.01" />
    <x-form-input name="ovr" type="number" label="OVR" :value="old('ovr', $product?->ovr)" min="1" />
    <x-form-input name="squad" label="Đội hình" :value="old('squad', $product?->squad)" />
</div>
<label class="grid gap-2 text-sm">
    <span class="font-medium">Mô tả</span>
    <textarea name="description" rows="4" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5">{{ old('description', $product?->description) }}</textarea>
</label>
<label class="grid gap-2 text-sm">
    <span class="font-medium">Cầu thủ nổi bật (mỗi dòng một tên)</span>
    <textarea name="featured_players_text" rows="4" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5">{{ $players }}</textarea>
</label>
<label class="grid gap-2 text-sm">
    <span class="font-medium">Trạng thái</span>
    <select name="status" class="rounded-xl border border-line bg-canvas px-3 py-2.5">
        @foreach (\App\Enums\ProductStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(old('status', $product?->status?->value ?? 'available') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
</label>
@error('status') <p class="text-sm text-primary">{{ $message }}</p> @enderror
<input type="hidden" name="is_featured" value="0">
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product?->is_featured))>
    Nổi bật trên trang chủ
</label>
<label class="grid gap-2 text-sm">
    <span class="font-medium">Thông tin đăng nhập game (chỉ admin và người mua)</span>
    <textarea name="credentials" rows="4" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5">{{ old('credentials', $product?->credentials) }}</textarea>
</label>
