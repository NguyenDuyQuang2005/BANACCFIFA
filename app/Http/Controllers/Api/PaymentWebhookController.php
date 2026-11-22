<?php

namespace App\Http\Controllers\Api;

use App\Actions\ConfirmDepositAction;
use App\Enums\DepositStatus;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentWebhookController extends Controller
{
    public function store(Request $request, ConfirmDepositAction $confirm): JsonResponse
    {
        $secret = (string) config('services.payments.webhook_secret');

        if ($secret === '') {
            return response()->json(['message' => 'Webhook chưa được cấu hình.'], 503);
        }

        $signature = (string) $request->header('X-Payment-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if ($signature === '' || ! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Chữ ký không hợp lệ.'], 403);
        }

        $payload = $request->validate([
            'code' => ['required', 'string'],
            'provider_reference' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:success,failed'],
        ]);

        $existing = Deposit::query()->where('provider_reference', $payload['provider_reference'])->first();

        if ($existing?->isSuccessful()) {
            return response()->json(['message' => 'Đã xử lý.', 'code' => $existing->code]);
        }

        $deposit = $existing ?? Deposit::query()->where('code', $payload['code'])->first();

        if ($deposit === null) {
            return response()->json(['message' => 'Không tìm thấy lệnh nạp.'], 404);
        }

        if ($payload['status'] === 'failed') {
            if (! $deposit->isSuccessful()) {
                $deposit->update([
                    'status' => DepositStatus::Failed,
                    'provider_reference' => $payload['provider_reference'],
                ]);
            }

            return response()->json(['message' => 'Đã ghi nhận thất bại.']);
        }

        try {
            $confirm->handle($deposit, null, $payload['provider_reference']);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'Đã xác nhận nạp tiền.']);
    }
}
