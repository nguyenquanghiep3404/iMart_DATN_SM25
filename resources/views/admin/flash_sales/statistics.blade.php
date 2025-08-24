@extends('admin.layouts.app')

@section('title', 'Thống kê Flash Sale - ' . $flashSale->name)

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <a href="{{ route('admin.flash-sales.index') }}"
            class="mb-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-arrow-left mr-2"></i>
            Quay lại danh sách
        </a>
        <div class="flex flex-col md:flex-row items-center justify-between pb-6 border-b border-gray-200 mb-6">
            <div class="flex items-center space-x-4 mb-4 md:mb-0">
                <i class="fas fa-bolt text-4xl text-yellow-500"></i>
                <h1 class="text-3xl font-bold text-gray-800">Thống Kê Chiến Dịch: {{ $flashSale->name }}</h1>
            </div>
            <div class="text-gray-500 text-sm text-right">
                <p>Thời gian: {{ $flashSale->start_time->format('d/m/Y H:i') }} -
                    {{ $flashSale->end_time->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div
                class="bg-red-500 text-white rounded-lg p-6 shadow-md transform transition-transform duration-300 hover:scale-105">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm uppercase font-semibold">Tổng Doanh Thu</span>
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
                <div class="text-3xl font-bold">{{ number_format($totalRevenue) }}đ</div>
                @if ($revenueChange !== null)
                    <p class="text-sm mt-1 text-white">
                        {{ $revenueChange >= 0 ? 'Tăng' : 'Giảm' }} {{ abs($revenueChange) }}% so với kỳ trước
                    </p>
                @endif
            </div>

            <div
                class="bg-green-500 text-white rounded-lg p-6 shadow-md transform transition-transform duration-300 hover:scale-105">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm uppercase font-semibold">Tổng Lượt Bán</span>
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
                <div class="text-3xl font-bold">{{ number_format($totalQuantitySold) }}/{{ number_format($totalQuantityLimit) }}</div>
                <p class="text-sm mt-1 text-white">Đã bán hết {{ $soldPercentage }}% sản phẩm</p>
            </div>

            <div
                class="bg-teal-500 text-white rounded-lg p-6 shadow-md transform transition-transform duration-300 hover:scale-105">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm uppercase font-semibold">Tổng Lợi Nhuận Gộp</span>
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div class="text-3xl font-bold">{{ number_format($totalGrossProfit) }}đ</div>
                <p class="text-sm mt-1 text-white">Tỷ suất lợi nhuận: {{ $grossProfitMargin }}%</p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 shadow-md mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Tỷ lệ doanh thu theo sản phẩm</h2>
            <div class="h-96 flex items-center justify-center">
                <canvas id="productPieChart" class="max-h-full"></canvas>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 shadow-md overflow-x-auto">
            <h2 class="text-xl font-bold text-gray-800 mb-4">5 sản phẩm bán chạy nhất trong chiến dịch {{ $flashSale->name }}</h2>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên Sản Phẩm</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Giá Gốc</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Giá Nhập</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Giá Flash Sale</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">SL Giới Hạn FS</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Đã Bán (FS)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tồn Kho FS</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tổng Tồn Kho</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tổng Tồn Kho Sau FS</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Doanh Thu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lợi Nhuận Gộp</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($topFive as $stat)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $stat['product_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['original_price'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['cost_price'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 font-semibold">{{ $stat['flash_price'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['quantity_limit'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">{{ $stat['quantity_sold'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['remaining_stock_fs'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['total_stock'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['total_stock_after_fs'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['revenue'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['gross_profit_per_unit'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Không có sản phẩm nào trong top 5.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 shadow-md overflow-x-auto mt-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Sản Phẩm Còn Lại (trong hạn mức Flash Sale)</h2>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên Sản Phẩm</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Giá Gốc</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Giá Nhập</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Giá Flash Sale</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">SL Giới Hạn FS</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Đã Bán (FS)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tồn Kho FS</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tổng Tồn Kho</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tổng Tồn Kho Sau FS</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Doanh Thu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lợi Nhuận Gộp</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($remaining as $stat)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $stat['product_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['original_price'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['cost_price'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 font-semibold">{{ $stat['flash_price'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['quantity_limit'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">{{ $stat['quantity_sold'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['remaining_stock_fs'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['total_stock'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stat['total_stock_after_fs'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['revenue'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['gross_profit_per_unit'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Không có sản phẩm nào ngoài top 5.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Biểu đồ tròn Tỷ lệ doanh thu sản phẩm
        const pieCtx = document.getElementById('productPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: [@foreach ($statistics as $stat)'{{ addslashes($stat['chart_label']) }}', @endforeach],
                datasets: [{
                    label: 'Tỷ lệ Doanh thu',
                    data: [@foreach ($statistics as $stat){{ $stat['chart_revenue'] }}, @endforeach],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 99, 71, 0.8)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 99, 71, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 14 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                let label = tooltipItem.label || '';
                                if (label) label += ': ';
                                const value = tooltipItem.raw;
                                label += new Intl.NumberFormat('vi-VN', {
                                    style: 'currency',
                                    currency: 'VND'
                                }).format(value);
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush