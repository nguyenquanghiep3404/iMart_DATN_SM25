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
            'payment_method' => 'required|string|in:cod,bank_transfer,vnpay',
            'notes' => 'nullable|string|max:1000',
        ]);
        // Kiểm tra giỏ hàng
        $cartData = $this->getCartData();
        if ($cartData['items']->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng đang trống.'], 400);
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
                return (object)[
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
}
