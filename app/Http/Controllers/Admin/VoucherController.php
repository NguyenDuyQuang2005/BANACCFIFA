<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $search = mb_strtoupper(trim((string) $request->query('q', '')));

        return view('admin.vouchers.index', [
            'search' => $search,
            'vouchers' => Voucher::query()
                ->with('user:id,email')
                ->withSum('redemptions as discount_given', 'discount_amount')
                ->when($search !== '', fn ($query) => $query->where('code', 'like', '%'.$search.'%'))
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.vouchers.form', ['voucher' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $voucher = Voucher::query()->create($this->validated($request));

        return redirect()->route('admin.vouchers.index')->with('status', 'Đã tạo voucher '.$voucher->code.'.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.form', ['voucher' => $voucher->load('user')]);
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($this->validated($request, $voucher));

        return redirect()->route('admin.vouchers.index')->with('status', 'Đã cập nhật voucher '.$voucher->code.'.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->delete();

        return back()->with('status', 'Đã xóa voucher '.$voucher->code.'. Đơn đã dùng mã vẫn giữ nguyên.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Voucher $voucher = null): array
    {
        $request->merge([
            'code' => filled($request->input('code')) ? mb_strtoupper(preg_replace('/\s+/', '', (string) $request->input('code'))) : null,
            ...collect(['value', 'max_discount', 'min_order'])->mapWithKeys(fn (string $field): array => [
                $field => filled($request->input($field)) ? preg_replace('/[^\d]/', '', (string) $request->input($field)) : null,
            ])->all(),
        ]);

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:40', 'regex:/^[A-Z0-9_\-]+$/', Rule::unique('vouchers', 'code')->ignore($voucher)],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in([Voucher::TYPE_PERCENT, Voucher::TYPE_FIXED])],
            'value' => ['required', 'integer', 'min:1', $request->input('type') === Voucher::TYPE_PERCENT ? 'max:100' : 'max:1000000000'],
            'max_discount' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'min_order' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'per_user_limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'user_email' => ['nullable', 'email', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['required', 'boolean'],
        ], [
            'code.regex' => 'Mã chỉ gồm chữ không dấu, số, gạch ngang hoặc gạch dưới.',
        ], [
            'value' => 'mức giảm',
            'max_discount' => 'giảm tối đa',
            'min_order' => 'đơn tối thiểu',
            'usage_limit' => 'tổng lượt dùng',
            'per_user_limit' => 'lượt mỗi người',
            'user_email' => 'email thành viên',
        ]);

        $userId = null;
        if (filled($validated['user_email'] ?? null)) {
            $userId = User::query()->where('email', $validated['user_email'])->value('id');

            if ($userId === null) {
                throw ValidationException::withMessages(['user_email' => 'Không có thành viên dùng email này.']);
            }
        }

        unset($validated['user_email']);

        return [
            ...$validated,
            'code' => $validated['code'] ?? $voucher?->code ?? $this->uniqueCode(),
            'max_discount' => $validated['type'] === Voucher::TYPE_PERCENT ? ($validated['max_discount'] ?? null) : null,
            'user_id' => $userId,
        ];
    }

    private function uniqueCode(): string
    {
        do {
            $code = 'DT'.Str::upper(Str::random(6));
        } while (Voucher::withTrashed()->where('code', $code)->exists());

        return $code;
    }
}
