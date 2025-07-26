@extends($layout)

@section('title', 'Phiên hết hạn quyền truy cập')


@section('content')

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiên làm việc đã hết hạn - Lỗi 419</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
        }
        h1 {
            color: #d9534f; /* Màu đỏ nhẹ cho lỗi */
            margin-bottom: 20px;
            font-size: 2.5em;
        }
        p {
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .icon {
            font-size: 4em;
            color: #d9534f;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #007bff; /* Màu xanh dương cho nút */
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⏳</div>
        <h1>Phiên làm việc của bạn đã hết hạn</h1>
        <p>Có vẻ như phiên làm việc của bạn đã kết thúc. Điều này thường xảy ra vì lý do bảo mật để bảo vệ thông tin của bạn.</p>
        <p>Vui lòng làm mới trang hoặc quay lại trang chủ để tiếp tục.</p>
        <a href="javascript:location.reload();" class="button">Làm mới trang</a>
        <a href="/" class="button" style="margin-left: 15px;">Quay lại trang chủ</a>
    </div>
</body>
</html>
@endsection

