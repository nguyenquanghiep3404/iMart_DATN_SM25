<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductInventory;
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
        // Tính tổng khối lượng và kích thước
        $items = $cartData['items'];
        $totalWeight = $items->sum(function($item) {
            return ($item->productVariant->weight ?? 0) * $item->quantity;
        });
        $maxLength = $items->max(function($item) {
            return $item->productVariant->dimensions_length ?? 0;
        });
        $maxWidth = $items->max(function($item) {
            return $item->productVariant->dimensions_width ?? 0;
        });
        $totalHeight = $items->sum(function($item) {
            return ($item->productVariant->dimensions_height ?? 0) * $item->quantity;
        });
        return view('users.payments.information', array_merge($cartData, [
            'baseWeight' => $totalWeight > 0 ? $totalWeight : 1000,
            'baseLength' => $maxLength > 0 ? $maxLength : 20,
            'baseWidth' => $maxWidth > 0 ? $maxWidth : 10,
            'baseHeight' => $totalHeight > 0 ? $totalHeight : 10,
        ]));
    }
    /**
     * Xử lý đặt hàng COD
     */
    public function processOrder(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'address_system' => 'required|string|in:new,old',
            'province_code' => 'required|string',
            'ward_code' => 'required|string',
            'district_code' => 'nullable|string', // Chỉ cần cho hệ thống cũ
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

        // Validate địa chỉ dựa trên hệ thống
        if ($request->address_system === 'new') {
            $request->validate([
                'province_code' => 'exists:provinces_new,code',
                'ward_code' => 'exists:wards_new,code',
            ]);
        } else {
            $request->validate([
                'province_code' => 'exists:provinces_old,code',
                'district_code' => 'required|exists:districts_old,code',
                'ward_code' => 'exists:wards_old,code',
            ]);
        }
        // Kiểm tra giỏ hàng
        $cartData = $this->getCartData();
        if ($cartData['items']->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng đang trống.'], 400);
        }
        // Xử lý địa chỉ GHN nếu chọn phương thức GHN
        $ghnProvinceId = null;
        $ghnDistrictId = null;
        $ghnWardCode = null;
        if ($request->shipping_method === 'ghn') {
            $ghnProvinceId = $request->input('ghn_province_id');
            $ghnDistrictId = $request->input('ghn_district_id');
            $ghnWardCode = $request->input('ghn_ward_code');
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
        $shippingFee = $request->has('shipping_fee') ? (int)$request->shipping_fee : $this->calculateShippingFee($request->shipping_method);
        // Format delivery date/time
        $deliveryInfo = $this->formatDeliveryDateTime(
            $request->shipping_method,
            $request->shipping_time
        );

        // Chuẩn bị dữ liệu địa chỉ
        $addressData = $this->prepareAddressData($request);

        // Tạo đơn hàng ngay lập tức với trạng thái "Chờ thanh toán"
        $order = Order::create([
            'user_id' => Auth::id(),
            'guest_id' => !Auth::check() ? session()->getId() : null,
            'order_code' => $orderCode,
            'customer_name' => $request->full_name,
            'customer_email' => $request->email,
            'customer_phone' => $request->phone,
            'shipping_address_line1' => $request->address,
            'shipping_zip_code' => $request->postcode,
            'shipping_country' => 'Vietnam',
            // Địa chỉ giao hàng
            'shipping_address_system' => $request->address_system,
            'shipping_new_province_code' => $addressData['shipping_new_province_code'],
            'shipping_new_ward_code' => $addressData['shipping_new_ward_code'],
            'shipping_old_province_code' => $addressData['shipping_old_province_code'],
            'shipping_old_district_code' => $addressData['shipping_old_district_code'],
            'shipping_old_ward_code' => $addressData['shipping_old_ward_code'],
            'ghn_province_id' => $ghnProvinceId,
            'ghn_district_id' => $ghnDistrictId,
            'ghn_ward_code' => $ghnWardCode,
            // Địa chỉ thanh toán (mặc định giống địa chỉ giao hàng)
            'billing_address_line1' => $request->address,
            'billing_zip_code' => $request->postcode,
            'billing_country' => 'Vietnam',
            'billing_address_system' => $request->address_system,
            'billing_new_province_code' => $addressData['shipping_new_province_code'],
            'billing_new_ward_code' => $addressData['shipping_new_ward_code'],
            'billing_old_province_code' => $addressData['shipping_old_province_code'],
            'billing_old_district_code' => $addressData['shipping_old_district_code'],
            'billing_old_ward_code' => $addressData['shipping_old_ward_code'],
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
            $shippingFee = $request->has('shipping_fee') ? (int)$request->shipping_fee : $this->calculateShippingFee($request->shipping_method);
            // Format delivery date/time
            $deliveryInfo = $this->formatDeliveryDateTime(
                $request->shipping_method,
                $request->shipping_time
            );
            // Chuẩn bị dữ liệu địa chỉ
            $addressData = $this->prepareAddressData($request);

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
                'shipping_zip_code' => $request->postcode,
                'shipping_country' => 'Vietnam',
                'shipping_address_system' => $request->address_system,
                'shipping_new_province_code' => $addressData['shipping_new_province_code'],
                'shipping_new_ward_code' => $addressData['shipping_new_ward_code'],
                'shipping_old_province_code' => $addressData['shipping_old_province_code'],
                'shipping_old_district_code' => $addressData['shipping_old_district_code'],
                'shipping_old_ward_code' => $addressData['shipping_old_ward_code'],
                // Địa chỉ thanh toán (mặc định giống địa chỉ giao hàng)
                'billing_address_line1' => $request->address,
                'billing_zip_code' => $request->postcode,
                'billing_country' => 'Vietnam',
                'billing_address_system' => $request->address_system,
                'billing_new_province_code' => $addressData['shipping_new_province_code'],
                'billing_new_ward_code' => $addressData['shipping_new_ward_code'],
                'billing_old_province_code' => $addressData['shipping_old_province_code'],
                'billing_old_district_code' => $addressData['shipping_old_district_code'],
                'billing_old_ward_code' => $addressData['shipping_old_ward_code'],
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
                $cartable = $item->productVariant ?? $item->cartable;
                $cartableType = $item->cartable_type ?? ProductVariant::class;
                
                // Chỉ kiểm tra và trừ tồn kho cho sản phẩm mới (ProductVariant)
                if ($cartableType === ProductVariant::class) {
                    // Kiểm tra tồn kho
                    if (!$this->checkStockAvailability($cartable, $item->quantity)) {
                        $availableStock = $this->getSellableStock($cartable);
                        throw new \Exception("Sản phẩm {$cartable->product->name} không đủ hàng. Hiện chỉ còn {$availableStock} sản phẩm.");
                    }
                    
                    // Lấy thông tin thuộc tính của variant
                    $variantAttributes = $cartable->attributeValues->mapWithKeys(function ($attrValue) {
                        return [$attrValue->attribute->name => $attrValue->value];
                    })->toArray();
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $cartable->id,
                        'sku' => $cartable->sku,
                        'product_name' => $cartable->product->name,
                        'variant_attributes' => $variantAttributes,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total_price' => $item->price * $item->quantity,
                    ]);

                    // Trừ tồn kho cho sản phẩm mới
                    $this->decrementInventoryStock($cartable, $item->quantity);
                } else {
                    // Sản phẩm cũ hoặc loại khác - không trừ tồn kho
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => null, // Hoặc logic khác tùy theo loại sản phẩm
                        'sku' => $cartable->sku ?? 'OLD-' . $cartable->id,
                        'product_name' => $cartable->product->name,
                        'variant_attributes' => [], // Hoặc logic khác
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total_price' => $item->price * $item->quantity,
                    ]);
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

            // Chuẩn bị dữ liệu địa chỉ
            $addressData = $this->prepareAddressData($request);

            $order = Order::create([
                'user_id' => Auth::id(),
                'guest_id' => !Auth::check() ? session()->getId() : null,
                'order_code' => $orderCode,
                'customer_name' => $request->full_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'shipping_address_line1' => $request->address,
                'shipping_zip_code' => $request->postcode,
                'shipping_country' => 'Vietnam',
                'shipping_address_system' => $request->address_system,
                'shipping_new_province_code' => $addressData['shipping_new_province_code'],
                'shipping_new_ward_code' => $addressData['shipping_new_ward_code'],
                'shipping_old_province_code' => $addressData['shipping_old_province_code'],
                'shipping_old_district_code' => $addressData['shipping_old_district_code'],
                'shipping_old_ward_code' => $addressData['shipping_old_ward_code'],
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
                $cartable = $item->productVariant ?? $item->cartable;
                $cartableType = $item->cartable_type ?? ProductVariant::class;
                
                if (!$cartable) {
                    throw new \Exception("Không tìm thấy sản phẩm cho một mục trong giỏ hàng.");
                }

                // Xử lý dựa trên loại sản phẩm
                if ($cartableType === ProductVariant::class) {
                    // Sản phẩm mới
                    $variantAttributes = $cartable->attributeValues->mapWithKeys(function ($attrValue) {
                        return [$attrValue->attribute->name => $attrValue->value];
                    })->toArray();

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $cartable->id,
                        'sku' => $cartable->sku,
                        'product_name' => $cartable->product->name,
                        'variant_attributes' => $variantAttributes,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total_price' => $item->price * $item->quantity,
                    ]);
                } else {
                    // Sản phẩm cũ hoặc loại khác
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => null,
                        'sku' => $cartable->sku ?? 'OLD-' . $cartable->id,
                        'product_name' => $cartable->product->name,
                        'variant_attributes' => [],
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total_price' => $item->price * $item->quantity,
                    ]);
                }
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

                        // Trừ tồn kho chỉ cho sản phẩm mới
                        foreach ($order->items as $item) {
                            if ($item->product_variant_id) {
                                $variant = ProductVariant::find($item->product_variant_id);
                                if ($variant) {
                                    $this->decrementInventoryStock($variant, $item->quantity);
                                }
                            }
                            // Sản phẩm cũ không cần trừ tồn kho
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
                            if ($item->product_variant_id) {
                                $variant = ProductVariant::find($item->product_variant_id);
                                if ($variant) {
                                    $this->decrementInventoryStock($variant, $item->quantity);
                                }
                            }
                            // Sản phẩm cũ không cần trừ tồn kho
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

            // Chuẩn bị dữ liệu địa chỉ
            $addressData = $this->prepareAddressData($request);

            $order = Order::create([
                'user_id' => Auth::id(),
                'guest_id' => !Auth::check() ? session()->getId() : null,
                'order_code' => $orderCode,
                'customer_name' => $request->full_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'shipping_address_line1' => $request->address,
                'shipping_zip_code' => $request->postcode,
                'shipping_country' => 'Vietnam',
                'shipping_address_system' => $request->address_system,
                'shipping_new_province_code' => $addressData['shipping_new_province_code'],
                'shipping_new_ward_code' => $addressData['shipping_new_ward_code'],
                'shipping_old_province_code' => $addressData['shipping_old_province_code'],
                'shipping_old_district_code' => $addressData['shipping_old_district_code'],
                'shipping_old_ward_code' => $addressData['shipping_old_ward_code'],
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
                $cartable = $item->productVariant ?? $item->cartable;
                $cartableType = $item->cartable_type ?? ProductVariant::class;
                
                if (!$cartable) {
                    throw new \Exception("Không tìm thấy sản phẩm cho một mục trong giỏ hàng.");
                }

                // Xử lý dựa trên loại sản phẩm
                if ($cartableType === ProductVariant::class) {
                    // Sản phẩm mới
                    $variantAttributes = $cartable->attributeValues->mapWithKeys(function ($attrValue) {
                        return [$attrValue->attribute->name => $attrValue->value];
                    })->toArray();

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $cartable->id,
                        'sku' => $cartable->sku,
                        'product_name' => $cartable->product->name,
                        'variant_attributes' => $variantAttributes,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total_price' => $item->price * $item->quantity,
                    ]);
                } else {
                    // Sản phẩm cũ hoặc loại khác
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => null,
                        'sku' => $cartable->sku ?? 'OLD-' . $cartable->id,
                        'product_name' => $cartable->product->name,
                        'variant_attributes' => [],
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total_price' => $item->price * $item->quantity,
                    ]);
                }
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
                    if ($item->product_variant_id) {
                        $variant = ProductVariant::find($item->product_variant_id);
                        if ($variant) {
                            $this->decrementInventoryStock($variant, $item->quantity);
                        }
                    }
                    // Sản phẩm cũ không cần trừ tồn kho
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
                    if ($item->product_variant_id) {
                        $variant = ProductVariant::find($item->product_variant_id);
                        if ($variant) {
                            $this->decrementInventoryStock($variant, $item->quantity);
                        }
                    }
                    // Sản phẩm cũ không cần trừ tồn kho
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
            // User đã đăng nhập - lấy từ database với polymorphic relationship
            $items = $user->cart->items()
                ->with('cartable.product', 'cartable.attributeValues.attribute', 'cartable.primaryImage')
                ->get()
                ->filter(fn($item) => $item->cartable && $item->cartable->product)
                ->map(function ($item) {
                    // Xử lý dựa trên loại sản phẩm
                    if ($item->cartable_type === ProductVariant::class) {
                        // Sản phẩm mới - có thể trừ tồn kho
                        $item->stock_quantity = $this->getSellableStock($item->cartable) ?? 0;
                        $item->productVariant = $item->cartable; // Để tương thích với code cũ
                    } else {
                        // Sản phẩm cũ hoặc loại khác - không trừ tồn kho
                        $item->stock_quantity = 999; // Hoặc logic khác tùy theo loại sản phẩm
                        $item->productVariant = $item->cartable; // Để tương thích với code cũ
                    }
                    return $item;
                });
        } else {
            // Khách vãng lai - lấy từ session
            $sessionCart = session('cart', []);
            $items = collect($sessionCart)->map(function ($data) {
                // Kiểm tra loại sản phẩm từ session
                $cartableType = $data['cartable_type'] ?? ProductVariant::class;
                $cartableId = $data['cartable_id'] ?? $data['variant_id'] ?? null;
                
                if (!$cartableId) {
                    return null;
                }
                
                $cartable = null;
                
                switch ($cartableType) {
                    case ProductVariant::class:
                        $cartable = ProductVariant::with('product', 'attributeValues.attribute', 'primaryImage')->find($cartableId);
                        break;
                    // Có thể thêm các case khác cho sản phẩm cũ
                    // case TradeInItem::class:
                    //     $cartable = TradeInItem::with('product')->find($cartableId);
                    //     break;
                    default:
                        return null;
                }
                
                if (!$cartable || !$cartable->product) {
                    return null;
                }
                
                $stockQuantity = 0;
                if ($cartableType === ProductVariant::class) {
                    $stockQuantity = $this->getSellableStock($cartable);
                } else {
                    $stockQuantity = 999; // Hoặc logic khác cho sản phẩm cũ
                }
                
                return (object) [
                    'id' => $cartableId,
                    'productVariant' => $cartable,
                    'price' => $data['price'],
                    'quantity' => $data['quantity'],
                    'stock_quantity' => $stockQuantity,
                    'cartable_type' => $cartableType,
                ];
            })->filter(fn($item) => $item && $item->productVariant && $item->productVariant->product);
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
        if (!$this->checkStockAvailability($variant, $request->quantity)) {
            $availableStock = $this->getSellableStock($variant);
            return response()->json([
                'success' => false,
                'message' => 'Số lượng vượt quá tồn kho. Hiện chỉ còn ' . $availableStock . ' sản phẩm.'
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
            'address_system' => 'required|string|in:new,old',
            'province_code' => 'required|string',
            'ward_code' => 'required|string',
            'district_code' => 'nullable|string', // Chỉ cần cho hệ thống cũ
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

        // Validate địa chỉ dựa trên hệ thống
        if ($request->address_system === 'new') {
            $request->validate([
                'province_code' => 'exists:provinces_new,code',
                'ward_code' => 'exists:wards_new,code',
            ]);
        } else {
            $request->validate([
                'province_code' => 'exists:provinces_old,code',
                'district_code' => 'required|exists:districts_old,code',
                'ward_code' => 'exists:wards_old,code',
            ]);
        }
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
            $shippingFee = $request->has('shipping_fee') ? (int)$request->shipping_fee : $this->calculateShippingFee($request->shipping_method);
            // Format delivery date/time
            $deliveryInfo = $this->formatDeliveryDateTime(
                $request->shipping_method,
                $request->shipping_time
            );
            // Chuẩn bị dữ liệu địa chỉ
            $addressData = $this->prepareAddressData($request);

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
                'shipping_zip_code' => $request->postcode,
                'shipping_country' => 'Vietnam',
                'shipping_address_system' => $request->address_system,
                'shipping_new_province_code' => $addressData['shipping_new_province_code'],
                'shipping_new_ward_code' => $addressData['shipping_new_ward_code'],
                'shipping_old_province_code' => $addressData['shipping_old_province_code'],
                'shipping_old_district_code' => $addressData['shipping_old_district_code'],
                'shipping_old_ward_code' => $addressData['shipping_old_ward_code'],
                // Địa chỉ thanh toán (mặc định giống địa chỉ giao hàng)
                'billing_address_line1' => $request->address,
                'billing_zip_code' => $request->postcode,
                'billing_country' => 'Vietnam',
                'billing_address_system' => $request->address_system,
                'billing_new_province_code' => $addressData['shipping_new_province_code'],
                'billing_new_ward_code' => $addressData['shipping_new_ward_code'],
                'billing_old_province_code' => $addressData['shipping_old_province_code'],
                'billing_old_district_code' => $addressData['shipping_old_district_code'],
                'billing_old_ward_code' => $addressData['shipping_old_ward_code'],
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
            if (!$this->checkStockAvailability($variant, $item->quantity)) {
                $availableStock = $this->getSellableStock($variant);
                throw new \Exception("Sản phẩm {$variant->product->name} không đủ hàng. Hiện chỉ còn {$availableStock} sản phẩm.");
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
            $this->decrementInventoryStock($variant, $item->quantity);
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
                'cartable' => $variant, // Để tương thích với logic đa hình
                'cartable_type' => ProductVariant::class, // Để tương thích với logic đa hình
                'price' => $buyNowSession['price'],
                'quantity' => $buyNowSession['quantity'],
                'stock_quantity' => $this->getSellableStock($variant),
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

    /**
     * Helper method để chuẩn bị dữ liệu địa chỉ cho cả hệ thống mới và cũ
     */
    private function prepareAddressData(Request $request): array
    {
        $addressData = [
            'shipping_new_province_code' => null,
            'shipping_new_ward_code' => null,
            'shipping_old_province_code' => null,
            'shipping_old_district_code' => null,
            'shipping_old_ward_code' => null,
        ];

        if ($request->address_system === 'new') {
            $addressData['shipping_new_province_code'] = $request->province_code;
            $addressData['shipping_new_ward_code'] = $request->ward_code;
        } else {
            $addressData['shipping_old_province_code'] = $request->province_code;
            $addressData['shipping_old_district_code'] = $request->district_code;
            $addressData['shipping_old_ward_code'] = $request->ward_code;
        }

        return $addressData;
    }

    /**
     * Helper method để kiểm tra tồn kho từ bảng product_inventories
     */
    private function checkStockAvailability(ProductVariant $variant, int $quantity): bool
    {
        if (!$variant->manage_stock) {
            return true;
        }

        $availableStock = $variant->inventories()
            ->where('inventory_type', 'new')
            ->sum('quantity');

        return $availableStock >= $quantity;
    }

    /**
     * Helper method để trừ tồn kho từ bảng product_inventories
     */
    private function decrementInventoryStock(ProductVariant $variant, int $quantity): void
    {
        if (!$variant->manage_stock) {
            return;
        }

        // Lấy tồn kho hàng mới
        $newInventory = $variant->inventories()
            ->where('inventory_type', 'new')
            ->first();

        if ($newInventory && $newInventory->quantity >= $quantity) {
            $newInventory->decrement('quantity', $quantity);
        } else {
            // Nếu không đủ hàng mới, có thể xử lý logic khác ở đây
            // Ví dụ: lấy từ hàng open_box hoặc báo lỗi
            throw new \Exception("Không đủ tồn kho cho sản phẩm {$variant->product->name}");
        }
    }

    /**
     * Helper method để lấy tồn kho có thể bán
     */
    private function getSellableStock(ProductVariant $variant): int
    {
        return $variant->inventories()
            ->where('inventory_type', 'new')
            ->sum('quantity');
    }

    /**
     * Chuẩn hóa tên để so khớp với GHN
     */
    private function normalize($str)
    {
        $str = mb_strtolower($str, 'UTF-8');
        // Loại bỏ các tiền tố hành chính phổ biến
        $str = preg_replace('/\b(tinh|thanh pho|quan|huyen|xa|phuong)\b\s*/u', '', $str);
        $str = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u', 'a', $str);
        $str = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $str);
        $str = preg_replace('/[iíìỉĩị]/u', 'i', $str);
        $str = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $str);
        $str = preg_replace('/[úùủũụưứừửữự]/u', 'u', $str);
        $str = preg_replace('/[ýỳỷỹỵ]/u', 'y', $str);
        $str = preg_replace('/đ/u', 'd', $str);
        $str = preg_replace('/[^a-z0-9 ]/', '', $str);
        return trim($str);
    }

    /**
     * AJAX: Lấy phí ship GHN động (so khớp tên địa chỉ cũ với GHN)
     */
    public function ajaxGhnShippingFee(Request $request)
    {
        $request->validate([
            'province_name' => 'required|string',
            'district_name' => 'required|string',
            'ward_name' => 'required|string',
            'weight' => 'required|integer|min:10',
            'length' => 'nullable|integer|min:1',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
        ]);
        $token = config('services.ghn.token');
        // \Log::info('GHN API - Địa chỉ nhận vào', [
        //     'province_name' => $request->province_name,
        //     'district_name' => $request->district_name,
        //     'ward_name' => $request->ward_name,
        //     'weight' => $request->weight
        // ]);
        // // Log lại config GHN thực tế trước khi gọi API
        // \Log::info('GHN config', [
        //     'api_url' => config('services.ghn.api_url'),
        //     'token' => config('services.ghn.token'),
        //     'shop_id' => config('services.ghn.shop_id'),
        // ]);
        $ghnProvinces = Http::withHeaders(['Token' => $token])
            ->get(config('services.ghn.api_url') . '/shiip/public-api/master-data/province');
        // \Log::info('GHN API - Response province', ['status' => $ghnProvinces->status(), 'body' => $ghnProvinces->body()]);
        $ghnProvinces = $ghnProvinces->json('data');
        if (!is_array($ghnProvinces)) {
            // \Log::error('GHN API - Danh sách tỉnh GHN trả về null hoặc không phải mảng', ['response' => $ghnProvinces]);
            return response()->json(['success' => false, 'message' => 'Không lấy được danh sách tỉnh từ GHN. Vui lòng kiểm tra cấu hình token/shop_id/API_URL.']);
        }
        // \Log::info('GHN API - Danh sách tỉnh GHN', ['provinces' => $ghnProvinces]);
        $provinceId = null;
        $inputNorm = $this->normalize($request->province_name);
        $matchedProvinces = [];
        foreach ($ghnProvinces as $province) {
            if ($this->normalize($province['ProvinceName']) === $inputNorm) {
                $matchedProvinces[] = $province;
            }
            if (!empty($province['NameExtension']) && is_array($province['NameExtension'])) {
                foreach ($province['NameExtension'] as $ext) {
                    if ($this->normalize($ext) === $inputNorm) {
                        $matchedProvinces[] = $province;
                        break;
                    }
                }
            }
        }
        // Ưu tiên bản ghi có ProvinceName = 'Hà Nội'
        foreach ($matchedProvinces as $province) {
            if ($this->normalize($province['ProvinceName']) === 'ha noi') {
                $provinceId = $province['ProvinceID'];
                break;
            }
        }
        // Nếu không có thì lấy bản đầu tiên khớp
        if (!$provinceId && count($matchedProvinces) > 0) {
            $provinceId = $matchedProvinces[0]['ProvinceID'];
        }
        if (!$provinceId) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tỉnh GHN phù hợp']);
        }
        // 2. Lấy danh sách quận/huyện GHN
        $ghnDistricts = Http::withHeaders(['Token' => $token])
            ->post(config('services.ghn.api_url') . '/shiip/public-api/master-data/district', [
                'province_id' => $provinceId
            ]);
        // \Log::info('GHN API - Response district', ['status' => $ghnDistricts->status(), 'body' => $ghnDistricts->body()]);
        $ghnDistricts = $ghnDistricts->json('data');
        // \Log::info('GHN API - Danh sách quận/huyện GHN', ['districts' => $ghnDistricts, 'province_id' => $provinceId]);
        $districtId = null;
        if (is_array($ghnDistricts)) {
            foreach ($ghnDistricts as $district) {
                // \Log::info('So khớp huyện', [
                //     'input' => $this->normalize($request->district_name),
                //     'ghn' => $this->normalize($district['DistrictName']),
                //     'raw_ghn' => $district['DistrictName']
                // ]);
                if ($this->normalize($district['DistrictName']) === $this->normalize($request->district_name)) {
                    $districtId = $district['DistrictID'];
                    break;
                }
            }
        } else {
            // \Log::error('GHN API - Danh sách quận/huyện GHN trả về null hoặc không phải mảng', ['province_id' => $provinceId, 'response' => $ghnDistricts]);
        }
        // 3. Lấy danh sách phường/xã GHN
        $ghnWards = Http::withHeaders(['Token' => $token])
            ->post(config('services.ghn.api_url') . '/shiip/public-api/master-data/ward', [
                'district_id' => $districtId
            ]);
        // \Log::info('GHN API - Response ward', ['status' => $ghnWards->status(), 'body' => $ghnWards->body()]);
        $ghnWards = $ghnWards->json('data');
        // \Log::info('GHN API - Danh sách phường/xã GHN', ['wards' => $ghnWards]);
        $wardCode = null;
        foreach ($ghnWards as $ward) {
            // \Log::info('So khớp xã', [
            //     'input' => $this->normalize($request->ward_name),
            //     'ghn' => $this->normalize($ward['WardName']),
            //     'raw_ghn' => $ward['WardName']
            // ]);
            if ($this->normalize($ward['WardName']) === $this->normalize($request->ward_name)) {
                $wardCode = $ward['WardCode'];
                break;
            }
        }
        if (!$wardCode) {
            // \Log::error('GHN API - Không tìm thấy phường/xã GHN phù hợp', [
            //     'input' => $request->ward_name,
            //     'normalized_input' => $this->normalize($request->ward_name)
            // ]);
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phường/xã GHN phù hợp']);
        }
        // 4. Gọi service GHN lấy phí ship
        $ghn = new \App\Services\GhnService();
        $length = $request->input('length', 20);
        $width = $request->input('width', 10);
        $height = $request->input('height', 10);
        $fee = $ghn->calculateShippingFee((int)$districtId, (string)$wardCode, (int)$request->weight, (int)$length, (int)$width, (int)$height);
        // Nếu $fee là instance của JsonResponse thì lấy giá trị fee thực sự
        if ($fee instanceof \Illuminate\Http\JsonResponse) {
            // \Log::info('GHN API - Phí ship trả về (unwrap)', ['fee' => $data['fee']]);
            $data = $fee->getData(true);
            if (isset($data['fee']) && is_numeric($data['fee'])) {
                // \Log::info('GHN API - Phí ship trả về (unwrap)', ['fee' => $data['fee']]);
                return response()->json(['success' => true, 'fee' => $data['fee']]);
            } else {
                return response()->json(['success' => false, 'message' => $data['message'] ?? 'Không lấy được phí GHN', 'fee' => null]);
            }
        }
        if ($fee !== false && is_numeric($fee)) {
            // \Log::info('GHN API - Phí ship trả về (direct)', ['fee' => $fee]);
            return response()->json(['success' => true, 'fee' => $fee]);
        }
        // \Log::error('GHN API - Không lấy được phí vận chuyển từ GHN', [
        //     'districtId' => $districtId,
        //     'wardCode' => $wardCode,
        //     'weight' => $request->weight
        // ]);
        return response()->json(['success' => false, 'message' => 'Không lấy được phí vận chuyển từ GHN', 'fee' => null]);
    }
}
