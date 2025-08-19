@extends('admin.layouts.app')

@section('title', 'Thống kê')

@section('content')
<div class="body-content px-8 py-8 bg-slate-100">
    {{-- Page Title and Filter --}}
    <div class="flex justify-between items-start flex-wrap">
        <div class="page-title mb-7">
            <h3 class="mb-0 text-4xl font-bold">Dashboard</h3>
            <p class="text-textBody m-0">Thống kê tổng quan về cửa hàng của bạn.</p>
        </div>
        <div class="mb-7">
            <form id="filterForm" action="{{ route('admin.dashboard') }}" method="GET">
                <div class="flex items-center space-x-3">
                    <select name="date_filter" id="date_filter_selector" class="p-2 border rounded-md">
                        <option value="this_month" @if($dateFilter == 'this_month') selected @endif>Tháng này</option>
                        <option value="last_7_days" @if($dateFilter == 'last_7_days') selected @endif>7 ngày qua</option>
                        <option value="today" @if($dateFilter == 'today') selected @endif>Hôm nay</option>
                        <option value="this_year" @if($dateFilter == 'this_year') selected @endif>Năm nay</option>
                        <option value="custom" @if($dateFilter == 'custom') selected @endif>Tùy chọn</option>
                    </select>
                    <div id="custom_date_range" class="flex items-center space-x-2 @if($dateFilter != 'custom') hidden @endif">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="p-2 border rounded-md">
                        <span>-</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="p-2 border rounded-md">
                    </div>
                    <button type="submit" class="tp-btn px-5 py-2">Lọc</button>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <div class="widget-item bg-white p-6 flex justify-between rounded-md shadow-sm">
            <div>
                <h4 class="text-xl font-semibold text-slate-700 mb-1 leading-none">{{ number_format($totalRevenue, 0, ',', '.') }} VNĐ</h4>
                <p class="text-tiny leading-4 text-slate-500">Tổng doanh thu</p>
            </div>
            <div class="text-green-500"><i class="fas fa-dollar-sign fa-2x"></i></div>
        </div>
        <div class="widget-item bg-white p-6 flex justify-between rounded-md shadow-sm">
            <div>
                <h4 class="text-xl font-semibold text-slate-700 mb-1 leading-none">{{ $totalOrders }}</h4>
                <p class="text-tiny leading-4 text-slate-500">Đơn hàng thành công</p>
            </div>
            <div class="text-blue-500"><i class="fas fa-shopping-cart fa-2x"></i></div>
        </div>
        <div class="widget-item bg-white p-6 flex justify-between rounded-md shadow-sm">
            <div>
                <h4 class="text-xl font-semibold text-slate-700 mb-1 leading-none">{{ $newCustomers }}</h4>
                <p class="text-tiny leading-4 text-slate-500">Khách hàng mới</p>
            </div>
            <div class="text-orange-500"><i class="fas fa-users fa-2x"></i></div>
        </div>
        <div class="widget-item bg-white p-6 flex justify-between rounded-md shadow-sm">
            <div>
                <h4 class="text-xl font-semibold text-slate-700 mb-1 leading-none">{{ $totalProductsSold }}</h4>
                <p class="text-tiny leading-4 text-slate-500">Sản phẩm đã bán</p>
            </div>
            <div class="text-purple-500"><i class="fas fa-box fa-2x"></i></div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <div class="col-span-1 xl:col-span-2 bg-white p-6 rounded-md shadow-sm">
            <h4 class="text-xl font-semibold mb-4">Biểu đồ doanh thu</h4>
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="col-span-1 bg-white p-6 rounded-md shadow-sm">
            <h4 class="text-xl font-semibold mb-4">Top danh mục</h4>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    {{-- Tables --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <div class="col-span-1 xl:col-span-1 bg-white p-6 rounded-md shadow-sm">
            <h4 class="text-xl font-semibold mb-4">Top 5 sản phẩm bán chạy</h4>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="p-3 text-left">Sản phẩm</th>
                            <th class="p-3 text-right">Đã bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSellingProducts as $product)
                            <tr class="border-b hover:bg-slate-50">
                                <td class="p-3">
                                    <div class="flex items-center space-x-3">
                                        {{-- SAO CHÉP LOGIC LẤY ẢNH TỪ TRANG ADMIN --}}
                                        @php
                                            $image = $product->coverImage ?? $product->variants->first()?->primaryImage;
                                            $imageUrl = $image ? Illuminate\Support\Facades\Storage::url($image->path) : asset('assets/admin/img/placeholder-image.png');
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded">

                                        <div>
                                            {{-- Dùng slug từ bảng products đã có sẵn --}}
                                            <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="hover:text-blue-600 font-medium">
                                                {{ $product->name }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-right font-semibold">{{ $product->total_quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="p-3 text-center">Không có dữ liệu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-span-1 xl:col-span-2 bg-white p-6 rounded-md shadow-sm">
            <h4 class="text-xl font-semibold mb-4">Đơn hàng mới nhất</h4>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="p-3 text-left">Mã ĐH</th>
                            <th class="p-3 text-left">Khách hàng</th>
                            <th class="p-3 text-right">Tổng tiền</th>
                            <th class="p-3 text-center">Trạng thái</th>
                            <th class="p-3 text-right">Ngày đặt</th>
                        </tr>
                    </thead>
                    @php
                        $statusMap = [
                            'pending_confirmation' => 'Chờ xác nhận',
                            'processing' => 'Đang xử lý',
                            'awaiting_shipment' => 'Chờ giao hàng',
                            'shipped' => 'Đã gửi',
                            'out_for_delivery' => 'Đang giao',
                            'delivered' => 'Đã giao',
                            'cancelled' => 'Đã hủy',
                            'returned' => 'Trả hàng',
                            'failed_delivery' => 'Giao thất bại'
                        ];
                    @endphp
                    <tbody>
                        @forelse($latestOrders as $order)
                        <tr class="border-b hover:bg-slate-50">
                            <td class="p-3 font-semibold">#{{ $order->order_code }}</td>
                            <td class="p-3">{{ $order->user->name ?? 'Khách vãng lai' }}</td>
                            <td class="p-3 text-right">{{ number_format($order->sub_total, 0, ',', '.') }} VNĐ</td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($order->status == 'delivered') bg-green-100 text-green-700
                                    @elseif($order->status == 'processing') bg-blue-100 text-blue-700
                                    @elseif($order->status == 'pending_confirmation') bg-yellow-100 text-yellow-700
                                    @else bg-red-100 text-red-700 @endif">
                                    {{ $statusMap[$order->status] ?? ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-right">{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-3 text-center">Không có đơn hàng nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Thêm Chart.js và FontAwesome (nếu chưa có) vào layout chính của bạn --}}
{{-- Ví dụ: <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
{{-- Ví dụ: <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" /> --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handle filter visibility
    const filterSelector = document.getElementById('date_filter_selector');
    const customDateRange = document.getElementById('custom_date_range');
    filterSelector.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateRange.classList.remove('hidden');
        } else {
            customDateRange.classList.add('hidden');
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($revenueData['labels']),
            datasets: [{
                label: 'Doanh thu',
                data: @json($revenueData['data']),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: true,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
                        }
                    }
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json($categorySalesData['labels']),
            datasets: [{
                label: 'Doanh thu theo danh mục',
                data: @json($categorySalesData['data']),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
        }
    });
});
</script>
@endpush
