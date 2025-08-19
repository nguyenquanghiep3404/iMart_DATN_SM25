<?php

namespace App\Http\Controllers\Users;

use App\Models\Cart;
use App\Models\Ward;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Province;
use App\Models\OrderItem;
use App\Models\CouponUsage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StoreLocation;
use App\Models\ProductVariant;
use App\Models\LoyaltyPointLog;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\PaymentRequest;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\OrderFulfillment;
use App\Services\AutoStockTransferService;
use App\Models\OrderFulfillmentItem;
use App\Services\FulfillmentService;
use App\Services\InventoryCommitmentService;

class PaymentController extends Controller
{
    public function index()
    {
        // 1. Lấy toàn bộ dữ liệu giỏ hàng đã được tính toán chính xác từ hàm getCartData()
        $cartData = $this->getCartData();

        if ($cartData['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('toast_error', 'Giỏ hàng của bạn đang trống.');
        }
        $items = $cartData['items'];

        // 2. Kiểm tra tồn kho ngay khi tiến hành thanh toán
        $insufficientStock = [];
        foreach ($items as $item) {
            $availableStock = $item->productVariant->inventories()
                ->where('inventory_type', 'new')
                ->selectRaw('COALESCE(SUM(quantity - quantity_committed),0) as available_stock')
                ->value('available_stock');

            if ($item->quantity > $availableStock) {
                $insufficientStock[] = [
                    'name' => $item->productVariant->product->name,
                    'variant' => $item->productVariant->attributeValues->pluck('value')->implode(', '),
                    'requested' => $item->quantity,
                    'available' => $availableStock,
                ];
            }
        }

        if (!empty($insufficientStock)) {
            $messages = collect($insufficientStock)->map(function($item){
                if ($item['available'] == 0) {
                    // Hết hàng
                    return "Sản phẩm {$item['name']}" 
                        . (!empty($item['variant']) ? " ({$item['variant']})" : "") 
                        . " hiện đã hết hàng, vui lòng xóa sản phẩm khỏi giỏ hàng!";
                } else {
                    // Còn hàng nhưng ít hơn số lượng đặt
                    return "Sản phẩm {$item['name']}" 
                        . (!empty($item['variant']) ? " ({$item['variant']})" : "") 
                        . " hiện chỉ còn {$item['available']} cái, bạn đã chọn {$item['requested']} cái. Vui lòng giảm số lượng xuống {$item['available']} cái.";
                }
            })->implode('<br>');

            return redirect()->route('cart.index')->with('toast_error', $messages);
        }

        // Tính tổng khối lượng và kích thước
        $items = $cartData['items'];
        $totalWeight = $items->sum(function ($item) {
            return ($item->productVariant->weight ?? 0) * $item->quantity;
        });
        $maxLength = $items->max(function ($item) {
            return $item->productVariant->dimensions_length ?? 0;
        });
        $maxWidth = $items->max(function ($item) {
            return $item->productVariant->dimensions_width ?? 0;
        });
        $totalHeight = $items->sum(function ($item) {
            return ($item->productVariant->dimensions_height ?? 0) * $item->quantity;
        });
        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
        $user = auth()->user();

        $availableCoupons = Coupon::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhereRaw('
                        (SELECT COUNT(*) FROM coupon_usages WHERE coupon_usages.coupon_id = coupons.id) < coupons.max_uses
                    ');
            })
            ->where(function ($query) use ($subtotal) {
                $query->whereNull('min_order_amount')
                    ->orWhere('min_order_amount', '<=', $subtotal);
            })
            ->get()
            ->filter(function ($coupon) use ($user) {
                if (!$user) {
                    return true;
                }
                if ($coupon->max_uses_per_user !== null) {
                    $usedByUser = DB::table('coupon_usages')
                        ->where('coupon_id', $coupon->id)
                        ->where('user_id', $user->id)
                        ->count();
                    if ($usedByUser >= $coupon->max_uses_per_user) {
                        return false;
                    }
                }
                return true;
            });
        // Ví dụ nếu có trong $cartData
        $pointsDiscount = $cartData['discount_from_points'] ?? 0;

        // Hoặc lấy trực tiếp từ session nếu chưa có trong $cartData
        if (!isset($pointsDiscount)) {
            $pointsApplied = session('points_applied', ['points' => 0, 'discount' => 0]);
            $pointsDiscount = $pointsApplied['discount'] ?? 0;
        }

        $appliedCoupon = session('applied_coupon');
        $discount = $appliedCoupon['discount'] ?? 0;
        $voucherCode = $appliedCoupon['code'] ?? null;
        $total = max(0, $subtotal - $discount - $pointsDiscount);

        // Lấy địa chỉ của user nếu đã đăng nhập
        $userAddresses = collect();
        if ($user) {
            $userAddresses = $user->addresses()->with(['province', 'district', 'ward', 'provinceOld', 'districtOld', 'wardOld'])->orderBy('is_default_shipping', 'desc')->get();
        }

        return view('users.payments.information', array_merge($cartData, [
            'baseWeight' => $totalWeight > 0 ? $totalWeight : 1000,
            'baseLength' => $maxLength > 0 ? $maxLength : 20,
            'baseWidth' => $maxWidth > 0 ? $maxWidth : 10,
            'baseHeight' => $totalHeight > 0 ? $totalHeight : 10,
            'availableCoupons' => $availableCoupons,
            'total' => $total,
            'discount' => $discount,
            'pointsDiscount' => $pointsDiscount,
            'userAddresses' => $userAddresses,
        ]));
        if ($cartData['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        // 2. Lấy các giá trị đã được tính toán đúng từ $cartData
        $items = $cartData['items'];
        $subtotal = $cartData['subtotal'];
        $couponDiscount = $cartData['discount_from_coupon']; // Chỉ lấy giảm giá từ coupon
        $pointsDiscount = $cartData['discount_from_points']; // Lấy giảm giá từ điểm
        $total = $cartData['total']; // Lấy tổng tiền cuối cùng đã được tính đúng
        $totalPointsToEarn = $cartData['totalPointsToEarn'];

        // 3. Tính toán các thông số vận chuyển
        $totalWeight = $items->sum(function ($item) {
            return ($item->productVariant->weight ?? 0) * $item->quantity;
        });
        $maxLength = $items->max(function ($item) {
            return $item->productVariant->dimensions_length ?? 0;
        });
        $maxWidth = $items->max(function ($item) {
            return $item->productVariant->dimensions_width ?? 0;
        });
        $totalHeight = $items->sum(function ($item) {
            return ($item->productVariant->dimensions_height ?? 0) * $item->quantity;
        });

        $availableCoupons = Coupon::where('status', 'active')->get();

        // 4. Trả về view với toàn bộ dữ liệu chính xác
        return view('users.payments.information', [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $couponDiscount,      // Chỉ giảm giá từ coupon
            'pointsDiscount' => $pointsDiscount,  // Biến mới cho giảm giá từ điểm
            'total' => $total,                  // Tổng cuối cùng đã tính đúng
            'totalPointsToEarn' => $totalPointsToEarn,
            'availableCoupons' => $availableCoupons,
            'baseWeight' => $totalWeight > 0 ? $totalWeight : 1000,
            'baseLength' => $maxLength > 0 ? $maxLength : 20,
            'baseWidth' => $maxWidth > 0 ? $maxWidth : 10,
            'baseHeight' => $totalHeight > 0 ? $totalHeight : 10,
        ]);
    }
    /**
     * Xử lý đặt hàng COD
     */
    public function processOrder(PaymentRequest $request)
    {
        // Debug: Log request data
        Log::info('PaymentRequest Data:', $request->all());

        $cartData = $this->getCartData();
        if ($cartData['items']->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng đang trống.'], 400);
        }

        // SỬA: Chuyển logic thanh toán online ra ngoài để dùng chung
        if (in_array($request->payment_method, ['vnpay', 'momo', 'bank_transfer_qr'])) {
            return $this->handleOnlinePayment($request, $cartData);
        }

        // Mặc định là COD
        return $this->handleCodPayment($request, $cartData);
    }
    private function handleOnlinePayment(PaymentRequest $request, array $cartData)
    {
        try {
            $order = DB::transaction(function () use ($request, $cartData) {
                return $this->createOrderAndItems($request, $cartData);
            });

            // Sau khi tạo đơn hàng thành công, gọi phương thức thanh toán tương ứng
            if ($request->payment_method === 'vnpay') {
                return $this->createVnpayPayment($order, $request);
            }
            if ($request->payment_method === 'momo') {
                return $this->createMomoPayment($order);
            }
            if ($request->payment_method === 'bank_transfer_qr') {
                // Gửi thông báo Telegram cho QR
                $confirmationUrl = route('payments.confirm', ['token' => $order->confirmation_token]);
                $this->sendTelegramNotification("🔔 *Đơn hàng QR mới!*\n", $order, $confirmationUrl);

                return response()->json([
                    'success' => true,
                    'redirect_url' => route('payments.bank_transfer_qr', ['order' => $order->id])
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi tạo đơn hàng cho thanh toán online: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
    private function handleCodPayment(PaymentRequest $request, array $cartData)
    {
        try {
            $order = DB::transaction(function () use ($request, $cartData) {
                // Tạo đơn hàng và các mục liên quan
                $order = $this->createOrderAndItems($request, $cartData);
                $user = Auth::user();
                    $pointsApplied = session('points_applied');
                    if ($user && $pointsApplied) {
                        $pointsUsed = $pointsApplied['points'] ?? 0;
                        if ($pointsUsed > 0) {
                            if ($pointsUsed > $user->loyalty_points_balance) {
                                throw new \Exception('Số dư điểm không đủ để thực hiện giao dịch này.');
                            }
                            $user->decrement('loyalty_points_balance', $pointsUsed);
                            LoyaltyPointLog::create([
                                'user_id' => $user->id,
                                'order_id' => $order->id,
                                'points' => -$pointsUsed,
                                'type' => 'spend',
                                'description' => "Sử dụng " . number_format($pointsUsed) . " điểm cho đơn hàng #{$order->order_code}",
                            ]);
                        }
                    }
                // Trừ tồn kho ngay lập tức cho COD
                foreach ($order->fulfillments as $fulfillment) {
                    foreach ($fulfillment->items as $fulfillmentItem) {
                        $this->decrementInventoryStock(
                            $fulfillmentItem->orderItem->productVariant,
                            $fulfillmentItem->quantity,
                            $fulfillment->store_location_id // Quan trọng: trừ kho từ đúng location
                        );
                    }
                }

                return $order;
            });

            // Gửi thông báo Telegram cho COD
            $this->sendTelegramNotification("📦 *Đơn hàng COD mới!*\n", $order);

            // Kích hoạt chuyển kho tự động
            $autoTransferService = new AutoStockTransferService();
            $transferResult = $autoTransferService->checkAndCreateAutoTransfer($order);

            if ($transferResult['success'] && !empty($transferResult['transfers_created'])) {
                Log::info('Đã tạo phiếu chuyển kho tự động cho đơn hàng: ' . $order->order_code, $transferResult['transfers_created']);
            }

            // Xóa giỏ hàng sau khi đặt hàng thành công
            $this->clearPurchaseSession();

            return response()->json(['success' => true, 'message' => 'Đặt hàng thành công!', 'order' => $order]);

        } catch (\Exception $e) {
            Log::error("Lỗi khi xử lý đơn hàng COD: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
    // note ///
    private function createOrderAndItems(PaymentRequest $request, array $cartData): Order
    {
        $user = Auth::user();
        $totalDiscount = $cartData['discount_from_coupon'] + $cartData['discount_from_points'];

        // SỬA: Tính tổng phí ship từ mảng shipments
        $totalShippingFee = 0;
        if ($request->delivery_method === 'delivery') {
            $totalShippingFee = collect($request->input('shipments', []))->sum('shipping_fee');
        }

        $grandTotal = $cartData['subtotal'] + $totalShippingFee - $totalDiscount;

        $customerInfo = $this->prepareCustomerInfo($request);
        $addressData = $this->prepareAddressData($request);

        $order = Order::create([
            'user_id' => $user->id ?? null,
            'guest_id' => !$user ? session()->getId() : null,
            'order_code' => 'DH-' . strtoupper(Str::random(10)),
            'customer_name' => $customerInfo['customer_name'],
            'customer_email' => $customerInfo['customer_email'],
            'customer_phone' => $customerInfo['customer_phone'],
            'shipping_address_line1' => $customerInfo['shipping_address_line1'],
            // ... (các trường địa chỉ khác từ $addressData)
            'sub_total' => $cartData['subtotal'],
            'shipping_fee' => $totalShippingFee, // SỬA: Dùng phí ship tổng
            'discount_amount' => $totalDiscount,
            'grand_total' => $grandTotal,
            'payment_method' => $request->payment_method,
            'payment_status' => Order::PAYMENT_PENDING,
            'status' => Order::STATUS_PENDING_CONFIRMATION,
            'shipping_method' => $request->delivery_method === 'delivery' ? 'Giao hàng tận nơi' : 'Nhận tại cửa hàng',
            'notes_from_customer' => $request->notes,
            // ... các trường khác
        ]);

        // Tạo Order Items
        $orderItemsMap = [];
        foreach ($cartData['items'] as $item) {
            $variant = $item->productVariant;
            $variantAttributes = $variant->attributeValues->mapWithKeys(fn($attr) => [$attr->attribute->name => $attr->value])->toArray();
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $variant->id,
                'sku' => $variant->sku,
                'product_name' => $variant->product->name,
                'variant_attributes' => $variantAttributes,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total_price' => $item->price * $item->quantity,
            ]);
            // Map product_variant_id với order_item_id để dùng ở bước sau
            $orderItemsMap[$variant->id] = $orderItem->id;
        }

        // MỚI: Sử dụng FulfillmentService để tạo Order Fulfillments
        if ($request->delivery_method === 'delivery') {
            $fulfillmentService = new FulfillmentService();
            $fulfillmentService->createOrderFulfillments($order, $cartData['items'], $request->input('shipments', []), $orderItemsMap);
        }

        // MỚI: Tạm giữ tồn kho cho đơn hàng
        try {
            $inventoryService = new InventoryCommitmentService();
            $inventoryService->commitInventoryForOrder($order);
        } catch (\Exception $e) {
            // Nếu không đủ tồn kho, xóa đơn hàng và báo lỗi
            $order->delete();
            throw new \Exception('Không đủ tồn kho: ' . $e->getMessage());
        }

        // ... (xử lý trừ điểm, ghi log coupon, lưu địa chỉ)
        // Phần này giữ nguyên

        return $order;
    }

    private function createVnpayPayment(Order $order, Request $request)
{
    try {
        // Dữ liệu cần thiết để tạo URL VNPay được lấy trực tiếp từ đối tượng $order
        $grandTotal = $order->grand_total;
        $orderCode = $order->order_code;

        // Kiểm tra nếu tổng tiền nhỏ hơn hoặc bằng 0 thì không cần thanh toán
        if ($grandTotal <= 0) {
            // Trong trường hợp này, đơn hàng được xem là đã thanh toán (ví dụ: thanh toán toàn bộ bằng điểm)
            // Bạn có thể cập nhật trạng thái đơn hàng ở đây nếu cần
            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'status' => Order::STATUS_PENDING_CONFIRMATION // Hoặc trạng thái phù hợp khác
            ]);

            // Trả về URL trang thành công thay vì URL thanh toán
            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được thanh toán thành công bằng điểm thưởng.',
                'redirect_url' => route('payments.success', ['order_id' => $order->id]) // Chuyển hướng đến trang thành công
            ]);
        }

        // --- LOGIC TẠO URL VNPAY (GIỮ NGUYÊN) ---
        $vnp_Url = config('vnpay.url');
        $vnp_Returnurl = route('payments.vnpay.return'); // Sử dụng route() để an toàn hơn
        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $grandTotal * 100, // VNPay yêu cầu số tiền nhân 100
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $request->ip(),
            "vnp_Locale" => 'vn',
            "vnp_OrderInfo" => "Thanh toan cho don hang #" . $orderCode,
            "vnp_OrderType" => 'billpayment',
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $orderCode,
        ];

        if (!empty($request->input('bank_code'))) {
            $inputData['vnp_BankCode'] = $request->input('bank_code');
        }

        ksort($inputData);
        $query = "";
        $hashdata = "";
        $i = 0;
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

        // Trả về URL để frontend chuyển hướng người dùng
        return response()->json([
            'success' => true,
            'message' => 'Đang chuyển hướng đến VNPay...',
            'payment_url' => $vnp_Url
        ]);

    } catch (\Exception $e) {
        // Ghi log lỗi nếu có bất kỳ vấn đề gì xảy ra
        Log::error("Lỗi khi tạo URL thanh toán VNPAY cho đơn hàng #{$order->order_code}: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi khởi tạo thanh toán. Vui lòng thử lại.'
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
        $order = Order::with('fulfillments.items.orderItem.productVariant')->where('order_code', $request->vnp_TxnRef)->first();
        if ($order) {
            if ($request->vnp_ResponseCode == '00') {
                if ($order->payment_status == Order::PAYMENT_PENDING) {
                    DB::transaction(function() use ($order) {
                        $order->payment_status = Order::PAYMENT_PAID;
                        $order->paid_at = now();
                        $order->save();

                        // MỚI: Trừ kho theo từng fulfillment
                        foreach ($order->fulfillments as $fulfillment) {
                            foreach ($fulfillment->items as $fulfillmentItem) {
                                $this->decrementInventoryStock(
                                    $fulfillmentItem->orderItem->productVariant,
                                    $fulfillmentItem->quantity,
                                    $fulfillment->store_location_id
                                );
                            }
                        }
                    });

                    // Kích hoạt chuyển kho tự động
                    $autoTransferService = new AutoStockTransferService();
                    $transferResult = $autoTransferService->checkAndCreateAutoTransfer($order);

                    if ($transferResult['success'] && !empty($transferResult['transfers_created'])) {
                        Log::info('Đã tạo phiếu chuyển kho tự động cho đơn hàng VNPay: ' . $order->order_code, $transferResult['transfers_created']);
                    }

                    $this->clearPurchaseSession();
                }
                return redirect()->route('payments.success', ['order_id' => $order->id])->with('success', 'Thanh toán thành công!');
            }
 else {
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
                                    $this->decrementInventoryStock($variant, $item->quantity, $storeLocationId);
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
    private function createMomoPayment(Order $order)
{
    try {
        // Dữ liệu cần thiết được lấy trực tiếp từ đối tượng $order
        $grandTotal = $order->grand_total;
        $orderCode = $order->order_code;

        // Kiểm tra nếu tổng tiền bằng 0 thì không cần chuyển qua MoMo
        if ($grandTotal <= 0) {
            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'status' => Order::STATUS_PENDING_CONFIRMATION
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được thanh toán thành công bằng điểm thưởng.',
                'redirect_url' => route('payments.success', ['order_id' => $order->id])
            ]);
        }

        // --- LOGIC TẠO URL MOMO (GIỮ NGUYÊN) ---
        $endpoint = config('momo.endpoint');
        $partnerCode = config('momo.partner_code');
        $accessKey = config('momo.access_key');
        $secretKey = config('momo.secret_key');

        $orderInfo = "Thanh toan cho don hang #" . $orderCode;
        $amount = (string)(int)$grandTotal;
        $orderId = $orderCode . "_" . time(); // Đảm bảo orderId là duy nhất cho mỗi giao dịch
        $requestId = (string) Str::uuid();
        $redirectUrl = route('payments.momo.return'); // Sử dụng route()
        $ipnUrl = route('payments.momo.ipn'); // Sử dụng route()
        $requestType = "captureWallet";
        $extraData = ""; // Có thể mã hóa base64 thông tin thêm nếu cần

        // Chuỗi để tạo chữ ký
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

        // Gửi request đến MoMo
        $response = Http::post($endpoint, $data);
        $jsonResponse = $response->json();

        // Xử lý kết quả từ MoMo
        if (isset($jsonResponse['resultCode']) && $jsonResponse['resultCode'] == 0) {
            return response()->json([
                'success' => true,
                'message' => 'Đang chuyển hướng đến MoMo...',
                'payment_url' => $jsonResponse['payUrl']
            ]);
        } else {
            // Ghi log lỗi và báo lỗi nếu MoMo trả về kết quả không thành công
            Log::error('Lỗi khi tạo thanh toán MoMo: ', $jsonResponse ?? ['message' => 'Không có phản hồi']);
            throw new \Exception('Lỗi từ MoMo: ' . ($jsonResponse['message'] ?? 'Không xác định'));
        }

    } catch (\Exception $e) {
        // Ghi log lỗi hệ thống
        Log::error("Lỗi khi tạo URL thanh toán MoMo cho đơn hàng #{$order->order_code}: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi khởi tạo thanh toán. Vui lòng thử lại.'
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
                            $this->decrementInventoryStock($variant, $item->quantity, $storeLocationId);
                        }
                    }
                    // Sản phẩm cũ không cần trừ tồn kho
                }

                // Kích hoạt chuyển kho tự động
                $autoTransferService = new AutoStockTransferService();
                $transferResult = $autoTransferService->checkAndCreateAutoTransfer($order);

                if ($transferResult['success'] && !empty($transferResult['transfers_created'])) {
                    Log::info('Đã tạo phiếu chuyển kho tự động cho đơn hàng MoMo: ' . $order->order_code, $transferResult['transfers_created']);
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
                            $this->decrementInventoryStock($variant, $item->quantity, $storeLocationId);
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
        if (session()->has('buy_now_session')) {
            session()->forget('buy_now_session');
        } else {
            if (Auth::check() && Auth::user()->cart) {
                Auth::user()->cart->items()->delete();
            } else {
                session()->forget('cart');
            }
        }
        session()->forget(['cart','applied_voucher', 'applied_coupon', 'discount','points_applied']);
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
                    ->with(['items.productVariant.product', 'shippingProvince', 'shippingWard', 'storeLocation.ward', 'storeLocation.district', 'storeLocation.province'])
                    ->first();
            } else {
                $order = Order::where('id', $orderId)
                    ->where('guest_id', session()->getId())
                    ->with(['items.productVariant.product', 'shippingProvince', 'shippingWard', 'storeLocation.ward', 'storeLocation.district', 'storeLocation.province'])
                    ->first();
            }
        }
        $totalPointsEarned = 0;
        if ($order) {
            // Tính tổng điểm thưởng từ tất cả các sản phẩm trong đơn hàng
            $totalPointsEarned = $order->items->sum(function ($item) {
                // Lấy điểm từ productVariant và nhân với số lượng
                return ($item->productVariant->points_awarded_on_purchase ?? 0) * $item->quantity;
            });
        }
        return view('users.payments.success', compact('order', 'totalPointsEarned'));
    }
    /**
     * Lấy dữ liệu giỏ hàng
     */
    // Trong file app/Http/Controllers/Users/PaymentController.php

    private function getCartData()
    {
        $user = auth()->user();
        $items = collect();
        // 1. Lấy danh sách sản phẩm
        if ($user && $user->cart) {
            $items = $user->cart->items()
                ->with('cartable.product.coverImage', 'cartable.attributeValues.attribute', 'cartable.primaryImage')
                ->get()
                ->filter(fn($item) => $item->cartable && $item->cartable->product)
                ->map(function ($item) {
                    if ($item->cartable_type === ProductVariant::class) {
                        $item->stock_quantity = $this->getSellableStock($item->cartable) ?? 0;
                        $item->productVariant = $item->cartable;
                        $item->points_to_earn = $item->cartable->points_awarded_on_purchase ?? 0;
                    } else {
                        $item->stock_quantity = 999;
                        $item->productVariant = $item->cartable;
                        $item->points_to_earn = 0;
                    }
                    return $item;
                });
        } else {
            $sessionCart = session('cart', []);
            $items = collect($sessionCart)->map(function ($data) {
                $cartableId = $data['cartable_id'] ?? $data['variant_id'] ?? null;
                if (!$cartableId)
                    return null;

                $cartable = ProductVariant::with('product.coverImage', 'attributeValues.attribute', 'primaryImage')->find($cartableId);
                if (!$cartable || !$cartable->product)
                    return null;

                return (object) [
                    'id' => $cartableId,
                    'productVariant' => $cartable,
                    'price' => $data['price'],
                    'quantity' => $data['quantity'],
                    'stock_quantity' => $this->getSellableStock($cartable),
                    'cartable_type' => ProductVariant::class,
                    'points_to_earn' => $cartable->points_awarded_on_purchase ?? 0,
                ];
            })->filter();
        }
        // 2. Tính toán các giá trị tài chính MỘT LẦN DUY NHẤT
        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
        // Lấy giảm giá từ coupon
        $couponDiscount = session('applied_coupon.discount', 0);
        // Lấy giảm giá từ điểm thưởng
        $pointsDiscount = 0;
        if (Auth::check()) {
            $pointsDiscount = session('points_applied.discount', 0);
        }
        // Tính tổng giảm giá và tổng tiền cuối cùng
        $totalDiscount = $couponDiscount + $pointsDiscount;
        $total = max(0, $subtotal - $totalDiscount);

        // Tính tổng điểm thưởng sẽ nhận được
        $totalPointsToEarn = $items->sum(function ($item) {
            return ($item->points_to_earn ?? 0) * $item->quantity;
        });
        // 3. Trả về kết quả cuối cùng
        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $couponDiscount, //  Chỉ trả về discount của coupon
            'discount_from_coupon' => $couponDiscount, // Để rõ ràng hơn
            'discount_from_points' => $pointsDiscount, // Để rõ ràng hơn
            'total' => $total, // Tổng tiền cuối cùng đã chính xác
            'voucher' => session('applied_coupon'), // Giữ nguyên để có thể dùng ở nơi khác
            'items_count' => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'totalPointsToEarn' => $totalPointsToEarn,
        ];
    }


    /**
     * Xử lý thông tin thời gian giao hàng
     */
    private function formatDeliveryDateTime($shippingMethod, $deliveryDate = null, $deliveryTimeSlot = null, $pickupDate = null, $pickupTimeSlot = null, $deliveryMethod = null)
    {
        // Kiểm tra delivery_method thay vì shipping_method để nhất quán với validation
        $deliveryMethod = $deliveryMethod ?? request('delivery_method');

        // Nếu là nhận tại cửa hàng
        if ($deliveryMethod === 'pickup' || str_contains(strtolower($shippingMethod), 'nhận tại cửa hàng')) {
            // Sử dụng pickup_date và pickup_time_slot từ tham số
            if ($pickupDate && $pickupTimeSlot) {
                return [
                    'date' => $pickupDate,
                    'time_slot' => $pickupTimeSlot
                ];
            }

            return [
                'date' => null,
                'time_slot' => null
            ];
        }

        // Nếu là giao hàng tiêu chuẩn
        if (str_contains(strtolower($shippingMethod), 'giao hàng tiêu chuẩn')) {
            return [
                'date' => 'Dự kiến 3-5 ngày làm việc',
                'time_slot' => null
            ];
        }

        // Nếu có delivery_date và delivery_time_slot từ form
        if ($deliveryDate && $deliveryTimeSlot) {
            // Lưu ngày theo định dạng Y-m-d vào database (chuẩn hơn)
            return [
                'date' => $deliveryDate,
                'time_slot' => $deliveryTimeSlot
            ];
        }

        // Fallback cho logic cũ (nếu có shipping_time)
        $shippingTime = request('shipping_time');
        if (!empty($shippingTime)) {
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
        }

        // Trả về giá trị mặc định nếu không có thông tin
        return [
            'date' => null,
            'time_slot' => null
        ];
    }

    /**
     * Chuẩn bị thông tin khách hàng từ request
     */
    private function prepareCustomerInfo(Request $request): array
    {
        // Kiểm tra xem có phải là "Nhận tại cửa hàng" không - sử dụng delivery_method để nhất quán
        $deliveryMethod = $request->delivery_method ?? '';
        $shippingMethod = $request->shipping_method ?? '';
        $isPickup = $deliveryMethod === 'pickup' || str_contains(strtolower($shippingMethod), 'nhận tại cửa hàng');

        // Fallback: Nếu có thông tin pickup nhưng không phải pickup method, vẫn sử dụng pickup info
        $hasPickupInfo = !empty($request->pickup_full_name) && !empty($request->pickup_phone_number);

        if ($isPickup || $hasPickupInfo) {
            // Nếu là nhận tại cửa hàng hoặc có thông tin pickup, sử dụng thông tin pickup
            $customerName = $request->pickup_full_name;
            if (empty($customerName)) {
                throw new \Exception('Tên khách hàng không được để trống khi nhận tại cửa hàng.');
            }

            return [
                'customer_name' => $customerName,
                'customer_email' => $request->pickup_email,
                'customer_phone' => $request->pickup_phone_number,
                'shipping_address_line1' => 'Nhận tại cửa hàng',
                'store_location_id' => $request->store_location_id,
                'shipping_zip_code' => null,
            ];
        }

        // Nếu sử dụng địa chỉ đã lưu
        if ($request->address_id) {
            $address = Address::findOrFail($request->address_id);

            // Kiểm tra quyền sở hữu địa chỉ
            if (Auth::check() && $address->user_id !== Auth::id()) {
                throw new \Exception('Bạn không có quyền sử dụng địa chỉ này.');
            }

            $customerName = $address->full_name;
            if (empty($customerName)) {
                throw new \Exception('Tên khách hàng trong địa chỉ đã lưu không được để trống.');
            }

            return [
                'customer_name' => $customerName,
                'customer_email' => Auth::check() ? Auth::user()->email : null, // Lấy email từ user đã đăng nhập
                'customer_phone' => $address->phone_number,
                'shipping_address_line1' => $address->address_line1,
                'shipping_zip_code' => null, // Address model không có postcode
            ];
        }

        // Nếu sử dụng địa chỉ mới
        $customerName = $request->full_name;
        if (empty($customerName)) {
            throw new \Exception('Tên khách hàng không được để trống.');
        }

        return [
            'customer_name' => $customerName,
            'customer_email' => $request->email,
            'customer_phone' => $request->phone_number ?? $request->phone,
            'shipping_address_line1' => $request->address_line1 ?? $request->address,
            'shipping_zip_code' => $request->postcode ?? null,
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
        session()->forget(['cart','applied_voucher', 'applied_coupon', 'discount', 'points_applied']);
    }
    /**
     * Tạo phiên Buy Now và chuyển đến trang thanh toán
     */
    public function buyNowCheckout(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_key' => 'nullable|string',
            'quantity' => 'required|integer|min:1|',
        ]);
        session()->forget(['applied_coupon', 'points_applied']);
        $product = Product::findOrFail($request->product_id);
        $variant = null;
        // Tìm variant dựa vào variant_key hoặc lấy variant đầu tiên
        if ($request->variant_key) {
            $variant = ProductVariant::with('product.coverImage', 'attributeValues', 'primaryImage')->where('product_id', $product->id)->get()
                ->first(function ($variant) use ($request) {
                    $attributes = $variant->attributeValues->pluck('value')->toArray();
                    return implode('_', $attributes) === $request->variant_key;
                });
        }
        if (!$variant) {
            $variant = ProductVariant::with('product.coverImage', 'primaryImage')->where('product_id', $product->id)->first();
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
            'image' => ($variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) . '?v=' . time() : ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path)) ? Storage::url($variant->product->coverImage->path) . '?v=' . time() : asset('images/placeholder.jpg'))),
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
        $items = $buyNowData['items'];
        if (!$buyNowData['items'] || $buyNowData['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Không tìm thấy sản phẩm.');
        }
        // 3. Tính subtotal
        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);

        // 4. Lọc coupon hợp lệ
        $user = auth()->user();

        $availableCoupons = Coupon::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhereRaw('
                        (SELECT COUNT(*) FROM coupon_usages WHERE coupon_usages.coupon_id = coupons.id) < coupons.max_uses
                    ');
            })
            ->where(function ($query) use ($subtotal) {
                $query->whereNull('min_order_amount')
                    ->orWhere('min_order_amount', '<=', $subtotal);
            })
            ->get()
            ->filter(function ($coupon) use ($user) {
                if (!$user)
                    return true;

                if ($coupon->max_uses_per_user !== null) {
                    $usedByUser = DB::table('coupon_usages')
                        ->where('coupon_id', $coupon->id)
                        ->where('user_id', $user->id)
                        ->count();
                    return $usedByUser < $coupon->max_uses_per_user;
                }
                return true;
            });
        $buyNowData['is_buy_now'] = true;
        $buyNowData['availableCoupons'] = $availableCoupons;
        $buyNowData['subtotal'] = $subtotal;

        // Lấy địa chỉ người dùng nếu đã đăng nhập
        $userAddresses = collect();
        if (auth()->check()) {
            $userAddresses = auth()->user()->addresses()->with(['province', 'district', 'ward', 'provinceOld', 'districtOld', 'wardOld'])->get();
        }
        $buyNowData['userAddresses'] = $userAddresses;

        // Thêm flag để template biết đây là Buy Now
        $buyNowData['is_buy_now'] = true;
        return view('users.payments.information', $buyNowData);
    }

    /**
     * Xử lý đặt hàng Buy Now
     *
     * LUỒNG HOẠT ĐỘNG MỚI:
     * 1. Kiểm tra tồn kho tại kho được chỉ định (pickup) hoặc tự động tìm kho (delivery)
     * 2. TẠM GIỮ HÀNG (commitStock) thay vì trừ thẳng tồn kho
     * 3. Tạo đơn hàng với trạng thái pending
     * 4. Kích hoạt chuyển kho tự động nếu cần thiết
     * 5. Tự động xử lý phiếu chuyển kho nếu có thể (cùng tỉnh/thành)
     *
     * QUẢN LÝ TỒN KHO:
     * - quantity_committed: Số lượng đã tạm giữ cho đơn hàng
     * - quantity: Tồn kho thực tế
     * - available_quantity = quantity - quantity_committed
     *
     * XỬ LÝ SAU ĐẶT HÀNG:
     * - Khi đơn hàng SHIPPED/OUT_FOR_DELIVERY: fulfillStock() - chuyển từ committed sang xuất kho thực
     * - Khi đơn hàng CANCELLED/RETURNED: releaseStock() - thả hàng đã tạm giữ
     */
    public function processBuyNowOrder(PaymentRequest $request)
    {
        // Validation đã được xử lý trong PaymentRequest
        // Kiểm tra session Buy Now
        if (!session()->has('buy_now_session')) {
            return response()->json(['success' => false, 'message' => 'Phiên mua hàng đã hết hạn.'], 400);
        }
        $buyNowData = $this->getBuyNowData();
        if (!$buyNowData['items'] || $buyNowData['items']->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm.'], 400);
        }
        if ($request->payment_method === 'vnpay') {
            try {
                DB::beginTransaction();

                $orderCode = 'DH-' . strtoupper(Str::random(10));
                $shippingFee = $request->has('shipping_fee') ? (int) $request->shipping_fee : 0;
                $customerInfo = $this->prepareCustomerInfo($request);
                $addressData = $this->prepareAddressData($request);
                $deliveryInfo = $this->formatDeliveryDateTime($request->shipping_method, $request->delivery_date, $request->delivery_time_slot, $request->pickup_date, $request->pickup_time_slot, $request->delivery_method);

                // Tạo đơn hàng cho VNPay
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'guest_id' => !Auth::check() ? session()->getId() : null,
                    'order_code' => $orderCode,
                    'customer_name' => $customerInfo['customer_name'],
                    'customer_email' => $customerInfo['customer_email'],
                    'customer_phone' => $customerInfo['customer_phone'],
                    'shipping_address_line1' => $customerInfo['shipping_address_line1'],
                    'shipping_zip_code' => $customerInfo['shipping_zip_code'] ?? null,
                    'shipping_country' => 'Vietnam',
                    'shipping_address_system' => $addressData['shipping_address_system'],
                    'shipping_old_province_code' => $addressData['shipping_old_province_code'],
                    'shipping_old_district_code' => $addressData['shipping_old_district_code'],
                    'shipping_old_ward_code' => $addressData['shipping_old_ward_code'],
                    'sub_total' => $buyNowData['subtotal'],
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => $buyNowData['discount'],
                    'grand_total' => $buyNowData['subtotal'] + $shippingFee - $buyNowData['discount'],
                    'payment_method' => 'vnpay',
                    'payment_status' => Order::PAYMENT_PENDING,
                    'shipping_method' => $request->shipping_method,
                    'status' => Order::STATUS_PENDING_CONFIRMATION,
                    'confirmation_token' => Str::random(40),
                    'notes_from_customer' => $request->notes,
                    'desired_delivery_date' => $deliveryInfo['date'],
                    'desired_delivery_time_slot' => $deliveryInfo['time_slot'],
                    'store_location_id' => $customerInfo['store_location_id'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Tạo order item từ dữ liệu "Mua Ngay"
                $item = $buyNowData['items']->first();
                $variant = $item->productVariant;
                $variantAttributes = $variant->attributeValues->mapWithKeys(fn($attrValue) => [$attrValue->attribute->name => $attrValue->value])->toArray();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'product_name' => $variant->product->name,
                    'variant_attributes' => $variantAttributes,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                    'image_url' => $variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) : ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path)) ? Storage::url($variant->product->coverImage->path) : asset('images/placeholder.jpg')),
                ]);

                if (Auth::check() && $request->save_address && !$request->address_id) {
                    $this->saveNewAddress($request);
                }

                DB::commit();
                return $this->createVnpayPayment($order, $request);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()], 500);
            }
        }

        // Nếu là thanh toán MoMo
        if ($request->payment_method === 'momo') {
            try {
                DB::beginTransaction();

                $orderCode = 'DH-' . strtoupper(Str::random(10));
                $shippingFee = $request->has('shipping_fee') ? (int) $request->shipping_fee : 0;
                $customerInfo = $this->prepareCustomerInfo($request);
                $addressData = $this->prepareAddressData($request);
                $deliveryInfo = $this->formatDeliveryDateTime($request->shipping_method, $request->delivery_date, $request->delivery_time_slot, $request->pickup_date, $request->pickup_time_slot, $request->delivery_method);

                // Tạo đơn hàng cho MoMo
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'guest_id' => !Auth::check() ? session()->getId() : null,
                    'order_code' => $orderCode,
                    'customer_name' => $customerInfo['customer_name'],
                    'customer_email' => $customerInfo['customer_email'],
                    'customer_phone' => $customerInfo['customer_phone'],
                    'shipping_address_line1' => $customerInfo['shipping_address_line1'],
                    'shipping_zip_code' => $customerInfo['shipping_zip_code'] ?? null,
                    'shipping_country' => 'Vietnam',
                    'shipping_address_system' => $addressData['shipping_address_system'],
                    'shipping_old_province_code' => $addressData['shipping_old_province_code'],
                    'shipping_old_district_code' => $addressData['shipping_old_district_code'],
                    'shipping_old_ward_code' => $addressData['shipping_old_ward_code'],
                    'sub_total' => $buyNowData['subtotal'],
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => $buyNowData['discount'],
                    'grand_total' => $buyNowData['subtotal'] + $shippingFee - $buyNowData['discount'],
                    'payment_method' => 'momo',
                    'payment_status' => Order::PAYMENT_PENDING,
                    'shipping_method' => $request->shipping_method,
                    'status' => Order::STATUS_PENDING_CONFIRMATION,
                    'confirmation_token' => Str::random(40),
                    'notes_from_customer' => $request->notes,
                    'desired_delivery_date' => $deliveryInfo['date'],
                    'desired_delivery_time_slot' => $deliveryInfo['time_slot'],
                    'store_location_id' => $customerInfo['store_location_id'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Tạo order item từ dữ liệu "Mua Ngay"
                $item = $buyNowData['items']->first();
                $variant = $item->productVariant;
                $variantAttributes = $variant->attributeValues->mapWithKeys(fn($attrValue) => [$attrValue->attribute->name => $attrValue->value])->toArray();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'product_name' => $variant->product->name,
                    'variant_attributes' => $variantAttributes,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                    'image_url' => $variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) : ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path)) ? Storage::url($variant->product->coverImage->path) : asset('images/placeholder.jpg')),
                ]);

                if (Auth::check() && $request->save_address && !$request->address_id) {
                    $this->saveNewAddress($request);
                }

                DB::commit();
                return $this->createMomoPayment($order);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()], 500);
            }
        }
        if ($request->payment_method === 'bank_transfer_qr') {
            try {
                DB::beginTransaction();

                $orderCode = 'DH-' . strtoupper(Str::random(10));
                $shippingFee = $request->has('shipping_fee') ? (int) $request->shipping_fee : 0;
                $customerInfo = $this->prepareCustomerInfo($request);
                $addressData = $this->prepareAddressData($request);
                $deliveryInfo = $this->formatDeliveryDateTime($request->shipping_method, $request->delivery_date, $request->delivery_time_slot, $request->pickup_date, $request->pickup_time_slot, $request->delivery_method);

                // Tạo đơn hàng ngay lập tức với trạng thái "Chờ thanh toán"
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'guest_id' => !Auth::check() ? session()->getId() : null,
                    'order_code' => $orderCode,
                    'customer_name' => $customerInfo['customer_name'],
                    'customer_email' => $customerInfo['customer_email'],
                    'customer_phone' => $customerInfo['customer_phone'],
                    'shipping_address_line1' => $customerInfo['shipping_address_line1'],
                    'shipping_zip_code' => $customerInfo['shipping_zip_code'] ?? null,
                    'shipping_country' => 'Vietnam',
                    'shipping_address_system' => $addressData['shipping_address_system'],
                    'shipping_old_province_code' => $addressData['shipping_old_province_code'],
                    'shipping_old_district_code' => $addressData['shipping_old_district_code'],
                    'shipping_old_ward_code' => $addressData['shipping_old_ward_code'],
                    'sub_total' => $buyNowData['subtotal'], // SỬA: Dùng buyNowData
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => $buyNowData['discount'], // SỬA: Dùng buyNowData
                    'grand_total' => $buyNowData['subtotal'] + $shippingFee - $buyNowData['discount'], // SỬA: Dùng buyNowData
                    'payment_method' => 'bank_transfer_qr',
                    'payment_status' => Order::PAYMENT_PENDING,
                    'shipping_method' => $request->shipping_method,
                    'status' => Order::STATUS_PENDING_CONFIRMATION,
                    'confirmation_token' => Str::random(40),
                    'notes_from_customer' => $request->notes,
                    'desired_delivery_date' => $deliveryInfo['date'],
                    'desired_delivery_time_slot' => $deliveryInfo['time_slot'],
                    'store_location_id' => $customerInfo['store_location_id'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Tạo order item từ dữ liệu "Mua Ngay"
                $item = $buyNowData['items']->first();
                $variant = $item->productVariant;
                $variantAttributes = $variant->attributeValues->mapWithKeys(fn($attrValue) => [$attrValue->attribute->name => $attrValue->value])->toArray();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'product_name' => $variant->product->name,
                    'variant_attributes' => $variantAttributes,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_price' => $item->price * $item->quantity,
                    'image_url' => $variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) : ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path)) ? Storage::url($variant->product->coverImage->path) : asset('images/placeholder.jpg')),
                ]);

                // Gửi thông báo Telegram
                $confirmationUrl = route('payments.confirm', ['token' => $order->confirmation_token]);
                $text = sprintf(
                    "🔔 *Đơn hàng QR mới (Mua Ngay)!*\n\n*Mã ĐH:* `%s`\n*Khách hàng:* %s\n*Tổng tiền:* %s VNĐ",
                    $order->order_code,
                    $order->customer_name,
                    number_format($order->grand_total)
                );
                Telegram::sendMessage([
                    'chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
                    'text' => $text,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '✅ Xác nhận đã thanh toán', 'url' => $confirmationUrl]]]])
                ]);

                if (Auth::check() && $request->save_address && !$request->address_id) {
                    $this->saveNewAddress($request);
                }

                // Xóa session "Mua Ngay"
                $this->clearBuyNowSession();
                DB::commit();

                return response()->json([
                    'success' => true,
                    'redirect_url' => route('payments.bank_transfer_qr', ['order' => $order->id])
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
            }
        }
        // Xử lý các phương thức thanh toán khác
        try {
            DB::beginTransaction();
            $user = Auth::user();
            // --- TÍCH HỢP ĐIỂM THƯỞNG ---
            $pointsApplied = session('points_applied');
            $pointsUsed = 0;
            $discountFromPoints = 0;
            $adminNote = $request->input('notes', '');

            if ($user && $pointsApplied) {
                $pointsUsed = $pointsApplied['points'];
                $discountFromPoints = $pointsApplied['discount'];
                if ($pointsUsed > $user->loyalty_points_balance) {
                    throw new \Exception('Số dư điểm không đủ.');
                }
                $pointsNote = "Đơn hàng áp dụng giảm giá từ " . number_format($pointsUsed) . " điểm (giảm " . number_format($discountFromPoints) . "đ).";
                $adminNote = trim($adminNote . "\n\n--- Ghi chú Điểm thưởng ---\n" . $pointsNote);
            }

            // --- TÍNH TOÁN LẠI GIÁ TRỊ CUỐI CÙNG ---
            $shippingFee = $request->has('shipping_fee') ? (int) $request->shipping_fee : 0;
            $totalDiscount = $buyNowData['discount'] + $discountFromPoints;
            $grandTotal = $buyNowData['subtotal'] + $shippingFee - $totalDiscount;

            $orderCode = 'DH-' . strtoupper(Str::random(10));
            $deliveryInfo = $this->formatDeliveryDateTime($request->shipping_method, $request->shipping_time);


            // Chuẩn bị dữ liệu địa chỉ và thông tin khách hàng
            $customerInfo = $this->prepareCustomerInfo($request);
            $addressData = $this->prepareAddressData($request);
            $deliveryInfo = $this->formatDeliveryDateTime($request->shipping_method, $request->delivery_date, $request->delivery_time_slot, $request->pickup_date, $request->pickup_time_slot, $request->delivery_method);

            // Kiểm tra thông tin khách hàng
            if (empty($customerInfo['customer_name'])) {
                throw new \Exception('Tên khách hàng không được để trống.');
            }

            $order = Order::create([
                'user_id' => Auth::id(),
                'guest_id' => !Auth::check() ? session()->getId() : null,
                'order_code' => $orderCode,
                'customer_name' => $request->full_name,
                // ...
                'customer_name' => $customerInfo['customer_name'],
                'customer_email' => $customerInfo['customer_email'],
                'customer_phone' => $customerInfo['customer_phone'],
                // Địa chỉ giao hàng
                'shipping_address_line1' => $customerInfo['shipping_address_line1'],
                'shipping_address_line2' => null,
                'shipping_zip_code' => $customerInfo['shipping_zip_code'] ?? null,
                'shipping_country' => 'Vietnam',
                'shipping_address_system' => $addressData['shipping_address_system'],
                'shipping_new_province_code' => $addressData['shipping_new_province_code'],
                'shipping_new_ward_code' => $addressData['shipping_new_ward_code'],
                'shipping_old_province_code' => $addressData['shipping_old_province_code'],
                'shipping_old_district_code' => $addressData['shipping_old_district_code'],
                'shipping_old_ward_code' => $addressData['shipping_old_ward_code'],
                // Địa chỉ thanh toán (mặc định giống địa chỉ giao hàng)
                'billing_address_line1' => $customerInfo['shipping_address_line1'],
                'billing_zip_code' => $customerInfo['shipping_zip_code'] ?? null,
                'billing_country' => 'Vietnam',
                'billing_old_province_code' => $addressData['shipping_old_province_code'],
                'billing_old_district_code' => $addressData['shipping_old_district_code'],
                'billing_old_ward_code' => $addressData['shipping_old_ward_code'],
                // Thông tin tài chính
                'sub_total' => $buyNowData['subtotal'],
                'shipping_fee' => $shippingFee,
                'discount_amount' => $totalDiscount,
                'grand_total' => $grandTotal,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cod' ? Order::PAYMENT_PENDING : Order::PAYMENT_PENDING,
                'shipping_method' => $request->shipping_method,
                'status' => Order::STATUS_PENDING_CONFIRMATION,
                'notes_from_customer' => $request->notes,
                'admin_note' => $adminNote,
                'desired_delivery_date' => $deliveryInfo['date'],
                'desired_delivery_time_slot' => $deliveryInfo['time_slot'],
                'store_location_id' => $customerInfo['store_location_id'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $item = $buyNowData['items']->first();
            $variant = $item->productVariant;
            $storeLocationId = $order->store_location_id;

            // Nếu không có store_location_id (giao hàng), tự động tìm kho có hàng
            if (!$storeLocationId) {
                $storeLocationId = $this->findAvailableStore($variant, $item->quantity);
                if (!$storeLocationId) {
                    $totalStock = $this->getSellableStock($variant);
                    throw new \Exception("Sản phẩm {$variant->product->name} không đủ hàng. Hiện chỉ còn {$totalStock} sản phẩm trong tất cả các kho.");
                }
                // Cập nhật store_location_id cho order
                $order->store_location_id = $storeLocationId;
                $order->save();
            } else {
                // Nếu có store_location_id (nhận tại cửa hàng), kiểm tra kho đó
                if (!$this->checkStockAvailability($variant, $item->quantity, $storeLocationId)) {
                    $availableStock = $variant->inventories()
                        ->where('store_location_id', $storeLocationId)
                        ->where('inventory_type', 'new')
                        ->sum('quantity');
                    $location = \App\Models\StoreLocation::find($storeLocationId);
                    throw new \Exception("Không đủ tồn kho cho sản phẩm {$variant->product->name} tại kho {$location->name}. Hiện chỉ còn {$availableStock} sản phẩm.");
                }
            }
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

            // Tạm giữ hàng thay vì trừ thẳng tồn kho
            $this->commitInventoryStock($variant, $item->quantity, $storeLocationId);

            // Kích hoạt chuyển kho tự động nếu cần
            $autoTransferService = new AutoStockTransferService();
            $transferResult = $autoTransferService->checkAndCreateAutoTransfer($order);

            if ($transferResult['success'] && !empty($transferResult['transfers_created'])) {
                Log::info('Đã tạo phiếu chuyển kho tự động cho đơn hàng Buy Now: ' . $order->order_code, $transferResult['transfers_created']);

                // Tự động xử lý phiếu chuyển kho nếu có thể (cùng tỉnh/thành)
                $this->processAutoTransfersIfPossible($transferResult['transfers_created']);
            }

            // --- XỬ LÝ TRỪ ĐIỂM ---
            if ($user && $pointsUsed > 0) {
                $user->decrement('loyalty_points_balance', $pointsUsed);
                LoyaltyPointLog::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'points' => -$pointsUsed,
                    'type' => 'spend',
                    'description' => "Sử dụng " . number_format($pointsUsed) . " điểm cho đơn hàng #{$order->order_code}",
                ]);
            }
            // --- Xử lý lượt dùng mã giảm giá ---
            $appliedCoupon = session('applied_coupon');
            if ($appliedCoupon && isset($appliedCoupon['id'])) {
                CouponUsage::create([
                    'coupon_id' => $appliedCoupon['id'],
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'usage_date' => now(),
                ]);
            }
            // Lưu địa chỉ mới vào sổ địa chỉ nếu người dùng chọn
            if (Auth::check() && $request->save_address && !$request->address_id) {
                $this->saveNewAddress($request);
            }

            // Xóa session Buy Now
            $this->clearBuyNowSession();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    // ...
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Lỗi khi xử lý đơn hàng Buy Now: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
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
        $variant = ProductVariant::with('product.coverImage', 'primaryImage')->findOrFail($buyNowSession['variant_id']);
        $items = collect([
            (object) [
                'id' => $variant->id,
                'productVariant' => $variant,
                'cartable' => $variant, // Để tương thích với logic đa hình
                'cartable_type' => ProductVariant::class, // Để tương thích với logic đa hình
                'price' => $buyNowSession['price'],
                'quantity' => $buyNowSession['quantity'],
                'stock_quantity' => $this->getSellableStock($variant),
                'points_to_earn' => $variant->points_awarded_on_purchase ?? 0, // Để tương thích với getCartData()
                'image' => $buyNowSession['image'] ?? ($variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) . '?v=' . time() : ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path)) ? Storage::url($variant->product->coverImage->path) . '?v=' . time() : asset('images/placeholder.jpg'))),
                'name' => $variant->name ?? $variant->product->name,
                'slug' => $variant->product->slug ?? '',
            ]
        ]);
        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
        $discount = session('applied_coupon.discount', 0);
        $pointsDiscount = 0;
        if (Auth::check()) {
            $pointsDiscount = session('points_applied.discount', 0);
        }
        $total = max(0, $subtotal - $discount - $pointsDiscount);

        // Tính tổng điểm thưởng sẽ nhận được
        $totalPointsToEarn = $items->sum(function ($item) {
            return ($item->productVariant->points_awarded_on_purchase ?? 0) * $item->quantity;
        });

        // Tính toán thông số vận chuyển
        $totalWeight = $items->sum(function ($item) {
            return ($item->productVariant->weight ?? 0) * $item->quantity;
        });
        $maxLength = $items->max(function ($item) {
            return $item->productVariant->dimensions_length ?? 0;
        });
        $maxWidth = $items->max(function ($item) {
            return $item->productVariant->dimensions_width ?? 0;
        });
        $totalHeight = $items->sum(function ($item) {
            return ($item->productVariant->dimensions_height ?? 0) * $item->quantity;
        });

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_from_coupon' => $discount, // Để rõ ràng hơn
            'discount_from_points' => $pointsDiscount, // Để rõ ràng hơn
            'pointsDiscount' => $pointsDiscount,
            'total' => $total,
            'voucher' => session('applied_coupon'), // Giữ nguyên để có thể dùng ở nơi khác
            'items_count' => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'totalPointsToEarn' => $totalPointsToEarn,
            'baseWeight' => $totalWeight > 0 ? $totalWeight : 1000,
            'baseLength' => $maxLength > 0 ? $maxLength : 20,
            'baseWidth' => $maxWidth > 0 ? $maxWidth : 10,
            'baseHeight' => $totalHeight > 0 ? $totalHeight : 10,
            'availableCoupons' => Coupon::where('status', 'active')->get(), // Buy Now VẪN áp dụng coupon
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
            'shipping_old_province_code' => null,
            'shipping_old_district_code' => null,
            'shipping_old_ward_code' => null,
            'shipping_new_province_code' => null,
            'shipping_new_ward_code' => null,
            'shipping_address_system' => 'old', // Mặc định sử dụng hệ thống cũ
        ];

        // Kiểm tra xem có phải là "Nhận tại cửa hàng" không - sử dụng delivery_method để nhất quán
        $deliveryMethod = $request->delivery_method ?? '';
        $shippingMethod = $request->shipping_method ?? '';
        $isPickup = $deliveryMethod === 'pickup' || str_contains(strtolower($shippingMethod), 'nhận tại cửa hàng');

        if ($isPickup) {
            // Nếu là nhận tại cửa hàng, không cần thông tin địa chỉ chi tiết
            return $addressData;
        }

        // Xác định hệ thống địa chỉ được sử dụng
        $addressSystem = $request->address_system ?? 'old';
        $addressData['shipping_address_system'] = $addressSystem;

        // Nếu sử dụng địa chỉ đã lưu
        if ($request->address_id) {
            $address = Address::findOrFail($request->address_id);

            $addressData['shipping_old_province_code'] = $address->old_province_code;
            $addressData['shipping_old_district_code'] = $address->old_district_code;
            $addressData['shipping_old_ward_code'] = $address->old_ward_code;
            $addressData['shipping_new_province_code'] = $address->new_province_code;
            $addressData['shipping_new_ward_code'] = $address->new_ward_code;
        } else {
            // Sử dụng hệ thống cũ hoặc mới tùy theo address_system
            if ($addressSystem === 'old') {
                $addressData['shipping_old_province_code'] = $request->province_code;
                $addressData['shipping_old_district_code'] = $request->district_code;
                $addressData['shipping_old_ward_code'] = $request->ward_code;
            } else {
                // Hệ thống mới - sử dụng province_id, district_id, ward_id
                $addressData['shipping_new_province_code'] = $request->province_id ?? $request->province_code;
                $addressData['shipping_new_ward_code'] = $request->ward_id ?? $request->ward_code;
                $addressData['shipping_old_province_code'] = $request->province_id ?? $request->province_code;
                $addressData['shipping_old_district_code'] = $request->district_id ?? $request->district_code;
                $addressData['shipping_old_ward_code'] = $request->ward_id ?? $request->ward_code;
            }
        }

        return $addressData;
    }

    /**
     * Helper method để kiểm tra tồn kho từ bảng product_inventories
     */
    private function checkStockAvailability(ProductVariant $variant, int $quantity, int $storeLocationId = null): bool
    {
        if (!$variant->manage_stock) {
            return true;
        }

        // Nếu có store_location_id, kiểm tra tồn kho tại kho cụ thể
        if ($storeLocationId) {
            $availableStock = $variant->inventories()
                ->where('store_location_id', $storeLocationId)
                ->where('inventory_type', 'new')
                ->sum('quantity');
            return $availableStock >= $quantity;
        } else {
            // Nếu không có store_location_id, kiểm tra tổng tồn kho
            $availableStock = $variant->inventories()
                ->where('inventory_type', 'new')
                ->sum('quantity');
            return $availableStock >= $quantity;
        }
    }

    /**
     * Tìm kho có đủ hàng khả dụng cho sản phẩm
     */
    private function findAvailableStore(ProductVariant $variant, int $quantity): ?int
    {
        if (!$variant->manage_stock) {
            return 1; // Trả về kho mặc định nếu không quản lý tồn kho
        }

        $inventory = $variant->inventories()
            ->where('inventory_type', 'new')
            ->where('quantity', '>=', $quantity)
            ->orderBy('quantity', 'desc') // Ưu tiên kho có nhiều hàng nhất
            ->first();

        return $inventory ? $inventory->store_location_id : null;
    }

    /**
     * Helper method để trừ tồn kho từ bảng product_inventories
     */
    private function decrementInventoryStock(ProductVariant $variant, int $quantity, int $storeLocationId): void
    {
        if (!$variant->manage_stock) {
            return;
        }

        // SỬA: Tìm tồn kho tại đúng kho cần trừ
        $inventory = $variant->inventories()
            ->where('store_location_id', $storeLocationId)
            ->where('inventory_type', 'new')
            ->first();

        if ($inventory && $inventory->quantity >= $quantity) {
            $inventory->decrement('quantity', $quantity);
        } else {
            $location = StoreLocation::find($storeLocationId);
            throw new \Exception("Không đủ tồn kho cho sản phẩm {$variant->product->name} tại kho {$location->name}.");
        }
    }

    /**
     * Tạm giữ tồn kho cho đơn hàng (sử dụng quantity_committed)
     * Thay vì trừ thẳng quantity như decrementInventoryStock
     */
    private function commitInventoryStock(ProductVariant $variant, int $quantity, int $storeLocationId): void
    {
        if (!$variant->manage_stock) {
            return;
        }

        // Tìm tồn kho tại kho được chỉ định
        $inventory = $variant->inventories()
            ->where('store_location_id', $storeLocationId)
            ->where('inventory_type', 'new')
            ->first();

        if ($inventory) {
            try {
                $inventory->commitStock($quantity);
                Log::info("Đã tạm giữ {$quantity} sản phẩm {$variant->product->name} tại kho {$inventory->storeLocation->name}");
            } catch (\Exception $e) {
                $location = StoreLocation::find($storeLocationId);
                throw new \Exception("Không đủ tồn kho có thể bán cho sản phẩm {$variant->product->name} tại kho {$location->name}. Lỗi: {$e->getMessage()}");
            }
        } else {
            $location = StoreLocation::find($storeLocationId);
            throw new \Exception("Không tìm thấy tồn kho cho sản phẩm {$variant->product->name} tại kho {$location->name}.");
        }
    }

    /**
     * Xuất kho thực tế khi đơn hàng được xác nhận giao hàng
     * Chuyển từ quantity_committed sang trừ quantity thực tế
     */
    public function fulfillOrderInventory(Order $order): void
    {
        $inventoryService = new InventoryCommitmentService();

        try {
            $inventoryService->fulfillInventoryForOrder($order);
            Log::info("Đã xuất kho thực tế cho đơn hàng {$order->order_code}");
        } catch (\Exception $e) {
            Log::error("Lỗi khi xuất kho cho đơn hàng {$order->order_code}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Thả tồn kho đã tạm giữ khi hủy đơn hàng
     */
    public function releaseOrderInventory(Order $order): void
    {
        $inventoryService = new InventoryCommitmentService();

        try {
            $inventoryService->releaseInventoryForOrder($order);
            Log::info("Đã thả tồn kho tạm giữ cho đơn hàng {$order->order_code}");
        } catch (\Exception $e) {
            Log::error("Lỗi khi thả tồn kho cho đơn hàng {$order->order_code}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Tự động xử lý các phiếu chuyển kho nếu có thể
     * (Áp dụng cho trường hợp cùng tỉnh/thành hoặc có thể xử lý tức thì)
     */
    private function processAutoTransfersIfPossible(array $transfersCreated): void
    {
        $autoTransferService = new AutoStockTransferService();

        foreach ($transfersCreated as $transferInfo) {
            try {
                $transfer = \App\Models\StockTransfer::find($transferInfo['transfer_id']);

                if ($transfer && $autoTransferService->canAutoProcessTransfer($transfer)) {
                    $result = $autoTransferService->autoProcessTransfer($transfer);

                    if ($result['success']) {
                        Log::info("Đã tự động xử lý phiếu chuyển kho {$transfer->transfer_code}: {$transferInfo['from_store']} → {$transferInfo['to_warehouse']}");
                    } else {
                        Log::warning("Không thể tự động xử lý phiếu chuyển kho {$transfer->transfer_code}: {$result['message']}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Lỗi khi tự động xử lý phiếu chuyển kho {$transferInfo['transfer_code']}: {$e->getMessage()}");
            }
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
     * Lưu địa chỉ mới vào sổ địa chỉ
     */
    private function saveNewAddress(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }

        $addressData = [
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number ?? $request->phone,
            'address_line1' => $request->address_line1 ?? $request->address,
            'address_system' => $request->address_system ?? 'old',
        ];

        // Thêm dữ liệu địa chỉ theo hệ thống
        if ($request->address_system === 'new') {
            $addressData['new_province_code'] = $request->province_code;
            $addressData['new_ward_code'] = $request->ward_code;
        } else {
            $addressData['old_province_code'] = $request->province_code;
            $addressData['old_district_code'] = $request->district_code;
            $addressData['old_ward_code'] = $request->ward_code;
        }

        // Kiểm tra xem có địa chỉ mặc định không, nếu không thì đặt làm mặc định
        $hasDefaultAddress = Address::where('user_id', Auth::id())
            ->where('is_default_shipping', true)
            ->exists();

        if (!$hasDefaultAddress) {
            $addressData['is_default_shipping'] = true;
        }

        Address::create($addressData);
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
        try {
            $request->validate([
                'province_name' => 'required|string',
                'district_name' => 'required|string',
                'ward_name' => 'required|string',
                'weight' => 'required|integer|min:10',
                'length' => 'nullable|integer|min:1',
                'width' => 'nullable|integer|min:1',
                'height' => 'nullable|integer|min:1',
            ]);

            // Set default values if not provided
            $length = $request->input('length', 20);
            $width = $request->input('width', 10);
            $height = $request->input('height', 10);
            $token = config('services.ghn.token');

            // Debug: Log request data (đã comment)
            // \Log::info('GHN API - Request data', [
            //     'province_name' => $request->province_name,
            //     'district_name' => $request->district_name,
            //     'ward_name' => $request->ward_name,
            //     'weight' => $request->weight,
            //     'length' => $length,
            //     'width' => $width,
            //     'height' => $height
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

            if (!$districtId) {
                // \Log::error('GHN API - Không tìm thấy quận/huyện GHN phù hợp', [
                //     'input' => $request->district_name,
                //     'normalized_input' => $this->normalize($request->district_name)
                // ]);
                return response()->json(['success' => false, 'message' => 'Không tìm thấy quận/huyện GHN phù hợp']);
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

            $fee = $ghn->calculateShippingFee((int) $districtId, (string) $wardCode, (int) $request->weight, (int) $length, (int) $width, (int) $height);

            if ($fee !== false && is_numeric($fee)) {
                // \Log::info('GHN API - Phí ship trả về', ['fee' => $fee, 'districtId' => $districtId, 'wardCode' => $wardCode]);
                return response()->json(['success' => true, 'fee' => $fee]);
            }

            // \Log::error('GHN API - Không lấy được phí vận chuyển từ GHN', [
            //     'districtId' => $districtId,
            //     'wardCode' => $wardCode,
            //     'weight' => $request->weight
            // ]);
            return response()->json(['success' => false, 'message' => 'Địa điểm này không được hỗ trợ giao hàng nhanh', 'fee' => null]);
        } catch (\Exception $e) {
            // \Log::error('GHN API Error: ' . $e->getMessage(), [
            //     'file' => $e->getFile(),
            //     'line' => $e->getLine(),
            //     'trace' => $e->getTraceAsString()
            // ]);
            return response()->json(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage(), 'fee' => null]);
        }
    }
    // Lấy danh sách cửa hàng theo tỉnh/huyện
    public function getStoreLocations(Request $request)
    {
        $provinceCode = $request->input('province_code');
        $districtCode = $request->input('district_code');
        $productVariantIds = $request->input('product_variant_ids', []);
        $query = StoreLocation::with(['province', 'district', 'ward'])
            ->where('is_active', true)
            ->where('type', 'store');
        // Lọc theo tỉnh/huyện nếu có
        if ($provinceCode) {
            $query->where('province_code', $provinceCode);
        }
        if ($districtCode) {
            $query->where('district_code', $districtCode);
        }
        // Nếu có danh sách sản phẩm, chỉ lấy cửa hàng có sản phẩm trong kho
        if (!empty($productVariantIds)) {
            $query->whereHas('productInventories', function ($inventoryQuery) use ($productVariantIds) {
                $inventoryQuery->whereIn('product_variant_id', $productVariantIds)
                    ->where('inventory_type', 'new')
                    ->where('quantity', '>', 0);
            });
        }
        $storeLocations = $query->get()->map(function ($location) use ($productVariantIds) {
            $storeData = [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address,
                'phone' => $location->phone,
                'full_address' => $location->full_address,
                'province_name' => $location->province ? $location->province->name_with_type : '',
                'district_name' => $location->district ? $location->district->name_with_type : '',
                'ward_name' => $location->ward ? $location->ward->name_with_type : '',
            ];
            // Nếu có danh sách sản phẩm, thêm thông tin tồn kho
            if (!empty($productVariantIds)) {
                $inventoryInfo = $location->productInventories()
                    ->whereIn('product_variant_id', $productVariantIds)
                    ->where('inventory_type', 'new')
                    ->where('quantity', '>', 0)
                    ->get()
                    ->map(function ($inventory) {
                        return [
                            'product_variant_id' => $inventory->product_variant_id,
                            'quantity' => $inventory->quantity,
                            'product_name' => $inventory->productVariant->product->name ?? 'N/A'
                        ];
                    });
                $storeData['available_products'] = $inventoryInfo;
                $storeData['total_available_items'] = $inventoryInfo->sum('quantity');
            }
            return $storeData;
        });
        return response()->json([
            'success' => true,
            'data' => $storeLocations
        ]);
    }
    // Lấy danh sách tỉnh/thành phố có cửa hàng
    public function getProvincesWithStores()
    {
        $provinces = StoreLocation::with('province')
            ->where('is_active', true)
            ->where('type', 'store')
            ->whereNotNull('province_code')
            ->get()
            ->pluck('province')
            ->unique('code')
            ->filter()
            ->values()
            ->map(function ($province) {
                return [
                    'code' => $province->code,
                    'name' => $province->name_with_type
                ];
            });
        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
    }
    // Lấy danh sách quận/huyện có cửa hàng theo tỉnh
    public function getDistrictsWithStores(Request $request)
    {
        $provinceCode = $request->input('province_code');

        if (!$provinceCode) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn tỉnh/thành phố'
            ], 400);
        }
        $districts = StoreLocation::with('district')
            ->where('is_active', true)
            ->where('type', 'store')
            ->where('province_code', $provinceCode)
            ->whereNotNull('district_code')
            ->get()
            ->pluck('district')
            ->unique('code')
            ->filter()
            ->values()
            ->map(function ($district) {
                return [
                    'code' => $district->code,
                    'name' => $district->name_with_type
                ];
            });
        return response()->json([
            'success' => true,
            'data' => $districts
        ]);
    }
    /**
     * Gửi thông báo Telegram cho admin
     */
    private function sendTelegramNotification($prefix, $order, $confirmationUrl = null)
    {
        try {
            $text = sprintf(
                "%s\n\n*Mã ĐH:* `%s`\n*Khách hàng:* %s\n*Tổng tiền:* %s VNĐ",
                $prefix,
                $order->order_code,
                $order->customer_name,
                number_format($order->grand_total)
            );

            $messageData = [
                'chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
                'text' => $text,
                'parse_mode' => 'Markdown'
            ];

            // Thêm nút xác nhận nếu có URL
            if ($confirmationUrl) {
                $messageData['reply_markup'] = json_encode([
                    'inline_keyboard' => [[
                        ['text' => '✅ Xác nhận đã thanh toán', 'url' => $confirmationUrl]
                    ]]
                ]);
            }

            Telegram::sendMessage($messageData);
        } catch (\Exception $e) {
            Log::error('Lỗi gửi thông báo Telegram: ' . $e->getMessage());
        }
    }

    public function confirmPaymentByToken($token)
    {
        // Tìm đơn hàng với token hợp lệ và đang chờ xác nhận
        $order = Order::where('confirmation_token', $token)
            ->where('status', Order::STATUS_PENDING_CONFIRMATION) // Sử dụng hằng số nếu có
            ->first();

        if (!$order) {
            // Có thể đơn hàng đã được xác nhận hoặc token không tồn tại
            return response('<h1>Link không hợp lệ hoặc đơn hàng đã được xử lý.</h1>', 404);
        }

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái
            $order->status = 'processing'; // Chuyển sang "Đang xử lý"
            $order->payment_status = Order::PAYMENT_PAID; // Sử dụng hằng số nếu có
            $order->paid_at = now();
            $order->save();

            // Trừ tồn kho
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::find($item->product_variant_id);
                    if ($variant) {
                        $this->decrementInventoryStock($variant, $item->quantity, $order->store_location_id ?? 1);
                    }
                }
            }

            // Kích hoạt chuyển kho tự động
            $autoTransferService = new AutoStockTransferService();
            $transferResult = $autoTransferService->checkAndCreateAutoTransfer($order);

            if ($transferResult['success'] && !empty($transferResult['transfers_created'])) {
                Log::info('Đã tạo phiếu chuyển kho tự động cho đơn hàng QR: ' . $order->order_code, $transferResult['transfers_created']);
            }

            // Kích hoạt gửi email sản phẩm cho khách (sẽ làm ở bước sau)
            // \Mail::to($order->customer_email)->send(new \App\Mail\ProductLinkMail($order));

            DB::commit();

            return response("<h1>Xác nhận thành công!</h1><p>Đơn hàng <strong>{$order->order_code}</strong> đã được cập nhật.</p>");
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Lỗi xác nhận thanh toán: ' . $e->getMessage());
            return response('<h1>Đã có lỗi xảy ra!</h1><p>Vui lòng thử lại hoặc liên hệ quản trị viên.</p>', 500);
        }
    }
}
