<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VNPayController extends Controller
{
    /**
     * Chuyển hướng người dùng đến cổng thanh toán VNPAY
     */
    public function createPayment(Request $request)
    {
        // Lấy dữ liệu từ request, ví dụ:
        // $amount = $request->input('amount');
        // $order_id = $request->input('order_id');

        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $vnp_Url = env('VNPAY_URL');
        $vnp_Returnurl = env('VNPAY_RETURN_URL');
        $vnp_TmnCode = env('VNPAY_TMN_CODE'); // Mã website
        $vnp_HashSecret = env('VNPAY_HASH_SECRET'); // Chuỗi bí mật

        // Các thông tin cần thiết cho giao dịch
        $vnp_TxnRef = time(); // Mã đơn hàng. Phải là duy nhất.
        $vnp_OrderInfo = 'Thanh toan don hang test'; // Nội dung thanh toán
        $vnp_OrderType = 'billpayment'; // Loại hàng hóa
        $vnp_Amount = 10000 * 100; // Số tiền. VNPAY yêu cầu nhân 100.
        $vnp_Locale = 'vn'; // Ngôn ngữ
        $vnp_BankCode = ''; // Mã ngân hàng. Trống để khách chọn.
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; // IP của khách

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

        // Sắp xếp dữ liệu theo key
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
            // Tạo chữ ký bảo mật
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Chuyển hướng người dùng sang VNPAY
        return redirect($vnp_Url);
    }

    /**
     * Xử lý kết quả VNPAY trả về
     */
    public function handleReturn(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);

        // Sắp xếp dữ liệu và tạo chuỗi hash
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
        
        // So sánh chữ ký
        if ($secureHash == $vnp_SecureHash) {
            // Mã phản hồi `00` là giao dịch thành công
            if ($inputData['vnp_ResponseCode'] == '00') {
                // TODO: Xử lý logic khi thanh toán thành công
                // 1. Lấy ra mã đơn hàng: $inputData['vnp_TxnRef']
                // 2. Kiểm tra xem đơn hàng này đã được cập nhật chưa
                // 3. Cập nhật trạng thái đơn hàng trong Database
                // 4. Trả về view thông báo thành công
                
                return "<h1>Giao dịch thành công</h1><p>Mã đơn hàng: " . $inputData['vnp_TxnRef'] . "</p>";

            } else {
                // TODO: Xử lý khi giao dịch thất bại
                return "<h1>Giao dịch không thành công</h1>";
            }
        } else {
            // TODO: Xử lý khi chữ ký không hợp lệ
            return "<h1>Chữ ký không hợp lệ</h1>";
        }
    }
}
