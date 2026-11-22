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
@php
    $moneyValue = fn ($value): string => $value === null || $value === '' ? '' : number_format((int) preg_replace('/\D/', '', (string) (is_numeric($value) ? (int) $value : $value)), 0, ',', '.');
@endphp
<fieldset class="grid gap-3 rounded-2xl border border-line p-4" data-price-group>
    <legend class="px-1 text-sm font-medium">Giá & giảm giá</legend>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-form-input name="original_price" label="Giá gốc (₫)" :value="$moneyValue(old('original_price', $product?->original_price))" inputmode="numeric" autocomplete="off" placeholder="VD: 1.500.000" data-money-input data-price-original />
        <x-form-input name="discount_percent" label="Giảm giá (%)" :value="old('discount_percent', $product?->discountPercent())" inputmode="numeric" autocomplete="off" placeholder="VD: 20" maxlength="2" data-price-discount />
        <x-form-input name="price" label="Giá bán (₫)" :value="$moneyValue(old('price', $product?->price))" inputmode="numeric" autocomplete="off" placeholder="VD: 1.200.000" data-money-input data-price-final />
    </div>
    <p class="text-xs text-muted">Gõ số bình thường, dấu chấm tự thêm. Nhập giá gốc + % giảm thì giá bán tự tính; hoặc nhập giá gốc + giá bán thì % tự tính. Không giảm giá thì để trống giá gốc và %.</p>
    @foreach (['price', 'original_price', 'discount_percent'] as $field)
        @error($field) <p class="text-sm text-primary">{{ $message }}</p> @enderror
    @endforeach
</fieldset>
<div class="grid gap-4 md:grid-cols-3">
    <x-form-input name="bp" label="BP (số BP thật, VD: 5.000.000.000)" :value="$moneyValue(old('bp', $product?->bp))" inputmode="numeric" autocomplete="off" placeholder="VD: 5.000.000.000" data-money-input />
    <x-form-input name="ovr" type="number" label="OVR" :value="old('ovr', $product?->ovr)" min="1" />
    <x-form-input name="squad" label="Đội hình" :value="old('squad', $product?->squad)" />
</div>
@foreach (['bp', 'ovr', 'squad', 'category_id'] as $field)
    @error($field) <p class="text-sm text-primary">{{ $message }}</p> @enderror
@endforeach
<div class="grid gap-2 rounded-2xl border border-line p-4" data-image-picker>
    <label class="grid gap-2 text-sm">
        <span class="font-medium">{{ $product ? 'Thêm ảnh acc' : 'Ảnh acc' }} (chọn được nhiều ảnh · jpg, png, webp · tối đa {{ \App\Http\Controllers\Admin\ProductImageController::MAX_FILES }} ảnh, mỗi ảnh ≤ 20MB)</span>
        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="text-sm" data-image-picker-input>
    </label>
    <p class="text-xs text-muted">Giữ Ctrl (hoặc Shift) để chọn nhiều ảnh cùng lúc. {{ $product ? 'Ảnh mới được thêm vào cuối danh sách bên dưới.' : 'Ảnh đầu tiên sẽ là ảnh bìa.' }} Tổng dung lượng mỗi lần lưu nên dưới 25MB.</p>
    <div class="grid grid-cols-3 gap-2 sm:grid-cols-5" data-image-picker-preview></div>
    @error('images') <p class="text-sm text-primary">{{ $message }}</p> @enderror
    @error('images.*') <p class="text-sm text-primary">{{ $message }}</p> @enderror
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
