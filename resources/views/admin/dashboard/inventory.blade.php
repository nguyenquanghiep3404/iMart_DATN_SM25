@extends('admin.layouts.app')
@section('content')
    @include('admin.dashboard.layouts.css')

    <body class="bg-gray-100 text-gray-800">

        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
            <!-- Header -->
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard Tồn kho</h1>
                <p class="text-gray-600 mt-1">Tổng quan tình hình tồn kho toàn hệ thống - Cập nhật lúc 08:04, 05/08/2025</p>
            </header>

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Card 1: Tổng giá trị tồn kho -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tổng giá trị tồn kho</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">125.8 tỷ ₫</p>
                        <p class="text-xs text-green-600 mt-2 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                            +5.2% so với tháng trước
                        </p>
                    </div>
                    <div class="bg-indigo-100 text-indigo-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Card 2: Tổng số SKU -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tổng số SKU</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">1,250</p>
                        <p class="text-xs text-gray-500 mt-2">+25 SKU mới trong tháng</p>
                    </div>
                    <div class="bg-green-100 text-green-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </div>

                <!-- Card 3: SKU dưới ngưỡng -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">SKU dưới ngưỡng</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2">78</p>
                        <p class="text-xs text-gray-500 mt-2">Cần tạo đơn nhập hàng</p>
                    </div>
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>

                <!-- Card 4: SKU sắp hết hạn -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">SKU sắp hết hạn</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">15</p>
                        <p class="text-xs text-gray-500 mt-2">Trong vòng 30 ngày tới</p>
                    </div>
                    <div class="bg-red-100 text-red-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">
                <!-- Left Chart: Tỷ trọng giá trị kho -->
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Tỷ trọng giá trị kho</h2>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="inventoryValueChart"></canvas>
                    </div>
                </div>

                <!-- Right Chart: Top sản phẩm tồn kho -->
                <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-md border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Top 10 sản phẩm có giá trị tồn kho cao nhất</h2>
                    <div class="h-64">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Quick Lists Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left List: Phiếu chuyển kho đang chờ -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Phiếu chuyển kho đang chờ xử lý</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-600 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Mã phiếu</th>
                                    <th scope="col" class="px-4 py-3">Kho đi</th>
                                    <th scope="col" class="px-4 py-3">Kho đến</th>
                                    <th scope="col" class="px-4 py-3">Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">PCK-00125</td>
                                    <td class="px-4 py-3">Kho Tổng HN</td>
                                    <td class="px-4 py-3">CH Đà Nẵng</td>
                                    <td class="px-4 py-3">04/08/2025</td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">PCK-00124</td>
                                    <td class="px-4 py-3">Kho Tổng TPHCM</td>
                                    <td class="px-4 py-3">CH Cần Thơ</td>
                                    <td class="px-4 py-3">03/08/2025</td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">PCK-00122</td>
                                    <td class="px-4 py-3">CH Ba Đình</td>
                                    <td class="px-4 py-3">CH Cầu Giấy</td>
                                    <td class="px-4 py-3">01/08/2025</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right List: Phiên kiểm kho đang diễn ra -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Phiên kiểm kho đang diễn ra</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-600 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Mã phiên</th>
                                    <th scope="col" class="px-4 py-3">Địa điểm</th>
                                    <th scope="col" class="px-4 py-3">Ngày bắt đầu</th>
                                    <th scope="col" class="px-4 py-3">Phụ trách</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">PKK-HN-0825</td>
                                    <td class="px-4 py-3">Kho Tổng HN</td>
                                    <td class="px-4 py-3">05/08/2025</td>
                                    <td class="px-4 py-3">Nguyễn Văn A</td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">PKK-DN-0825</td>
                                    <td class="px-4 py-3">CH Đà Nẵng</td>
                                    <td class="px-4 py-3">02/08/2025</td>
                                    <td class="px-4 py-3">Trần Thị B</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <script>
            // Wait for the DOM to be fully loaded
            document.addEventListener('DOMContentLoaded', () => {

                // --- Chart 1: Tỷ trọng giá trị kho (Donut Chart) ---
                const inventoryValueCtx = document.getElementById('inventoryValueChart').getContext('2d');
                new Chart(inventoryValueCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Kho Tổng HN', 'Kho Tổng TPHCM', 'CH Đà Nẵng', 'Các CH khác'],
                        datasets: [{
                            label: 'Giá trị tồn kho',
                            data: [50.3, 45.2, 15.8, 14.5], // Dữ liệu giả (tỷ đồng)
                            backgroundColor: [
                                'rgba(79, 70, 229, 0.8)', // Indigo
                                'rgba(22, 163, 74, 0.8)', // Green
                                'rgba(202, 138, 4, 0.8)', // Yellow
                                'rgba(107, 114, 128, 0.8)' // Gray
                            ],
                            borderColor: '#ffffff', // Match the card background
                            borderWidth: 4,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#4b5563', // Text color for legend
                                    font: {
                                        family: "'Inter', sans-serif"
                                    }
                                }
                            }
                        }
                    }
                });

                // --- Chart 2: Top sản phẩm tồn kho (Horizontal Bar Chart) ---
                const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
                new Chart(topProductsCtx, {
                    type: 'bar',
                    data: {
                        labels: [
                            'iPhone 15 Pro Max',
                            'Macbook Pro 16" M3',
                            'Samsung Galaxy S25',
                            'Apple Watch Ultra 3',
                            'Sony WH-1000XM6'
                        ],
                        datasets: [{
                            label: 'Giá trị (tỷ ₫)',
                            data: [12.5, 9.8, 7.2, 5.4, 4.1], // Dữ liệu giả
                            backgroundColor: 'rgba(79, 70, 229, 0.6)',
                            borderColor: 'rgba(79, 70, 229, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y', // This makes the bar chart horizontal
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    color: '#4b5563'
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#4b5563'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false // Hide legend for a cleaner look
                            }
                        }
                    }
                });
            });
        </script>

    </body>

    </html>
@endsection
