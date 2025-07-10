<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function index()
    {
        // Kiểm tra giỏ hàng có sản phẩm không
        $cartData = $this->getCartData();

        if ($cartData['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }
        return view('users.payments.information', $cartData);
    }
    /**
     * Xử lý đặt hàng COD
     */
    public function processOrder(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'province_code' => 'required|string|exists:provinces,code',
            'ward_code' => 'required|string|exists:wards,code',
            'shipping_method' => 'required|string',
            'shipping_time' => 'nullable|string',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^[0-9]{10,11}$/',
            'email' => 'required|email|max:255',
            'address' => 'required|string|min:5|max:500',
            'postcode' => 'nullable|string|max:10',
            'payment_method' => 'required|string|in:cod,bank_transfer,vnpay,bank_transfer_qr,momo',
            'notes' => 'nullable|string|max:1000',
        ]);
        // Kiểm tra giỏ hàng
        $cartData = $this->getCartData();
        if ($cartData['items']->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng đang trống.'], 400);
        }
        // Nếu là thanh toán VNPay
        if ($request->payment_method === 'vnpay') {
            return $this->createVnpayPayment($request, $cartData);
        }
        // momo
        if ($request->payment_method === 'momo') {
            return $this->createMomoPayment($request, $cartData);
        }

        if ($request->payment_method === 'bank_transfer_qr') {
    try {
        DB::beginTransaction();

        // Tạo mã đơn hàng
        $orderCode = 'DH-' . strtoupper(Str::random(10));
        // Tính toán shipping fee dựa vào phương thức
        $shippingFee = $this->calculateShippingFee($request->shipping_method);
        // Format delivery date/time
        $deliveryInfo = $this->formatDeliveryDateTime(
            $request->shipping_method,
            $request->shipping_time
        );

        // Tạo đơn hàng ngay lập tức với trạng thái "Chờ thanh toán"
        $order = Order::create([
            'user_id' => Auth::id(),
            'guest_id' => !Auth::check() ? session()->getId() : null,
            'order_code' => $orderCode,
            'customer_name' => $request->full_name,
            'customer_email' => $request->email,
            'customer_phone' => $request->phone,
            'shipping_address_line1' => $request->address,
            'shipping_province_code' => $request->province_code,
            'shipping_ward_code' => $request->ward_code,
            'shipping_zip_code' => $request->postcode,
            'shipping_country' => 'Vietnam',
            'sub_total' => $cartData['subtotal'],
            'shipping_fee' => $shippingFee,
            'discount_amount' => $cartData['discount'],
            'grand_total' => $cartData['subtotal'] + $shippingFee - $cartData['discount'],
            'payment_method' => 'bank_transfer_qr', // Đặt phương thức thanh toán
            'payment_status' => Order::PAYMENT_PENDING, // Đặt trạng thái chờ thanh toán
            'shipping_method' => $request->shipping_method,
            'status' => Order::STATUS_PENDING_CONFIRMATION,
            'notes_from_customer' => $request->notes,
            'desired_delivery_date' => $deliveryInfo['date'],
            'desired_delivery_time_slot' => $deliveryInfo['time_slot'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Tạo order items
        foreach ($cartData['items'] as $item) {
            $variant = $item->productVariant ?? ProductVariant::find($item->productVariant->id);
            if (!$variant) {
                throw new \Exception("Không tìm thấy biến thể sản phẩm.");
            }
            $variantAttributes = $variant->attributeValues->mapWithKeys(function ($attrValue) {
                return [$attrValue->attribute->name => $attrValue->value];
            })->toArray();

            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'sku' => $variant->sku,
                'product_name' => $variant->product->name,
                'variant_attributes' => $variantAttributes,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total_price' => $item->price * $item->quantity,
            ]);
        }

        // Xóa giỏ hàng hoặc session "Mua Ngay"
        $this->clearPurchaseSession();
        DB::commit();

        // Trả về một URL để frontend tự chuyển hướng
        return response()->json([
            'success' => true,
            'redirect_url' => route('payments.bank_transfer_qr', ['order' => $order->id])
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
    }
        }
        try {
            DB::beginTransaction();
            // Tạo mã đơn hàng
            $orderCode = 'DH-' . strtoupper(Str::random(10));
            // Tính toán shipping fee dựa vào phương thức
            $shippingFee = $this->calculateShippingFee($request->shipping_method);
            // Format delivery date/time
            $deliveryInfo = $this->formatDeliveryDateTime(
                $request->shipping_method,
                $request->shipping_time
            );
            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => Auth::id(),
                'guest_id' => !Auth::check() ? session()->getId() : null,
                'order_code' => $orderCode,
                'customer_name' => $request->full_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                // Địa chỉ giao hàng
                'shipping_address_line1' => $request->address,
                'shipping_address_line2' => null,
                'shipping_province_code' => $request->province_code,
                'shipping_ward_code' => $request->ward_code,
                'shipping_zip_code' => $request->postcode,
                'shipping_country' => 'Vietnam',
                // Địa chỉ thanh toán (mặc định giống địa chỉ giao hàng)
                'billing_address_line1' => $request->address,
                'billing_province_code' => $request->province_code,
                'billing_ward_code' => $request->ward_code,
                'billing_zip_code' => $request->postcode,
                'billing_country' => 'Vietnam',
                // Thông tin tài chính
                'sub_total' => $cartData['subtotal'],
                'shipping_fee' => $shippingFee,
                'discount_amount' => $cartData['discount'],
                'tax_amount' => 0, // Có thể tính sau
                'grand_total' => $cartData['subtotal'] + $shippingFee - $cartData['discount'],
                // Phương thức thanh toán và vận chuyển
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cod' ? Order::PAYMENT_PENDING : Order::PAYMENT_PENDING,
                'shipping_method' => $request->shipping_method,
                'status' => Order::STATUS_PENDING_CONFIRMATION,
                // Ghi chú và thông tin khác
                'notes_from_customer' => $request->notes,
                'desired_delivery_date' => $deliveryInfo['date'],
                'desired_delivery_time_slot' => $deliveryInfo['time_slot'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            // Tạo order items
            foreach ($cartData['items'] as $item) {
                $variant = $item->productVariant ?? ProductVariant::find($item->productVariant->id);
                // Kiểm tra tồn kho
                if ($variant->manage_stock && $variant->stock_quantity < $item->quantity) {
                    throw new \Exception("Sản phẩm {$variant->product->name} không đủ hàng.");
                }
                // Lấy thông tin thuộc tính của variant
                $variantAttributes = $variant->attributeValues->mapWithKeys(function ($attrValue) {
                    return [$attrValue->attribute->name => $attrValue->value];
                })->toArray();
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'product_name' => $variant->product->name,
                    'variant_attributes' => $variantAttributes,
                    'quantity' => $item->quantity,
                    'sku' => $variant->sku,
                    'price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                ]);

                // Trừ tồn kho nếu có quản lý kho
                if ($variant->manage_stock) {
                    $variant->decrement('stock_quantity', $item->quantity);
                }
            }
            // Lưu thông tin sử dụng coupon nếu có
            if ($cartData['voucher']) {
                CouponUsage::create([
                    'coupon_id' => $cartData['voucher']['id'],
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'usage_date' => now(),
                ]);
            }
            // Xóa giỏ hàng
            $this->clearCart();
            DB::commit();
            // Trả về thông tin đơn hàng
            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'grand_total' => $order->grand_total,
                    'payment_method' => $order->payment_method,
                    'shipping_method' => $order->shipping_method,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'shipping_address' => $order->shipping_full_address_with_type,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    private function createVnpayPayment(Request $request, array $cartData)
    {
        DB::beginTransaction();
        try {
            $orderCode = 'DH-' . strtoupper(Str::random(10));
            $shippingFee = $this->calculateShippingFee($request->shipping_method);
            $grandTotal = $cartData['subtotal'] + $shippingFee - $cartData['discount'];

            $order = Order::create([
                'user_id' => Auth::id(),
                'guest_id' => !Auth::check() ? session()->getId() : null,
                'order_code' => $orderCode,
                'customer_name' => $request->full_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'shipping_address_line1' => $request->address,
                'shipping_province_code' => $request->province_code,
                'shipping_ward_code' => $request->ward_code,
                'sub_total' => $cartData['subtotal'],
                'shipping_fee' => $shippingFee,
                'discount_amount' => $cartData['discount'],
                'grand_total' => $grandTotal,
                'payment_method' => 'vnpay',
                'payment_status' => Order::PAYMENT_PENDING,
                'status' => Order::STATUS_PENDING_CONFIRMATION,
                'notes_from_customer' => $request->notes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            foreach ($cartData['items'] as $item) {
                $variant = $item->productVariant ?? ProductVariant::find($item->productVariant->id);
                if (!$variant) {
                    throw new \Exception("Không tìm thấy biến thể sản phẩm cho một mục trong giỏ hàng.");
                }

                $variantAttributes = $variant->attributeValues->mapWithKeys(function ($attrValue) {
                    return [$attrValue->attribute->name => $attrValue->value];
                })->toArray();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'product_name' => $variant->product->name,
                    'variant_attributes' => $variantAttributes,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                ]);
            }

            $vnp_Url = config('vnpay.url');
            $vnp_Returnurl = url(config('vnpay.return_url'));
            $vnp_TmnCode = config('vnpay.tmn_code');
            $vnp_HashSecret = config('vnpay.hash_secret');
            $vnp_TxnRef = $order->order_code;
            $vnp_OrderInfo = "Thanh toan don hang " . $order->order_code;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $order->grand_total * 100;
            $vnp_Locale = 'vn';
            $vnp_BankCode = '';
            $vnp_IpAddr = $request->ip();

            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            ];

            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đang chuyển hướng đến VNPay...',
                'payment_url' => $vnp_Url
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Xử lý khi VNPay redirect người dùng về
     */
    public function vnpayReturn(Request $request)
    {
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = $request->except('vnp_SecureHash');
        $vnp_HashSecret = config('vnpay.hash_secret');

        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            $order = Order::where('order_code', $request->vnp_TxnRef)->first();

            if ($order) {
                if ($request->vnp_ResponseCode == '00') {
                    // Cập nhật trạng thái đơn hàng thành đã thanh toán
                    // Chỉ cập nhật nếu trạng thái đang là "chờ thanh toán" để tránh xử lý lại
                    if ($order->payment_status == Order::PAYMENT_PENDING) {
                        $order->payment_status = Order::PAYMENT_PAID;
                        $order->save();

                        // Trừ tồn kho
                        foreach ($order->items as $item) {
                            $variant = ProductVariant::find($item->product_variant_id);
                            if ($variant && $variant->manage_stock) {
                                $variant->decrement('stock_quantity', $item->quantity);
                            }
                        }

                        // Xóa giỏ hàng
                        $this->clearCart();
                    }
                    // Chuyển hướng đến trang thành công
                    return redirect()->route('payments.success', ['order_id' => $order->id])
                        ->with('success', 'Thanh toán thành công!');
                } else {
                    // Thanh toán thất bại, có thể xóa đơn hàng hoặc cập nhật trạng thái thất bại
                    $order->status = Order::STATUS_CANCELLED;
                    $order->payment_status = Order::PAYMENT_FAILED;
                    $order->cancellation_reason = 'Thanh toán VNPay thất bại.';
                    $order->save();

                    return redirect()->route('cart.index')->with('error', 'Thanh toán thất bại. Vui lòng thử lại.');
                }
            } else {
                return redirect()->route('cart.index')->with('error', 'Không tìm thấy đơn hàng.');
            }
        } else {
            return redirect()->route('cart.index')->with('error', 'Chữ ký không hợp lệ.');
        }
    }

    /**
     * Xử lý IPN từ VNPay (server-to-server)
     */
    public function vnpayIpn(Request $request)
    {
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = $request->except('vnp_SecureHash');
        $vnp_HashSecret = config('vnpay.hash_secret');

        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            $order = Order::where('order_code', $request->vnp_TxnRef)->first();
            if ($order) {
                if ($request->vnp_ResponseCode == '00' && $request->vnp_TransactionStatus == '00') {
                    if ($order->payment_status == Order::PAYMENT_PENDING) {
                        $order->payment_status = Order::PAYMENT_PAID;
                        $order->save();

                        foreach ($order->items as $item) {
                            $variant = ProductVariant::find($item->product_variant_id);
                            if ($variant && $variant->manage_stock) {
                                $variant->decrement('stock_quantity', $item->quantity);
                            }
                        }
                    }
                } else {
                    $order->status = Order::STATUS_CANCELLED;
                    $order->payment_status = Order::PAYMENT_FAILED;
                    $order->save();
                }
                return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
            }
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }
        return response()->json(['RspCode' => '97', 'Message' => 'Invalid Checksum']);
    }
    private function createMomoPayment(Request $request, array $cartData)
    {
        DB::beginTransaction();
        try {
            $orderCode = 'DH-' . strtoupper(Str::random(10));
            $shippingFee = $this->calculateShippingFee($request->shipping_method);
            $grandTotal = $cartData['subtotal'] + $shippingFee - $cartData['discount'];

            $order = Order::create([
                'user_id' => Auth::id(),
                'guest_id' => !Auth::check() ? session()->getId() : null,
                'order_code' => $orderCode,
                'customer_name' => $request->full_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'shipping_address_line1' => $request->address,
                'shipping_province_code' => $request->province_code,
                'shipping_ward_code' => $request->ward_code,
                'sub_total' => $cartData['subtotal'],
                'shipping_fee' => $shippingFee,
                'discount_amount' => $cartData['discount'],
                'grand_total' => $grandTotal,
                'payment_method' => 'momo',
                'payment_status' => Order::PAYMENT_PENDING,
                'status' => Order::STATUS_PENDING_CONFIRMATION,
                'notes_from_customer' => $request->notes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            foreach ($cartData['items'] as $item) {
                $variant = $item->productVariant ?? ProductVariant::find($item->productVariant->id);
                if (!$variant) {
                    throw new \Exception("Không tìm thấy biến thể sản phẩm cho một mục trong giỏ hàng.");
                }
                $variantAttributes = $variant->attributeValues->mapWithKeys(function ($attrValue) {
                    return [$attrValue->attribute->name => $attrValue->value];
                })->toArray();
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'product_name' => $variant->product->name,
                    'variant_attributes' => $variantAttributes,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                ]);
            }

            $endpoint = config('momo.endpoint');
            $partnerCode = config('momo.partner_code');
            $accessKey = config('momo.access_key');
            $secretKey = config('momo.secret_key');
            $orderInfo = "Thanh toan don hang " . $order->order_code; // <-- SỬA LẠI Ở ĐÂY
            $amount = (string) (int) $order->grand_total;
            $orderId = $order->order_code . "_" . time();
            $requestId = (string) Str::uuid();
            $redirectUrl = config('momo.redirect_url');
            $ipnUrl = config('momo.ipn_url');
            $requestType = "captureWallet";
            $extraData = "";

            $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            $data = [
                'partnerCode' => $partnerCode,
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature,
            ];

            // Ghi log trước khi gửi để kiểm tra
            \Illuminate\Support\Facades\Log::info('Final MoMo Request Data:', $data);

            $response = Http::post($endpoint, $data);
            $jsonResponse = $response->json();

            if (isset($jsonResponse['resultCode']) && $jsonResponse['resultCode'] == 0) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Đang chuyển hướng đến MoMo...',
                    'payment_url' => $jsonResponse['payUrl']
                ]);
            } else {
                \Illuminate\Support\Facades\Log::error('MoMo Creation Error: ', $jsonResponse ?? []);
                throw new \Exception('Lỗi từ MoMo: ' . ($jsonResponse['message'] ?? 'Không xác định'));
            }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
    public function momoReturn(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('MoMo Return Data:', $request->all());
        $secretKey = config('momo.secret_key');
        $momoSignature = $request->signature;

        // Lấy accessKey từ file config
        $accessKey = config('momo.access_key');

        // Tạo chuỗi rawHash theo đúng các trường MoMo yêu cầu cho chữ ký trả về
        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $request->amount .
            "&extraData=" . $request->extraData .
            "&message=" . $request->message .
            "&orderId=" . $request->orderId .
            "&orderInfo=" . $request->orderInfo .
            "&orderType=" . $request->orderType .
            "&partnerCode=" . $request->partnerCode .
            "&payType=" . $request->payType .
            "&requestId=" . $request->requestId .
            "&responseTime=" . $request->responseTime .
            "&resultCode=" . $request->resultCode .
            "&transId=" . $request->transId;

        $expectedSignature = hash_hmac("sha256", $rawHash, $secretKey);

        // Ghi log để so sánh
        \Illuminate\Support\Facades\Log::info('MoMo Return Signature Check', [
            'rawHash' => $rawHash,
            'momo_signature' => $momoSignature,
            'expected_signature' => $expectedSignature
        ]);

        if ($momoSignature !== $expectedSignature) {
            return redirect()->route('cart.index')->with('error', 'Chữ ký không hợp lệ. Giao dịch không được xác nhận.');
        }

        $orderCode = explode("_", $request->orderId)[0];
        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return redirect()->route('cart.index')->with('error', 'Không tìm thấy đơn hàng.');
        }

        if ($request->resultCode == 0) { // Thành công
            if ($order->payment_status == Order::PAYMENT_PENDING) {
                $order->payment_status = Order::PAYMENT_PAID;
                $order->save();
                foreach ($order->items as $item) {
                    $variant = ProductVariant::find($item->product_variant_id);
                    if ($variant && $variant->manage_stock) {
                        $variant->decrement('stock_quantity', $item->quantity);
                    }
                }
                $this->clearPurchaseSession();
            }
            return redirect()->route('payments.success', ['order_id' => $order->id])->with('success', 'Thanh toán thành công!');
        } else { // Thất bại
            if ($order) {
                $order->status = Order::STATUS_CANCELLED;
                $order->payment_status = Order::PAYMENT_FAILED;
                $order->cancellation_reason = $request->message;
                $order->save();
            }
            return redirect()->route('cart.index')->with('error', 'Thanh toán thất bại: ' . $request->message);
        }
    }

    public function momoIpn(Request $request)
    {
        $secretKey = config('momo.secret_key');
        $momoSignature = $request->signature;
        $accessKey = config('momo.access_key');

        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $request->amount .
            "&extraData=" . $request->extraData .
            "&message=" . $request->message .
            "&orderId=" . $request->orderId .
            "&orderInfo=" . $request->orderInfo .
            "&orderType=" . $request->orderType .
            "&partnerCode=" . $request->partnerCode .
            "&payType=" . $request->payType .
            "&requestId=" . $request->requestId .
            "&responseTime=" . $request->responseTime .
            "&resultCode=" . $request->resultCode .
            "&transId=" . $request->transId;

        $expectedSignature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($momoSignature !== $expectedSignature) {
            return response()->json(['resultCode' => 99, 'message' => 'Invalid Signature'], 400);
        }

        $orderCode = explode("_", $request->orderId)[0];
        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return response()->json(['resultCode' => 98, 'message' => 'Order Not Found'], 404);
        }

        if ($request->resultCode == 0) {
            if ($order->payment_status == Order::PAYMENT_PENDING) {
                $order->payment_status = Order::PAYMENT_PAID;
                $order->status = Order::STATUS_PROCESSING;
                $order->save();
                foreach ($order->items as $item) {
                    $variant = ProductVariant::find($item->product_variant_id);
                    if ($variant && $variant->manage_stock) {
                        $variant->decrement('stock_quantity', $item->quantity);
                    }
                }
            }
        } else {
            $order->status = Order::STATUS_CANCELLED;
            $order->payment_status = Order::PAYMENT_FAILED;
            $order->cancellation_reason = 'Thanh toán MoMo thất bại qua IPN.';
            $order->save();
        }

        return response()->json([
            "resultCode" => 0,
            "message" => "Success",
            "responseTime" => now()->timestamp . '000'
        ]);
    }

    private function clearPurchaseSession()
    {
        if (session()->has('buy_now_item')) {
            session()->forget('buy_now_item');
        } else {
            if (Auth::check() && Auth::user()->cart) {
                Auth::user()->cart->items()->delete();
            } else {
                session()->forget('cart');
            }
        }
        session()->forget(['applied_voucher', 'applied_coupon', 'discount']);
    }
public function showBankTransferQr(Order $order)
{
    // Kiểm tra để đảm bảo người dùng chỉ xem được đơn hàng của chính họ
    $isOwner = (Auth::check() && $order->user_id === Auth::id()) || ($order->guest_id && $order->guest_id === session()->getId());

    if (!$isOwner) {
        abort(404);
    }

    return view('users.payments.bank_transfer_qr', compact('order'));
}

    /**
     * Trang thành công
     */
    public function success(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = null;
        if ($orderId) {
            if (Auth::check()) {
                $order = Order::where('id', $orderId)
                    ->where('user_id', Auth::id())
                    ->with(['items.productVariant.product', 'shippingProvince', 'shippingWard'])
                    ->first();
            } else {
                $order = Order::where('id', $orderId)
                    ->where('guest_id', session()->getId())
                    ->with(['items.productVariant.product', 'shippingProvince', 'shippingWard'])
                    ->first();
            }
        }
        return view('users.payments.success', compact('order'));
    }
    /**
     * Lấy dữ liệu giỏ hàng
     */
    private function getCartData()
    {
        $user = auth()->user();
        $items = collect();
        $subtotal = 0;
        $voucher = session('applied_voucher');
        $discount = 0;
        if ($user && $user->cart) {
            // User đã đăng nhập - lấy từ database
            $items = $user->cart->items()
                ->with('productVariant.product', 'productVariant.attributeValues.attribute', 'productVariant.primaryImage')
                ->get()
                ->filter(fn($item) => $item->productVariant && $item->productVariant->product)
                ->map(function ($item) {
                    $item->stock_quantity = $item->productVariant?->stock_quantity ?? 0;
                    return $item;
                });
        } else {
            // Khách vãng lai - lấy từ session
            $sessionCart = session('cart', []);
            $items = collect($sessionCart)->map(function ($data) {
                $variant = ProductVariant::with('product', 'attributeValues.attribute', 'primaryImage')->find($data['variant_id']);
                return (object) [
                    'id' => $data['variant_id'],
                    'productVariant' => $variant,
                    'price' => $data['price'],
                    'quantity' => $data['quantity'],
                    'stock_quantity' => $variant?->stock_quantity ?? 0,
                ];
            })->filter(fn($item) => $item->productVariant && $item->productVariant->product);
        }
        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
        // Tính giảm giá từ voucher
        if ($voucher) {
            $discount = $voucher['type'] === 'percentage'
                ? $subtotal * $voucher['value'] / 100
                : min($voucher['value'], $subtotal);
        }

        $total = max(0, $subtotal - $discount);
        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'voucher' => $voucher,
            'items_count' => $items->count(),
            'total_quantity' => $items->sum('quantity')
        ];
    }
    /**
     * Tính phí vận chuyển
     */
    private function calculateShippingFee($shippingMethod)
    {
        $fees = [
            'Giao hàng nhanh' => 55000,
            'Nhận tại cửa hàng' => 0,
            'Giao hàng tiêu chuẩn' => 25000,
        ];

        return $fees[$shippingMethod] ?? 25000;
    }

    /**
     * Xử lý thông tin thời gian giao hàng
     */
    private function formatDeliveryDateTime($shippingMethod, $shippingTime)
    {
        // Nếu là giao hàng tiêu chuẩn
        if (str_contains(strtolower($shippingMethod), 'giao hàng tiêu chuẩn')) {
            return [
                'date' => 'Dự kiến 3-5 ngày làm việc',
                'time_slot' => null
            ];
        }
        // Nếu không có thời gian được chọn
        if (empty($shippingTime)) {
            return [
                'date' => null,
                'time_slot' => null
            ];
        }
        // Tách ngày và giờ
        $parts = explode(' ', trim($shippingTime));
        // Nếu có đủ thông tin (ví dụ: "Thứ 2 12:00 - 15:00")
        if (count($parts) >= 4) {
            $dayLabel = $parts[0] . ' ' . $parts[1]; // "Thứ 2"
            $timeRange = implode(' ', array_slice($parts, 2)); // "12:00 - 15:00"
            return [
                'date' => $dayLabel,
                'time_slot' => $timeRange
            ];
        }
        // Trả về giá trị mặc định nếu không match pattern
        return [
            'date' => null,
            'time_slot' => null
        ];
    }
    /**
     * Xóa giỏ hàng sau khi đặt hàng thành công
     */
    private function clearCart()
    {
        if (Auth::check()) {
            // Xóa cart items trong database
            $cart = Auth::user()->cart;
            if ($cart) {
                $cart->items()->delete();
            }
        } else {
            // Xóa session cart
            session()->forget('cart');
        }
        // Xóa voucher đã áp dụng
        session()->forget(['applied_voucher', 'applied_coupon', 'discount']);
    }
    /**
     * Tạo phiên Buy Now và chuyển đến trang thanh toán
     */
    public function buyNowCheckout(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_key' => 'nullable|string',
            'quantity' => 'required|integer|min:1|max:5',
        ]);
        $product = Product::findOrFail($request->product_id);
        $variant = null;
        // Tìm variant dựa vào variant_key hoặc lấy variant đầu tiên
        if ($request->variant_key) {
            $variant = ProductVariant::where('product_id', $product->id)->get()
                ->first(function ($variant) use ($request) {
                    $attributes = $variant->attributeValues->pluck('value')->toArray();
                    return implode('_', $attributes) === $request->variant_key;
                });
        }
        if (!$variant) {
            $variant = ProductVariant::where('product_id', $product->id)->first();
        }
        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm chưa có biến thể, vui lòng liên hệ quản trị viên.'
            ], 422);
        }
        // Kiểm tra tồn kho
        if ($variant->manage_stock && $variant->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng vượt quá tồn kho. Hiện chỉ còn ' . $variant->stock_quantity . ' sản phẩm.'
            ], 422);
        }
        // Tính giá hiện tại sale price hoặc regular price
        $now = now();
        $isOnSale = $variant->sale_price &&
            (!$variant->sale_price_starts_at || $variant->sale_price_starts_at <= $now) &&
            (!$variant->sale_price_ends_at || $variant->sale_price_ends_at >= $now);
        $finalPrice = $isOnSale ? $variant->sale_price : $variant->price;
        // Tạo session buy now tạm thời tách biệt với cart thông thường
        session()->put('buy_now_session', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'name' => $product->name,
            'price' => $finalPrice,
            'quantity' => $request->quantity,
            'image' => $variant->image_url,
            'created_at' => now()->timestamp
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Chuyển đến trang thanh toán...',
            'redirect_url' => route('buy-now.information')
        ]);
    }
    /**
     * Hiển thị trang thanh toán cho Buy Now
     */
    public function buyNowInformation()
    {
        // Kiểm tra có session Buy Now không
        if (!session()->has('buy_now_session')) {
            return redirect()->route('cart.index')->with('error', 'Phiên mua hàng đã hết hạn.');
        }
        // Lấy dữ liệu từ session Buy Now
        $buyNowData = $this->getBuyNowData();
        if (!$buyNowData['items'] || $buyNowData['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Không tìm thấy sản phẩm.');
        }
        // Thêm flag để template biết đây là Buy Now
        $buyNowData['is_buy_now'] = true;
        return view('users.payments.information', $buyNowData);
    }

    /**
     * Xử lý đặt hàng Buy Now
     */
    public function processBuyNowOrder(Request $request)
    {
        // Validate dữ liệu (giống như processOrder)
        $request->validate([
            'province_code' => 'required|string|exists:provinces,code',
            'ward_code' => 'required|string|exists:wards,code',
            'shipping_method' => 'required|string',
            'shipping_time' => 'nullable|string',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^[0-9]{10,11}$/',
            'email' => 'required|email|max:255',
            'address' => 'required|string|min:5|max:500',
            'postcode' => 'nullable|string|max:10',
            'payment_method' => 'required|string|in:cod,bank_transfer,vnpay',
            'notes' => 'nullable|string|max:1000',
        ]);
        // Kiểm tra session Buy Now
        if (!session()->has('buy_now_session')) {
            return response()->json(['success' => false, 'message' => 'Phiên mua hàng đã hết hạn.'], 400);
        }
        $buyNowData = $this->getBuyNowData();
        if (!$buyNowData['items'] || $buyNowData['items']->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm.'], 400);
        }
        try {
            DB::beginTransaction();
            // Tạo mã đơn hàng
            $orderCode = 'DH-' . strtoupper(Str::random(10));
            // Tính toán shipping fee
            $shippingFee = $this->calculateShippingFee($request->shipping_method);
            // Format delivery date/time
            $deliveryInfo = $this->formatDeliveryDateTime(
                $request->shipping_method,
                $request->shipping_time
            );
            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => Auth::id(),
                'guest_id' => !Auth::check() ? session()->getId() : null,
                'order_code' => $orderCode,
                'customer_name' => $request->full_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                // Địa chỉ giao hàng
                'shipping_address_line1' => $request->address,
                'shipping_address_line2' => null,
                'shipping_province_code' => $request->province_code,
                'shipping_ward_code' => $request->ward_code,
                'shipping_zip_code' => $request->postcode,
                'shipping_country' => 'Vietnam',
                // Địa chỉ thanh toán (mặc định giống địa chỉ giao hàng)
                'billing_address_line1' => $request->address,
                'billing_province_code' => $request->province_code,
                'billing_ward_code' => $request->ward_code,
                'billing_zip_code' => $request->postcode,
                'billing_country' => 'Vietnam',
                // Thông tin tài chính
                'sub_total' => $buyNowData['subtotal'],
                'shipping_fee' => $shippingFee,
                'discount_amount' => 0, // Buy Now không áp dụng voucher
                'tax_amount' => 0,
                'grand_total' => $buyNowData['subtotal'] + $shippingFee,
                // Phương thức thanh toán và vận chuyển
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cod' ? Order::PAYMENT_PENDING : Order::PAYMENT_PENDING,
                'shipping_method' => $request->shipping_method,
                'status' => Order::STATUS_PENDING_CONFIRMATION,
                // Ghi chú và thông tin khác
                'notes_from_customer' => $request->notes,
                'desired_delivery_date' => $deliveryInfo['date'],
                'desired_delivery_time_slot' => $deliveryInfo['time_slot'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            // Tạo order item (chỉ có 1 sản phẩm trong Buy Now)
            $item = $buyNowData['items']->first();
            $variant = $item->productVariant;
            // Kiểm tra tồn kho lần cuối
            if ($variant->manage_stock && $variant->stock_quantity < $item->quantity) {
                throw new \Exception("Sản phẩm {$variant->product->name} không đủ hàng.");
            }
            // Lấy thông tin thuộc tính của variant
            $variantAttributes = $variant->attributeValues->mapWithKeys(function ($attrValue) {
                return [$attrValue->attribute->name => $attrValue->value];
            })->toArray();
            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product->name,
                'variant_attributes' => $variantAttributes,
                'quantity' => $item->quantity,
                'sku' => $variant->sku,
                'price' => $item->price,
                'total_price' => $item->price * $item->quantity,
            ]);
            // Trừ tồn kho nếu có quản lý kho
            if ($variant->manage_stock) {
                $variant->decrement('stock_quantity', $item->quantity);
            }
            // Xóa session Buy Now
            $this->clearBuyNowSession();
            DB::commit();
            // Trả về thông tin đơn hàng
            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'grand_total' => $order->grand_total,
                    'payment_method' => $order->payment_method,
                    'shipping_method' => $order->shipping_method,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'shipping_address' => $order->shipping_full_address_with_type,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Lấy dữ liệu giỏ hàng cho Buy Now
     */
    private function getBuyNowData()
    {
        $buyNowSession = session('buy_now_session');
        if (!$buyNowSession) {
            return ['items' => collect(), 'subtotal' => 0, 'discount' => 0, 'total' => 0];
        }
        $product = Product::findOrFail($buyNowSession['product_id']);
        $variant = ProductVariant::findOrFail($buyNowSession['variant_id']);
        $items = collect([
            (object)[
                'id' => $variant->id,
                'productVariant' => $variant,
                'price' => $buyNowSession['price'],
                'quantity' => $buyNowSession['quantity'],
                'stock_quantity' => $variant->stock_quantity,
            ]
        ]);
        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
        $discount = 0; // Buy Now không áp dụng voucher
        $total = max(0, $subtotal - $discount);
        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'voucher' => null,
            'items_count' => $items->count(),
            'total_quantity' => $items->sum('quantity')
        ];
    }
    /**
     * Xóa session Buy Now
     */
    private function clearBuyNowSession()
    {
        session()->forget('buy_now_session');
    }
}
