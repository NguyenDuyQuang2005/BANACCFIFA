@php
    $method = $method ?? null;
@endphp
<x-form-input name="name" label="Ngân hàng" :value="old('name', $method?->name)" required />
<x-form-input name="account_name" label="Chủ tài khoản" :value="old('account_name', $method?->account_name)" required />
<x-form-input name="account_number" label="Số tài khoản" :value="old('account_number', $method?->account_number)" required />
<x-form-input name="bank_code" label="Mã ngân hàng VietQR (vd: MB, VCB, ACB, TCB, BIDV, VPB, 970422)" :value="old('bank_code', $method?->bank_code)" />
<p class="-mt-2 text-xs text-muted">Có mã này thì mỗi lệnh nạp sẽ có QR riêng, điền sẵn số tiền và nội dung. Để trống thì dùng ảnh QR tĩnh bên dưới.</p>
<x-form-input name="sort_order" type="number" label="Thứ tự" :value="old('sort_order', $method?->sort_order ?? 1)" min="0" required />
<input type="hidden" name="is_active" value="0">
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method?->is_active ?? true))>
    Đang sử dụng
</label>
<label class="grid gap-2 text-sm">
    <span class="font-medium">Ảnh QR (tuỳ chọn · jpg, png, webp · tối đa 20MB)</span>
    <input type="file" name="qr_image" accept="image/*" class="text-sm">
</label>
@if ($method?->qrUrl())
    <img src="{{ $method->qrUrl() }}" alt="QR" class="max-w-xs rounded-2xl border border-line">
@endif
