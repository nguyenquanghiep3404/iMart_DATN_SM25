<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CassoWebhookController extends Controller
{
    
//     public function testSignature()
// {
//     // Dữ liệu từ log cũ, chúng ta giữ nguyên
//     $timestamp = '1751977750671';
//     $casso_signature = 'MxoQQ5JLojyzdnIY6xzskhurRvP16bLLDlVZZlYpwQdqFcIyg1FIzUnQX1Wxr8sm';
//     $rawBody = '{"error":0,"data":{"id":0,"reference":"MA_GIAO_DICH_THU_NGHIEM","description":"giao dich thu nghiem","amount":599000,"runningBalance":25000000,"transactionDateTime":"2025-07-08 19:29:10","accountNumber":"88888888","bankName":"VPBank","bankAbbreviation":"VPB","virtualAccountNumber":"","virtualAccountName":"","counterAccountName":"NGUYEN VAN A","counterAccountNumber":"8888888888","counterAccountBankId":"970415","counterAccountBankName":"VietinBank"}}';
//     $signedPayload = $timestamp . '.' . $rawBody;

//     // === PHẦN TEST SO SÁNH ===

//     // 1. Lấy key từ file .env như bình thường
//     $key_from_env = trim(config('services.casso.webhook_secret'));
//     $signature_from_env = hash_hmac('sha512', $signedPayload, $key_from_env);

//     // 2. Dán trực tiếp key bạn vừa copy ở Bước 1 vào đây
//     $pasted_key = 'MxoQQ5JLojyzdnIY6xzskhurRvP16bLLDlVZZlYpwQdqFcIyg1FIzUnQX1Wxr8sm';
//     $signature_from_pasted_key = hash_hmac('sha512', $signedPayload, $pasted_key);


//     // === HIỂN THỊ KẾT QUẢ ===
//     dd([
//         'DOES_PASTED_KEY_WORK' => hash_equals($signature_from_pasted_key, $casso_signature),
//         'signature_from_pasted_key' => $signature_from_pasted_key,
//         'signature_from_env_key' => $signature_from_env,
//         'casso_signature_from_log' => $casso_signature,
//     ]);
// }

    public function handle(Request $request)
    {
        try {
            $this->verifySignature($request);
        } catch (\Exception $e) {
            Log::error('Casso Webhook V2: ' . $e->getMessage(), [
                'header' => $request->header('x-casso-signature'),
                'ip' => $request->ip(),
            ]);
            abort(401, $e->getMessage());
        }

        // Mở gói dữ liệu với key "content"
        $tempPayload = json_decode($request->getContent(), true);
        $rawBody = $tempPayload['content'] ?? $request->getContent();
        $payload = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Casso Webhook V2: Invalid JSON payload received.');
            return response()->json(['error' => 1, 'msg' => 'Invalid JSON payload.'], 400);
        }
        
        $transactions = $payload['data'] ?? [];
        if (empty($transactions)) {
            return response()->json(['error' => 0, 'msg' => 'No transaction data to process.']);
        }

        foreach ($transactions as $transaction) {
            $this->processTransaction($transaction);
        }

        return response()->json(['error' => 0, 'msg' => 'Webhook processed successfully']);
    }

    private function verifySignature(Request $request)
    {
        $cassoSignatureHeader = $request->header('x-casso-signature');
        $webhookSecret = trim(config('services.casso.webhook_secret'));

        if (!$webhookSecret) throw new \Exception('Webhook Secret is not configured.');
        if (!$cassoSignatureHeader) throw new \Exception('Missing x-casso-signature header.');

        if (!preg_match('/t=(\d+),v1=([a-f0-9]+)/', $cassoSignatureHeader, $matches)) {
            throw new \Exception('Malformed signature header.');
        }
        
        [, $timestamp, $signatureV1] = $matches;

        // "Mở gói" JSON với key là "content"
        $tempPayload = json_decode($request->getContent(), true);
        
        // Lấy giá trị của key 'content', nếu không có thì dùng nội dung gốc
        $rawBody = $tempPayload['content'] ?? $request->getContent(); 

        $signedPayload = $timestamp . '.' . $rawBody;
        $expectedSignature = hash_hmac('sha512', $signedPayload, $webhookSecret);
        
        if (!hash_equals($expectedSignature, $signatureV1)) {
            Log::warning('Casso Webhook V2: Invalid signature.', [
                'reason'               => 'Signature mismatch',
                'casso_signature_v1'   => $signatureV1,
                'expected_signature'   => $expectedSignature,
            ]);
            throw new \Exception('Invalid Signature.');
        }
        
        $tolerance = 300;
        if (time() - ($timestamp / 1000) > $tolerance) {
            Log::warning('Casso Webhook V2: Timestamp is too old.', ['timestamp' => $timestamp]);
        }
    }

    private function processTransaction(array $transaction)
    {
        // ... (Hàm này giữ nguyên, không cần thay đổi)
        $amountReceived = (int) ($transaction['amount'] ?? 0);
        $description = $transaction['description'] ?? '';
        $transactionId = $transaction['tid'] ?? ($transaction['reference'] ?? null);

        if ($amountReceived <= 0 || !$transactionId) {
            return;
        }
        
        $orderCode = $this->parseOrderCode($description);
        if (!$orderCode) {
            return;
        }

        $order = Order::where('order_code', $orderCode)
                        ->where('payment_status', Order::PAYMENT_PENDING)
                        ->first();

        if (!$order) {
            return;
        }
        
        if (Order::where('casso_tid', $transactionId)->exists()) {
            return;
        }

        $orderGrandTotal = (int) $order->grand_total;
        $acceptableDifference = 5000;

        try {
            DB::transaction(function () use ($order, $amountReceived, $orderGrandTotal, $transactionId, $acceptableDifference) {
                $order->casso_tid = $transactionId;
                
                if (abs($orderGrandTotal - $amountReceived) <= $acceptableDifference) {
                    $order->payment_status = 'paid';
                    $order->status = 'processing';
                    $order->admin_note = "Thanh toán thành công qua Casso. Mã GD: {$transactionId}. Thực nhận: " . number_format($amountReceived) . "đ.";
                } elseif ($amountReceived < $orderGrandTotal) {
                    $order->payment_status = 'partial';
                    $order->admin_note = "Khách chuyển thiếu tiền. Yêu cầu {$orderGrandTotal}đ, thực nhận {$amountReceived}đ. Mã GD Casso: {$transactionId}.";
                } else {
                    $order->payment_status = 'overpaid';
                    $order->status = 'processing';
                    $order->admin_note = "Khách chuyển thừa tiền. Yêu cầu {$orderGrandTotal}đ, thực nhận {$amountReceived}đ. Mã GD Casso: {$transactionId}.";
                }
                
                $order->save();
            });
        } catch (\Exception $e) {
            Log::error('Casso Webhook: Failed to process order transaction.', [
                'order_code' => $orderCode,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function parseOrderCode(string $description): ?string
    {
        preg_match('/DH-[A-Z0-9]{10}/i', $description, $matches);
        return $matches[0] ?? null;
    }
}