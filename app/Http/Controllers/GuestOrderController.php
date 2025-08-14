<?php

// app/Http/Controllers/GuestOrderController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class GuestOrderController extends Controller
{
    // Trang hiển thị form tra cứu
    public function index()
    {
        return view('users.guest.orders.form');
    }

    // Xử lý tra cứu AJAX
    public function lookupAjax(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string|max:255',
        ]);

        $order = Order::with([
            'items.variant.product',
            'shippingProvince',
            'shippingDistrict',
            'shippingWard',
            'items.variant.primaryImage'
        ])->where('order_code', $request->order_code)->first();

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng.'], 404);
        }

        return response()->json([
            'order_code' => $order->order_code,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'shipping_method' => $order->shipping_method,
            'shipping_address_line1' => $order->shipping_address_line1,
            'shipping_address_line2' => $order->shipping_address_line2,
            'shipping_province' => $order->shippingProvince?->name,
            'shipping_district' => $order->shippingDistrict?->name,
            'shipping_ward' => $order->shippingWard?->name,
            'desired_delivery_date' => $order->desired_delivery_date,
            'desired_delivery_time_slot' => $order->desired_delivery_time_slot,
            'sub_total' => $order->sub_total,
            'shipping_fee' => $order->shipping_fee,
            'discount_amount' => $order->discount_amount,
            'grand_total' => $order->grand_total,
            'status' => $order->status,
            'timestamps' => [
                'created_at' => $order->created_at,
                'processed_at' => $order->processed_at,
                'shipped_at' => $order->shipped_at,
                'delivered_at' => $order->delivered_at,
            ],
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'items' => $order->items->map(function ($item) {
                $variant = $item->variant;

                return [
                    'product_name' => $variant?->product?->name ?? 'Không rõ',
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'image_url' => $variant?->image_url ?? asset('images/placeholder.png'),
                ];
            }),

        ]);
    }
}
