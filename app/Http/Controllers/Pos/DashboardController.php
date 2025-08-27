<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\InventorySerial;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemSerial;
use App\Models\PosSession;
use App\Models\ProductInventory;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /**
     * Hiển thị giao diện bán hàng POS chính.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $posSession = PosSession::where('user_id', $user->id)
                                ->where('status', 'open')
                                ->with(['register.storeLocation'])
                                ->first();

        if (!$posSession) {
            $registerId = session('selected_register_id');
            if ($registerId) {
                return redirect()->route('pos.sessions.manage', ['register_id' => $registerId]);
            }
            return redirect()->route('pos.selection.index')->with('error', 'Vui lòng mở ca làm việc trước.');
        }

        $storeLocationId = $posSession->register->store_location_id;

        // Cập nhật truy vấn để lấy số lượng tồn kho
        $products = ProductVariant::with(['primaryImage', 'product'])
            ->select('product_variants.*', DB::raw('COALESCE(pi.quantity, 0) as stock_quantity'))
            ->leftJoin('product_inventories as pi', function($join) use ($storeLocationId) {
                $join->on('product_variants.id', '=', 'pi.product_variant_id')
                     ->where('pi.store_location_id', '=', $storeLocationId);
            })
            ->where('product_variants.status', 'active')
            ->where('pi.quantity', '>', 0) // Lọc sản phẩm có tồn kho > 0
            ->orderByDesc('product_variants.created_at')
            ->take(20) // Lấy nhiều sản phẩm hơn để hiển thị
            ->get();
            
        $categories = Category::where('status', 'active')
            ->whereHas('products.variants.inventories', function ($query) use ($storeLocationId) {
                $query->where('store_location_id', $storeLocationId)
                      ->where('quantity', '>', 0);
            })
            ->get(['id', 'name']);

        return view('pos.dashboard.index', compact('user', 'posSession', 'products', 'categories'));
    }

    /**
     * Tìm kiếm sản phẩm cho POS.
     */
    public function searchProducts(Request $request)
    {
        $term = $request->input('term', '');

        if (empty($term)) {
            return response()->json([]);
        }

        $posSession = PosSession::where('user_id', Auth::id())
                                ->where('status', 'open')
                                ->first();

        if (!$posSession) {
            return response()->json(['error' => 'No active session'], 400);
        }

        $storeLocationId = $posSession->register->store_location_id;

        // Cập nhật truy vấn để lấy số lượng tồn kho
        $results = ProductVariant::with(['product', 'primaryImage'])
            ->select('product_variants.*', DB::raw('COALESCE(pi.quantity, 0) as stock_quantity'))
            ->leftJoin('product_inventories as pi', function($join) use ($storeLocationId) {
                $join->on('product_variants.id', '=', 'pi.product_variant_id')
                     ->where('pi.store_location_id', '=', $storeLocationId);
            })
            ->where('product_variants.status', 'active')
            ->where('pi.quantity', '>', 0)
            ->where(function ($query) use ($term) {
                $query->where('product_variants.sku', 'LIKE', "%{$term}%")
                      ->orWhereHas('product', function ($q) use ($term) {
                          $q->where('name', 'LIKE', "%{$term}%");
                      });
            })
            ->take(10)
            ->get();

        return response()->json($results);
    }

    /**
     * Tìm kiếm khách hàng.
     */
    public function searchCustomers(Request $request)
    {
        $term = $request->input('term', '');
        if (strlen($term) < 2) {
            return response()->json([]);
        }
        $customers = User::where(function ($query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('email', 'LIKE', "%{$term}%")
                  ->orWhere('phone_number', 'LIKE', "%{$term}%");
        })
        ->whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', ['admin', 'sales_staff', 'shipper', 'content_manager']);
        })
        ->take(10)
        ->get(['id', 'name', 'email', 'phone_number']);
        return response()->json($customers);
    }

    /**
     * Xác thực Serial Number.
     */
    public function validateSerial(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'product_variant_id' => 'required|integer|exists:product_variants,id',
        ]);
        $posSession = PosSession::where('user_id', Auth::id())
                                ->where('status', 'open')
                                ->firstOrFail();
        $storeLocationId = $posSession->register->store_location_id;
        $serial = InventorySerial::where('serial_number', $request->serial_number)
            ->where('product_variant_id', $request->product_variant_id)
            ->where('store_location_id', $storeLocationId)
            ->where('status', 'available')
            ->first();
        if ($serial) {
            return response()->json(['valid' => true]);
        }
        return response()->json(['valid' => false, 'message' => 'Serial không hợp lệ hoặc không có sẵn tại kho này.'], 422);
    }

    /**
     * Xử lý và hoàn tất đơn hàng từ POS.
     */
    public function processSale(Request $request)
    {
        $validated = $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.product.id' => 'required|integer|exists:product_variants,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.imeis' => 'present|array',
            'customer' => 'required|array',
            'customer.id' => 'nullable|integer|exists:users,id',
            'customer.name' => 'required_without:customer.id|string|max:255',
            'customer.phone_number' => 'required_without:customer.id|string|max:255|unique:users,phone_number',
            'customer.email' => 'nullable|email|max:255|unique:users,email',
            'payment' => 'required|array',
            'payment.method' => 'required|string',
            'payment.amount_received' => 'required|numeric|min:0',
        ]);

        $posSession = PosSession::where('user_id', Auth::id())->where('status', 'open')->with('register.storeLocation')->firstOrFail();
        $storeLocationId = $posSession->register->store_location_id;

        try {
            $order = DB::transaction(function () use ($validated, $posSession, $storeLocationId) {
                // Bước 1: Xử lý khách hàng
                $customerData = $validated['customer'];
                if (!empty($customerData['id'])) {
                    $customer = User::findOrFail($customerData['id']);
                } else {
                    $customer = User::create([
                        'name' => $customerData['name'],
                        'phone_number' => $customerData['phone_number'],
                        'email' => $customerData['email'] ?? null,
                        'password' => Hash::make(Str::random(10)),
                    ]);
                }

                // Bước 2: Tính toán tổng tiền ở backend
                $subTotal = 0;
                foreach ($validated['cart'] as $item) {
                    $variant = ProductVariant::find($item['product']['id']);
                    $subTotal += $variant->price * $item['quantity'];
                }
                $taxAmount = $subTotal * 0.08;
                $grandTotal = $subTotal + $taxAmount;

                // Bước 3: Tạo đơn hàng
                $order = Order::create([
                    'channel' => 'pos',
                    'user_id' => $customer->id,
                    'store_location_id' => $storeLocationId,
                    'register_id' => $posSession->register_id,
                    'pos_session_id' => $posSession->id,
                    'order_code' => 'POS-' . time() . '-' . Str::upper(Str::random(4)),
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone_number,
                    
                    // === THÊM DÒNG NÀY ===
                    'shipping_address_line1' => $posSession->register->storeLocation->address,

                    'sub_total' => $subTotal,
                    'tax_amount' => $taxAmount,
                    'grand_total' => $grandTotal,
                    'payment_method' => $validated['payment']['method'],
                    'payment_status' => 'paid',
                    'status' => 'delivered',
                    'paid_at' => now(),
                    'delivered_at' => now(),
                    'processed_by' => Auth::id(),
                ]);

                // Bước 4: Thêm các sản phẩm vào đơn hàng và cập nhật tồn kho
                foreach ($validated['cart'] as $itemData) {
                    $variant = ProductVariant::find($itemData['product']['id']);
                    $orderItem = $order->items()->create([
                        'product_variant_id' => $variant->id,
                        'product_name' => $variant->product->name,
                        'sku' => $variant->sku,
                        'quantity' => $itemData['quantity'],
                        'price' => $variant->price,
                        'total_price' => $variant->price * $itemData['quantity'],
                    ]);

                    ProductInventory::where('product_variant_id', $variant->id)
                        ->where('store_location_id', $storeLocationId)
                        ->decrement('quantity', $itemData['quantity']);

                    if ($variant->has_serial_tracking && !empty($itemData['imeis'])) {
                        foreach ($itemData['imeis'] as $serialNumber) {
                            InventorySerial::where('serial_number', $serialNumber)
                                ->where('product_variant_id', $variant->id)
                                ->where('store_location_id', $storeLocationId)
                                ->update(['status' => 'sold']);
                            
                            OrderItemSerial::create([
                                'order_item_id' => $orderItem->id,
                                'product_variant_id' => $variant->id,
                                'serial_number' => $serialNumber,
                                'status' => 'sold',
                            ]);
                        }
                    }
                }

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng thành công!',
                'order_code' => $order->order_code,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }
}