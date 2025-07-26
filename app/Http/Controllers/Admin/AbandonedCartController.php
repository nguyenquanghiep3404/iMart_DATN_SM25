<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\AbandonedCartReminder;
use App\Models\AbandonedCartLog;
use App\Mail\AbandonedCartMail;
use Illuminate\Support\Facades\Mail;
class AbandonedCartController  extends Controller
{
    public function index(Request $request)
    {
        $query = AbandonedCart::with([
            'user',
            'cart.items.cartable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\ProductVariant::class => ['product'],
                    \App\Models\TradeInItem::class => [],
                ]);
            }
        ])
        // Lọc theo trạng thái giỏ hàng (pending, recovered, archived)
        ->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->input('status'));
        })

        // Lọc theo trạng thái liên hệ: email và in-app
        ->when($request->filled('contact_status'), function ($q) use ($request) {
            $contact = $request->input('contact_status');
            if ($contact === 'not_sent_email') {
                $q->where('email_status', 'unsent');
            } elseif ($contact === 'sent_email') {
                $q->where('email_status', 'sent');
            } elseif ($contact === 'not_sent_in_app') {
                $q->where('in_app_notification_status', 'unsent');
            } elseif ($contact === 'sent_in_app') {
                $q->where('in_app_notification_status', 'sent');
            }
        })

        // Lọc theo ngày bắt đầu
        ->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->input('date_from'));
        })

        // Lọc theo ngày kết thúc
        ->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->input('date_to'));
        })

        // Tìm kiếm theo tên/email người dùng hoặc email khách vãng lai
        ->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->input('search');
            $q->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('guest_email', 'like', "%{$search}%");
            });
        });

        // Lấy danh sách giỏ hàng và tổng số lượng
        $abandonedCarts = $query->latest()->paginate(15)->withQueryString();
        $totalAbandonedCarts = AbandonedCart::count();

        return view('admin.abandoned_carts.index', compact('abandonedCarts', 'totalAbandonedCarts'));
    }
    public function show($id)
    {
        $cart = \App\Models\AbandonedCart::with([
            'user',
            'cart.items.cartable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\ProductVariant::class => ['product'],
                    \App\Models\TradeInItem::class => [],
                ]);
            }
        ])->findOrFail($id);

        $total = $cart->cart->items->sum(function ($item) {
            $price = $item->price ?? ($item->cartable->price ?? 0);
            return $item->quantity * $price;
        });

       $logs = \App\Models\AbandonedCartLog::with('causer')
            ->where('abandoned_cart_id', $cart->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.abandoned_carts.show', compact('cart', 'total','logs'));
    } 
    public function sendInApp($id)
    {
        $abandonedCart = AbandonedCart::with('user')->find($id);

        if (!$abandonedCart) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy giỏ hàng.']);
        }

        if (!$abandonedCart->user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng.']);
        }

        try {
            // ✅ CHỈ gửi in-app notification
            $abandonedCart->user->notify(new AbandonedCartReminder(['database']));

            $abandonedCart->in_app_notification_status = 'sent';
            $abandonedCart->save();

            AbandonedCartLog::create([
                'abandoned_cart_id' => $abandonedCart->id,
                'action' => 'sent_in_app_notification',
                'description' => 'Đã gửi thông báo in-app cho  ' . $abandonedCart->user->email,
                'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                'causer_id' => auth()->id(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi khi gửi thông báo.']);
        }

        return response()->json(['success' => true, 'message' => 'Đã gửi thông báo in-app thành công.']);
    }
    public function sendEmail($id)
    {
        $abandonedCart = AbandonedCart::findOrFail($id);

        if (!$abandonedCart->user || !$abandonedCart->user->email) {
            return response()->json(['message' => 'Không có email để gửi.'], 400);
        }
        try {
            Mail::to($abandonedCart->user->email)
                ->send(new AbandonedCartMail($abandonedCart));
            $abandonedCart->email_status = 'sent'; 
            $abandonedCart->last_notified_at = now(); 
            $abandonedCart->save();
            // Tạo log hành động gửi email
            AbandonedCartLog::create([
                'abandoned_cart_id' => $abandonedCart->id,
                'action' => 'sent_email',
                'description' => 'Đã gửi email khôi phục cho  ' . $abandonedCart->user->email,
                'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                'causer_id' => auth()->id(),
            ]);
            return response()->json(['message' => 'Đã gửi email thành công.']);
        } catch (\Exception $e) {
            $abandonedCart->email_status = 'failed';
            $abandonedCart->save();

            return response()->json(['message' => 'Lỗi gửi mail: ' . $e->getMessage()], 500);
        }
    }
    public function bulkSendEmail(Request $request)
    {
        $ids = $request->input('cart_ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Không có giỏ hàng nào được chọn.']);
        }

        $carts = AbandonedCart::with('user')->whereIn('id', $ids)->get();

        foreach ($carts as $cart) {
            if (!$cart->user || !$cart->user->email) continue;

            try {
                Mail::to($cart->user->email)->send(new AbandonedCartMail($cart));
                $cart->update(['email_status' => 'sent', 'last_notified_at' => now()]);

                AbandonedCartLog::create([
                    'abandoned_cart_id' => $cart->id,
                    'action' => 'sent_email',
                    'description' => 'Đã gửi email hàng loạt cho ' . $cart->user->email,
                    'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                    'causer_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                \Log::error("Bulk email failed for cart {$cart->id}: {$e->getMessage()}");
            }
        }

        return response()->json(['success' => true, 'message' => 'Đã gửi email cho các giỏ hàng được chọn.']);
    }

    public function bulkSendInApp(Request $request)
    {
        $ids = $request->input('cart_ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Không có giỏ hàng nào được chọn.']);
        }

        $carts = AbandonedCart::with('user')->whereIn('id', $ids)->get();

        foreach ($carts as $cart) {
            if (!$cart->user) continue;

            try {
                $cart->user->notify(new AbandonedCartReminder(['database']));
                $cart->update(['in_app_notification_status' => 'sent']);

                AbandonedCartLog::create([
                    'abandoned_cart_id' => $cart->id,
                    'action' => 'sent_in_app_notification',
                    'description' => 'Đã gửi in-app hàng loạt cho ' . $cart->user->email,
                    'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                    'causer_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                \Log::error("Bulk in-app failed for cart {$cart->id}: {$e->getMessage()}");
            }
        }

        return response()->json(['success' => true, 'message' => 'Đã gửi in-app cho các giỏ hàng được chọn.']);
    }

}
