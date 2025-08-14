<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Chuyển Khoản qua QR - iMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
   <style>
        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        body {
            background: linear-gradient(-45deg, #ff4444, #8B0000, #ff6666, #cc0000);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
        }

        .main-card {
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            overflow: hidden;
        }

        .main-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(139, 0, 0, 0.3) !important;
        }

        .qr-hover {
            transition: transform 0.3s ease;
            border: 3px solid #8B0000;
        }

        .qr-hover:hover {
            transform: scale(1.05);
        }

        .fire-effect {
            position: relative;
            overflow: hidden;
        }

        .fire-effect::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            pointer-events: none;
                /* background: linear-gradient(45deg, transparent, rgba(255, 68, 68, 0.2), transparent); */
                /* animation: fire 2s linear infinite; */
        }

        @keyframes fire {
            0% {
                transform: rotate(0deg) translate(-25%, -25%);
            }

            100% {
                transform: rotate(360deg) translate(-25%, -25%);
            }
        }

        .text-red {
            color: #8B0000 !important;
        }

        .alert-custom {
            background: rgba(139, 0, 0, 0.1);
            border: 2px solid #8B0000;
            color: #8B0000;
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <main role="main" class="container py-5">
        <div class="main-card card shadow-lg border-0 rounded-4 fire-effect">
            <div class="card-header bg-gradient text-white text-center rounded-top-4" style="background: linear-gradient(135deg, #8B0000, #ff4444); border-bottom: 3px solid white;">
                <h4 class="mb-0 fw-bold"><i class="bi bi-qr-code-scan"></i> THANH TOÁN NHANH QUA QR</h4>
            </div>
            <div class="card-body p-5" style="background: rgba(255, 255, 255, 0.95);">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-4 mb-md-0 text-center">
                        <img src="https://img.vietqr.io/image/MB-0971647692-qr_only.png?addInfo={{ urlencode($order->order_code) }}&amount={{ $order->grand_total }}"
                             alt="QR Code" class="qr-hover img-fluid rounded-3 shadow" style="max-width: 300px;">
                        <p class="mt-3 text-muted fst-italic">Quét mã và chuyển khoản theo hướng dẫn</p>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-3" style="background: linear-gradient(145deg, #ffffff, #fff0f0);">
                            <h5 class="mb-3 text-red fw-bold"><i class="bi bi-wallet2"></i> THÔNG TIN CHUYỂN KHOẢN</h5>
                            <ul class="list-unstyled fs-5">
                                <li class="mb-3"><span class="text-red"><i class="bi bi-building"></i> Ngân hàng:</span> <strong>MBBank</strong></li>
                                <li class="mb-3"><span class="text-red"><i class="bi bi-credit-card-2-front"></i> Số TK:</span> <strong class="text-decoration-underline">0971 647 692</strong></li>
                                <li class="mb-3"><span class="text-red"><i class="bi bi-person-circle"></i> Chủ TK:</span> <strong>Nguyễn Quang Hiệp</strong></li>
                                <li class="mb-3"><span class="text-red"><i class="bi bi-currency-exchange"></i> Số tiền:</span> <strong class="text-danger">{{ number_format($order->grand_total, 0, ',', '.') }}₫</strong></li>
                                <li class="mb-3"><span class="text-red"><i class="bi bi-chat-text"></i> Nội dung:</span> <strong class="text-break">{{ $order->order_code }}</strong></li>
                            </ul>
                            <div class="alert alert-custom mt-4 d-flex align-items-center">
                                <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                                <div>
                                    <strong>Lưu ý:</strong>
                                    <span class="d-block mt-1">Đừng sửa nội dung chuyển khoản bạn nhé</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center py-3 rounded-bottom-4" style="background: linear-gradient(135deg, #8B0000, #ff4444); border-top: 3px solid white;">
                {{-- Thay bằng route của Laravel --}}
                <a href="{{ route('users.home') }}" class="btn btn-light btn-lg rounded-pill px-4 py-2 shadow-sm hover-transform">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i>QUAY VỀ TRANG CHỦ
                </a>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>