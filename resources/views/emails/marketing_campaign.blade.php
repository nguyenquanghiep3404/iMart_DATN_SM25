<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $subjectLine ?? 'Marketing Campaign' }}</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f7; margin: 0; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0"
        style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <tr>
            <td style="text-align: center; padding-bottom: 20px;">
                <h1 style="color: #333333; margin: 0;">{{ $campaignName }}</h1>
            </td>
        </tr>
        <tr>
            <td style="color: #555555; font-size: 16px; line-height: 1.5;">
                {!! $contentHtml !!}
            </td>
        </tr>
        @if (!empty($voucherCode))
            <tr>
                <td style="padding: 30px 0; text-align: center;">
                    <p
                        style="font-weight: bold; font-size: 18px; color: #ffffff; background-color: #28a745; display: inline-block; padding: 15px 25px; border-radius: 6px; letter-spacing: 3px;">
                        Mã voucher của bạn: {{ $voucherCode }}
                    </p>
                </td>
            </tr>
        @endif
        <tr>
            <td style="font-size: 14px; color: #999999; text-align: center; padding-top: 20px;">
                <p>Cảm ơn bạn đã đồng hành cùng iMart!</p>
            </td>
        </tr>
    </table>
</body>

</html>
