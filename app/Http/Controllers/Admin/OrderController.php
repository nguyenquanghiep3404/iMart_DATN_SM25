<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with([
            'user:id,name,email,phone_number',
            'items.productVariant.product:id,name',
            'items.productVariant.product.coverImage',
            'processor:id,name',
            'shipper:id,name'
        ]);
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        if ($request->filled('date_range')) {
            $query->whereDate('created_at', $request->get('date_range'));
        }
        $query->orderBy('created_at', 'desc');
        $orders = $query->paginate(10);
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ]
            ]);
        }
        return view('admin.orders.index', compact('orders'));
    }
    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email,phone_number',
            'items.productVariant.product:id,name',
            'items.productVariant.product.coverImage',
            'processor:id,name',
            'shipper:id,name'
        ]);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
}
