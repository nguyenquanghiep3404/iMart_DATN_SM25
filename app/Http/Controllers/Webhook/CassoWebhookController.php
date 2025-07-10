<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CassoWebhookController extends Controller
{
    /**
     * Handle incoming webhook requests from Casso.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // === BƯỚC 1: LẤY DỮ LIỆU CẦN THIẾT ===

        // Lấy chữ ký từ header của request. VD: "t=1751971899213,v1=..."
        $cassoSignatureHeader = $request->header('x-casso-signature');
        // Lấy secret key từ file config.
        $webhookSecret = config('casso.webhook_secret');
        // Lấy toàn bộ nội dung body của request
        $requestBody = $request->getContent();

        // Kiểm tra xem Webhook Secret đã được cấu hình chưa
        if (!$webhookSecret) {
            Log::error('Casso Webhook V2: Webhook Secret is not configured.');
            abort(500, 'Webhook configuration error.');
        }

        // Kiểm tra xem có chữ ký không
        if (!$cassoSignatureHeader) {
            Log::warning('Casso Webhook V2: Missing x-casso-signature header.');
            abort(401, 'Missing Signature.');
        }

        // === BƯỚC 2: XÁC THỰC CHỮ KÝ (SIGNATURE) THEO CHUẨN V2 ===

        // Tách chuỗi header để lấy timestamp và chữ ký
        $parts = explode(',', $cassoSignatureHeader);
        $timestamp = null;
        $signatureV1 = null;

        foreach ($parts as $part) {
            // Tách từng phần tử theo dấu '='
            $subParts = explode('=', $part, 2);
            if (count($subParts) === 2) {
                if (trim($subParts[0]) === 't') {
                    $timestamp = trim($subParts[1]);
                } elseif (trim($subParts[0]) === 'v1') {
                    $signatureV1 = trim($subParts[1]);
                }
            }
        }

        // Nếu không tìm thấy timestamp hoặc chữ ký -> lỗi
        if (!$timestamp || !$signatureV1) {
            Log::warning('Casso Webhook V2: Malformed signature header.', ['header' => $cassoSignatureHeader]);
            abort(401, 'Malformed Signature.');
        }

        // Tạo chuỗi payload để ký: timestamp + "." + requestBody
        $signedPayload = $timestamp . '.' . $requestBody;

        // FIX: Sử dụng thuật toán 'sha512' theo đúng chữ ký mà Casso cung cấp (dài 128 ký tự).
        $expectedSignature = hash_hmac('sha512', $signedPayload, $webhookSecret);

        // So sánh an toàn hai chuỗi chữ ký
        if (!hash_equals($expectedSignature, $signatureV1)) {
            Log::warning('Casso Webhook V2: Invalid signature.', [
                'reason' => 'Signature mismatch',
                'casso_signature_v1' => $signatureV1,
                'expected_signature' => $expectedSignature,
                'signed_payload' => $signedPayload,
            ]);
            abort(401, 'Invalid Signature.');
        }

        // === BƯỚC 3: XỬ LÝ DỮ LIỆU KHI ĐÃ AN TOÀN ===
        $payload = json_decode($requestBody, true);

        // Kiểm tra nếu JSON không hợp lệ
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Casso Webhook V2: Invalid JSON payload received.');
            return response()->json(['error' => 1, 'msg' => 'Invalid JSON payload.'], 400);
        }

        // Casso có thể gửi một giao dịch hoặc một mảng giao dịch
        $transactions = [];
        if (isset($payload['data'])) {
            // Kiểm tra xem 'data' là một object (giao dịch đơn lẻ) hay array (nhiều giao dịch)
            if (isset($payload['data'][0])) {
                $transactions = $payload['data'];
            } else {
                $transactions[] = $payload['data'];
            }
        }

        if (empty($transactions)) {
            Log::info('Casso Webhook V2: No transaction data to process.');
            return response()->json(['error' => 0, 'msg' => 'No transaction data to process.']);
        }

        foreach ($transactions as $transaction) {
            if (empty($transaction['amount']) || $transaction['amount'] <= 0) {
                continue;
            }

            $orderCode = $transaction['description'] ?? null;
            $amountReceived = $transaction['amount'] ?? 0;
            $transactionId = $transaction['tid'] ?? null;

            if (!$orderCode || !$transactionId) {
                Log::info('Casso Webhook V2: Missing order code or transaction ID.', ['transaction' => $transaction]);
                continue;
            }

            $order = Order::where('order_code', $orderCode)
                          ->where('payment_status', Order::PAYMENT_PENDING)
                          ->first();

            if ($order) {
                if ((int)$order->grand_total === (int)$amountReceived) {
                    DB::transaction(function () use ($order, $transactionId) {
                        $order->payment_status = Order::PAYMENT_PAID;
                        $order->status = Order::STATUS_PROCESSING;
                        $order->admin_note = "Thanh toán thành công qua Casso. Mã GD: " . $transactionId;
                        $order->save();
                    });
                    Log::info('Casso Webhook: Successfully processed order.', ['order_code' => $orderCode]);
                } else {
                    Log::warning('Casso Webhook: Amount mismatch.', [
                        'order_code' => $orderCode,
                        'expected' => $order->grand_total,
                        'received' => $amountReceived,
                    ]);
                    $order->admin_note = "Khách chuyển sai số tiền. Thực nhận: " . number_format($amountReceived) . "đ. Mã GD Casso: " . $transactionId;
                    $order->save();
                }
            } else {
                Log::info('Casso Webhook: Order not found or already processed.', ['order_code' => $orderCode]);
            }
        }

        return response()->json(['error' => 0, 'msg' => 'Webhook processed successfully']);
    }
}
