<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * Hiển thị trang quản lý ca làm việc (mở hoặc đóng ca).
     */
    public function index(Request $request)
    {
        $request->validate(['register_id' => 'required|exists:registers,id']);
        
        $register = Register::findOrFail($request->register_id);
        $user = Auth::user();

        // Tìm ca làm việc đang mở trên máy POS này
        $posSession = PosSession::where('register_id', $register->id)
                                ->where('status', 'open')
                                ->with('user') // Lấy thông tin người đang mở ca
                                ->first();

        // Khởi tạo chi tiết doanh thu
        $revenueDetails = [
            'cash' => 0,
            'card' => 0,
            'qr' => 0,
            'total' => 0,
        ];

        // Nếu có ca đang mở, tính toán doanh thu
        if ($posSession) {
            $sales = Order::where('pos_session_id', $posSession->id)
                          ->where('payment_status', 'paid') // Chỉ tính các đơn đã thanh toán
                          ->select('payment_method', DB::raw('SUM(grand_total) as total'))
                          ->groupBy('payment_method')
                          ->pluck('total', 'payment_method');

            // SỬA LỖI: Dùng đúng key ('cash', 'card', 'qr') thay vì Tiếng Việt
            $revenueDetails['cash'] = $sales->get('cash', 0);
            $revenueDetails['card'] = $sales->get('card', 0);
            $revenueDetails['qr'] = $sales->get('qr', 0);
            $revenueDetails['total'] = $sales->sum();
        }

        return view('pos.sessions.manage', compact('register', 'user', 'posSession', 'revenueDetails'));
    }

    /**
     * Mở một ca làm việc mới.
     */
    public function open(Request $request)
    {
        $validated = $request->validate([
            'register_id' => 'required|exists:registers,id',
            'opening_balance' => 'required|string',
        ]);

        $existingSession = PosSession::where('register_id', $validated['register_id'])
                                     ->where('status', 'open')
                                     ->exists();
        if ($existingSession) {
            return back()->with('error', 'Máy POS này đã có người mở ca. Vui lòng đóng ca trước.');
        }

        PosSession::create([
            'register_id' => $validated['register_id'],
            'user_id' => Auth::id(),
            'opening_balance' => (float) str_replace(['.', ','], '', $validated['opening_balance']),
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return redirect()->route('pos.dashboard.index')->with('success', 'Đã mở ca làm việc thành công!');
    }

    /**
     * Đóng ca làm việc hiện tại.
     */
    public function close(Request $request, PosSession $posSession)
    {
        $validated = $request->validate([
            'closing_balance' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Tính toán lại doanh thu để lưu vào DB
        $sales = Order::where('pos_session_id', $posSession->id)
                      ->where('payment_status', 'paid')
                      ->where('payment_method', 'cash')
                      ->sum('grand_total');
            
        $expectedCash = $posSession->opening_balance + $sales;

        $posSession->update([
            'closing_balance' => (float) str_replace(['.', ','], '', $validated['closing_balance']),
            'calculated_balance' => $expectedCash,
            'notes' => $validated['notes'],
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return redirect()->route('pos.selection.index')->with('success', 'Đã đóng ca và tổng kết doanh thu thành công.');
    }
}