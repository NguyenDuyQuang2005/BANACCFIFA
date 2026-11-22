@php
    $money = fn ($value): string => $value === null || $value === '' ? '' : number_format((int) preg_replace('/\D/', '', (string) (is_numeric($value) ? (int) $value : $value)), 0, ',', '.');
    $type = old('type', $voucher?->type ?? 'fixed');
@endphp
<x-layouts.admin :title="$voucher ? 'Sửa voucher' : 'Tạo voucher'">
    <a href="{{ route('admin.vouchers.index') }}" class="text-sm text-muted hover:text-accent">← Voucher</a>
    <h1 class="mt-3 font-display text-3xl font-semibold">{{ $voucher ? 'Sửa voucher '.$voucher->code : 'Tạo voucher' }}</h1>

    <form method="post" action="{{ $voucher ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @if ($voucher) @method('put') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form-input name="code" label="Mã (để trống sẽ tự tạo)" :value="old('code', $voucher?->code)" maxlength="40" class="uppercase" placeholder="VD: CHAOMUNG50K" />
            <x-form-input name="description" label="Ghi chú (tuỳ chọn)" :value="old('description', $voucher?->description)" maxlength="255" placeholder="VD: Quà 50 thành viên Zalo" />
        </div>
        @error('code') <p class="text-sm text-primary">{{ $message }}</p> @enderror

        <fieldset class="grid gap-4 rounded-2xl border border-line p-4">
            <legend class="px-1 text-sm font-medium">Mức giảm</legend>
            <div class="flex gap-4 text-sm">
                <label class="flex items-center gap-2"><input type="radio" name="type" value="fixed" @checked($type === 'fixed')> Số tiền cố định (₫)</label>
                <label class="flex items-center gap-2"><input type="radio" name="type" value="percent" @checked($type === 'percent')> Phần trăm (%)</label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form-input name="value" label="Giảm (số tiền hoặc %)" :value="$type === 'percent' ? old('value', $voucher ? (int) $voucher->value : null) : $money(old('value', $voucher?->value))" inputmode="numeric" required placeholder="VD: 50.000 hoặc 10" data-money-input />
                <x-form-input name="max_discount" label="Giảm tối đa (₫, chỉ cho %)" :value="$money(old('max_discount', $voucher?->max_discount))" inputmode="numeric" placeholder="VD: 100.000" data-money-input />
            </div>
            @foreach (['type', 'value', 'max_discount'] as $field)
                @error($field) <p class="text-sm text-primary">{{ $message }}</p> @enderror
            @endforeach
        </fieldset>

        <fieldset class="grid gap-4 rounded-2xl border border-line p-4">
            <legend class="px-1 text-sm font-medium">Điều kiện</legend>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-form-input name="min_order" label="Đơn tối thiểu (₫)" :value="$money(old('min_order', $voucher?->min_order))" inputmode="numeric" placeholder="Không bắt buộc" data-money-input />
                <x-form-input name="usage_limit" type="number" label="Tổng lượt dùng" :value="old('usage_limit', $voucher?->usage_limit)" min="1" placeholder="Không giới hạn" />
                <x-form-input name="per_user_limit" type="number" label="Lượt mỗi người" :value="old('per_user_limit', $voucher?->per_user_limit ?? 1)" min="1" placeholder="Không giới hạn" />
            </div>
            <x-form-input name="user_email" type="email" label="Chỉ dành cho thành viên (email, để trống = ai cũng dùng được)" :value="old('user_email', $voucher?->user?->email)" />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form-input name="starts_at" type="datetime-local" label="Bắt đầu" :value="old('starts_at', $voucher?->starts_at?->format('Y-m-d\TH:i'))" />
                <x-form-input name="ends_at" type="datetime-local" label="Hết hạn" :value="old('ends_at', $voucher?->ends_at?->format('Y-m-d\TH:i'))" />
            </div>
            @foreach (['min_order', 'usage_limit', 'per_user_limit', 'user_email', 'ends_at'] as $field)
                @error($field) <p class="text-sm text-primary">{{ $message }}</p> @enderror
            @endforeach
        </fieldset>

        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $voucher?->is_active ?? true))> Đang bật</label>
        @if ($voucher)
            <p class="text-xs text-muted">Đã dùng {{ $voucher->used_count }} lượt.</p>
        @endif
        <button class="justify-self-start rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Lưu voucher</button>
    </form>
</x-layouts.admin>
