@extends('layouts.shipper')

@section('title', 'Thống kê & Thu nhập')

@section('content')
    <header class="page-header p-5 bg-white border-b flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">Thống kê & Thu nhập</h1>
        {{-- Nút thông báo có thể được thêm vào đây nếu cần --}}
    </header>

    <main class="page-content p-5 space-y-6">
        <!-- Bộ lọc theo thời gian -->
        <div>
             <label class="text-sm font-semibold text-gray-600">Xem theo</label>
             <div class="mt-2 flex border border-gray-200 rounded-lg p-1 bg-gray-100">
                {{-- Các nút lọc giờ là thẻ <a> để tải lại trang với tham số range --}}
                 <a href="{{ route('shipper.stats', ['range' => 'today']) }}" class="stats-filter-btn flex-1 p-2 text-sm rounded-md font-semibold text-center {{ $range == 'today' ? 'bg-white text-indigo-600 shadow' : 'text-gray-500' }}">Hôm nay</a>
                 <a href="{{ route('shipper.stats', ['range' => 'week']) }}" class="stats-filter-btn flex-1 p-2 text-sm rounded-md font-semibold text-center {{ $range == 'week' ? 'bg-white text-indigo-600 shadow' : 'text-gray-500' }}">Tuần này</a>
                 <a href="{{ route('shipper.stats', ['range' => 'month']) }}" class="stats-filter-btn flex-1 p-2 text-sm rounded-md font-semibold text-center {{ $range == 'month' ? 'bg-white text-indigo-600 shadow' : 'text-gray-500' }}">Tháng này</a>
             </div>
        </div>

        <!-- Các thẻ KPI hiển thị dữ liệu từ Controller -->
        <div class="grid grid-cols-2 gap-4">
             <div class="bg-white p-4 rounded-xl shadow-sm">
                <p class="text-sm text-gray-500">Tổng thu nhập</p>
                <p id="stat-total-income" class="text-2xl font-bold text-green-600">{{ number_format($stats['total_income'], 0, ',', '.') }}đ</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm">
                <p class="text-sm text-gray-500">Tỉ lệ thành công</p>
                <p id="stat-success-rate" class="text-2xl font-bold text-blue-600">{{ $stats['success_rate'] }}%</p>
            </div>
             <div class="bg-white p-4 rounded-xl shadow-sm">
                <p class="text-sm text-gray-500">Đơn thành công</p>
                <p id="stat-total-delivered" class="text-2xl font-bold text-gray-800">{{ $stats['total_delivered'] }}</p>
            </div>
             <div class="bg-white p-4 rounded-xl shadow-sm">
                <p class="text-sm text-gray-500">Đơn thất bại</p>
                <p id="stat-total-failed" class="text-2xl font-bold text-gray-800">{{ $stats['total_failed'] }}</p>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <h3 class="font-bold mb-4">Hiệu suất giao hàng</h3>
            {{-- Chart.js sẽ vẽ biểu đồ vào đây --}}
            <canvas id="performance-chart"></canvas>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Lấy thẻ canvas
        const ctx = document.getElementById('performance-chart')?.getContext('2d');

        // Chỉ vẽ biểu đồ nếu thẻ canvas tồn tại
        if (ctx) {
            // Dùng json_encode để chuyển mảng PHP thành mảng JavaScript một cách an toàn
            const chartLabels = {!! json_encode($chartLabels) !!};
            const chartValues = {!! json_encode($chartValues) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Số đơn thành công',
                        data: chartValues,
                        backgroundColor: 'rgba(79, 70, 229, 0.8)', // Màu indigo
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false } // Ẩn chú thích của bộ dữ liệu
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // Đảm bảo các giá trị trên trục Y là số nguyên
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
