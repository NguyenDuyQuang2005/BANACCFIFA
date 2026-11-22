<form method="get" action="{{ isset($currentCategory) && $currentCategory ? route('catalog.category', $currentCategory) : route('catalog.index') }}" class="grid gap-4 rounded-3xl border border-line bg-surface p-5">
    <x-form-input name="q" label="Mã tài khoản" :value="$filters['q'] ?? ''" placeholder="Nhập mã" />

    <label class="grid gap-2 text-sm">
        <span class="font-medium">Loại tài khoản</span>
        <select name="category" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5 outline-none focus:border-accent">
            <option value="">Tất cả</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
    </label>

    <x-form-input name="price_min" label="Giá từ" type="number" :value="$filters['price_min'] ?? ''" />
    <x-form-input name="price_max" label="Giá đến" type="number" :value="$filters['price_max'] ?? ''" />
    <x-form-input name="bp_min" label="BP từ" type="number" :value="$filters['bp_min'] ?? ''" />
    <x-form-input name="ovr_min" label="OVR từ" type="number" :value="$filters['ovr_min'] ?? ''" />

    <label class="grid gap-2 text-sm">
        <span class="font-medium">Tình trạng</span>
        <select name="status" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5 outline-none focus:border-accent">
            <option value="" @selected(($filters['status'] ?? '') === '')>Tất cả</option>
            <option value="available" @selected(($filters['status'] ?? '') === 'available')>Còn hàng</option>
            <option value="sold" @selected(($filters['status'] ?? '') === 'sold')>Đã bán</option>
        </select>
    </label>

    <label class="grid gap-2 text-sm">
        <span class="font-medium">Sắp xếp</span>
        <select name="sort" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5 outline-none focus:border-accent">
            <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Mới nhất</option>
            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Giá tăng dần</option>
            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Giá giảm dần</option>
        </select>
    </label>

    <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark">Áp dụng</button>
</form>
