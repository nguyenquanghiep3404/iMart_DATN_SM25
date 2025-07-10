<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhân viên Xử lý Đơn hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .status-active {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .status-inactive {
            background-color: #f3f4f6;
            color: #4b5563;
        }

        /* Modal styles */
        .modal {
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .modal-content {
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }

        .modal:not(.hidden) .modal-content {
            transform: scale(1);
        }
    </style>
</head>
