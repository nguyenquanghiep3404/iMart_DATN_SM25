<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông báo hoàn tiền</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px;">

    <div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ config('app.url') }}/assets/users/app-icons/logoiMart.jpg" alt="iMart Logo" style="max-height: 60px;">
        </div>
        <h2 style="color: #1f2937;">🎉 Xin chào {{ $returnRequest->user->name ?? 'Quý khách' }},</h2>

        <p style="font-size: 16px; color: #374151;">
            Yêu cầu hoàn tiền của bạn với mã <strong style="color: #111827;">{{ $returnRequest->return_code }}</strong> đã được <strong style="color: #16a34a;">xử lý thành công</strong>.
        </p>

        <table cellpadding="10" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="font-weight: bold; color: #374151;">🔁 Phương thức hoàn tiền:</td>
                <td style="color: #1f2937;">{{ $returnRequest->refund_method_text }}</td><br>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #374151;">💰 Số tiền:</td>
                <td style="color: #dc2626;"><strong>{{ number_format($returnRequest->refund_amount) }} VNĐ</strong></td>
            </tr>

            @if ($returnRequest->refund_method === 'points')
            <tr>
                <td style="font-weight: bold; color: #374151;">⭐ Điểm được cộng:</td>
                <td style="color: #f59e0b;"><strong>{{ $returnRequest->refunded_points }} điểm</strong></td>
            </tr>
            @endif
        </table>

        <p style="margin-top: 20px; font-size: 16px; color: #374151;">
            Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với bộ phận hỗ trợ của chúng tôi.
        </p>

        <p style="margin-top: 30px; color: #6b7280;">
            Trân trọng,<br>
            <strong style="color: #111827;">Đội ngũ hỗ trợ iMart</strong>
        </p>

        <div style="margin-top: 40px; text-align: center;">
            <a href="{{ config('app.url') }}"
                style="background-color: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                Quay về trang chủ
            </a>
        </div>
    </div>

</body>

</html>