<?php

namespace App\Http\Controllers\Api;

use App\Actions\ProcessBankTransferAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives SePay "incoming transfer" webhooks.
 * SePay is configured to send header "Authorization: Apikey <SEPAY_WEBHOOK_API_KEY>".
 */
class SepayWebhookController extends Controller
{
    public function store(Request $request, ProcessBankTransferAction $processTransfer): JsonResponse
    {
        $apiKey = (string) config('services.sepay.webhook_api_key');

        if ($apiKey === '') {
            return response()->json(['success' => false, 'message' => 'Webhook SePay chưa được cấu hình.'], 503);
        }

        $header = (string) $request->header('Authorization', '');

        if (! hash_equals('Apikey '.$apiKey, $header)) {
            return response()->json(['success' => false, 'message' => 'Sai API key.'], 401);
        }

        $payload = $request->validate([
            'id' => ['required'],
            'gateway' => ['nullable', 'string', 'max:60'],
            'transactionDate' => ['nullable', 'date'],
            'accountNumber' => ['nullable', 'string', 'max:40'],
            'code' => ['nullable', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:1000'],
            'transferType' => ['required', 'in:in,out'],
            'transferAmount' => ['required', 'numeric', 'min:0'],
            'referenceCode' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $record = $processTransfer->handle([
            'provider' => 'sepay',
            'provider_transaction_id' => (string) $payload['id'],
            'gateway' => $payload['gateway'] ?? null,
            'account_number' => $payload['accountNumber'] ?? null,
            'amount' => number_format((float) $payload['transferAmount'], 2, '.', ''),
            'content' => $payload['content'] ?? $payload['description'] ?? null,
            'reference_code' => $payload['referenceCode'] ?? null,
            'transacted_at' => $payload['transactionDate'] ?? null,
            'is_incoming' => $payload['transferType'] === 'in',
            'hinted_code' => $payload['code'] ?? null,
            'payload' => $payload,
        ]);

        return response()->json([
            'success' => true,
            'status' => $record->status->value,
        ]);
    }
}
