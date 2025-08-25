@extends('admin.layouts.app')
@section('content')
    @include('admin.dashboard.layouts.css')

    <div class="bg-gray-100 text-gray-800">
        <div class="container mx-auto p-4 md:p-6 lg:p-8">
            <!-- HEADER -->
            <header class="mb-6">
                <h1 class="text-3xl font-bold text-slate-900">Báo Cáo & Phân Tích Kinh Doanh</h1>
                <p class="text-slate-500 mt-1">Phân tích về hiệu suất kinh doanh.</p>
            </header>

            <!-- FILTERS SECTION -->
            <form method="GET" action="{{ route('admin.business-analysis.index') }}" id="filters" class="bg-white rounded-xl shadow-md border border-gray-200 p-6 mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                    <!-- Date Filters Group -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-slate-600 mb-2">Phạm vi thời gian</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <select name="date_filter" id="date-range-quick-select"
                                class="sm:col-span-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="this_month" {{ $dateFilter == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                                <option value="last_month" {{ $dateFilter == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                                <option value="this_week" {{ $dateFilter == 'this_week' ? 'selected' : '' }}>Tuần này</option>
                                <option value="last_week" {{ $dateFilter == 'last_week' ? 'selected' : '' }}>Tuần trước</option>
                                <option value="custom" {{ $dateFilter == 'custom' ? 'selected' : '' }}>Tùy chỉnh</option>
                            </select>
                            <input type="date" name="start_date" id="from-date" value="{{ $startDate }}"
                                class="sm:col-span-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="date" name="end_date" id="to-date" value="{{ $endDate }}"
                                class="sm:col-span-1 w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <!-- Store Location -->
                    <div class="lg:col-span-1">
                        <label for="store-location" class="block text-sm font-medium text-slate-600 mb-2">Địa điểm</label>
                        <select name="store_location" id="store-location"
                            class="w-full h-[35px] rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="all" {{ $storeLocation == 'all' ? 'selected' : '' }}>Tất cả</option>
                            @foreach($storeLocations as $location)
                                <option value="{{ $location->id }}" {{ $storeLocation == $location->id ? 'selected' : '' }}>
                                    {{ Str::limit($location->name, 15) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Product Category -->
                    <div class="lg:col-span-1">
                        <label for="product-category" class="block text-sm font-medium text-slate-600 mb-2">Danh mục</label>
                        <select name="category" id="product-category"
                            class="w-full h-[35px] rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="all" {{ $category == 'all' ? 'selected' : '' }}>Tất cả</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $category == $cat->id ? 'selected' : '' }}>
                                    {{ Str::limit($cat->name, 15) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-3">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white font-semibold h-[35px] px-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center justify-center gap-1 text-sm">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                            <span>Áp dụng</span>
                        </button>
                        <button type="button" id="clear-filters"
                            class="flex-1 bg-gray-500 text-white font-semibold h-[35px] px-3 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 flex items-center justify-center gap-1 text-sm">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span>Xóa lọc</span>
                        </button>
                    </div>



                </div>
            </form>



            <!-- KPIs SECTION -->
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tổng Doanh Thu</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($kpis['total_revenue']) }} VNĐ
                        </p>

                        {{-- <p class="text-xs {{ $kpis['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-2 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ $kpis['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($kpis['revenue_change'], 1) }}% so với tháng trước
                        </p> --}}
                    </div>
                    <div class="bg-green-100 text-green-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Lợi Nhuận Gộp</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($kpis['gross_profit']) }} VNĐ
                        </p>

                        {{-- <p class="text-xs text-gray-500 mt-2">Tỷ suất: {{ number_format($kpis['gross_profit_margin'], 1) }}%</p> --}}
                    </div>
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Giá Vốn (COGS)</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($kpis['total_cogs']) }} VNĐ
                        </p>

                        <p class="text-xs text-gray-500 mt-2">Chi phí sản xuất</p>
                    </div>
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                {{-- <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tỷ Suất LN Gộp</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($kpis['gross_profit_margin'], 1) }}%</p>
                        <p class="text-xs text-gray-500 mt-2">Hiệu quả kinh doanh</p>
                    </div>
                    <div class="bg-purple-100 text-purple-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div> --}}
                {{-- <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Giá Trị Tồn Kho</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">25.3 Tỷ</p>
                        <p class="text-xs text-gray-500 mt-2">Tính đến hôm nay</p>
                    </div>
                    <div class="bg-indigo-100 text-indigo-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div> --}}
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Số Lượng Đơn</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($kpis['total_orders']) }}</p>

                        <p class="text-xs text-gray-500 mt-2">Đơn hàng thành công</p>
                    </div>
                    <div class="bg-orange-100 text-orange-600 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
            </section>

            <!-- Charts Grid -->
            <section class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">
                <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-md border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Doanh thu, Giá vốn & Lợi nhuận</h3>
                    <div class="h-80">

                        <canvas id="revenueProfitChart"></canvas>
                    </div>
                </div>
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Cơ cấu Lợi nhuận theo Danh mục</h3>
                    <div class="h-80 flex items-center justify-center">

                        <canvas id="profitByCategoryChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- Detailed Reports Section (Chuyển sang trang riêng) -->
            <section class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Báo Cáo Chi Tiết</h2>
                <div class="bg-white rounded-xl shadow-md border border-gray-200">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Báo cáo Lợi nhuận theo Sản phẩm</h3>
                        <a href="{{ route('admin.reports.product-profit.index', [
                            'date_filter' => $dateFilter,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'store_location' => $storeLocation,
                            'category' => $category,
                        ]) }}" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-700 text-sm flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <script>
            // --- XỬ LÝ BỘ LỌC NGÀY ---
            const quickSelect = document.getElementById('date-range-quick-select');
            const fromDateInput = document.getElementById('from-date');
            const toDateInput = document.getElementById('to-date');

            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function updateDateInputs(option) {
                const now = new Date();
                let startDate, endDate;
                switch (option) {
                    case 'this_month':
                        startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                        endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                        break;
                    case 'last_month':
                        startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                        endDate = new Date(now.getFullYear(), now.getMonth(), 0);
                        break;
                    case 'this_week':
                        const firstDayOfWeek = now.getDate() - now.getDay() + (now.getDay() === 0 ? -6 : 1);
                        startDate = new Date(now.setDate(firstDayOfWeek));
                        endDate = new Date(startDate);
                        endDate.setDate(startDate.getDate() + 6);
                        break;
                    case 'last_week':
                        const lastWeekStartDate = new Date();
                        lastWeekStartDate.setDate(now.getDate() - now.getDay() - 6);
                        startDate = lastWeekStartDate;
                        endDate = new Date(startDate);
                        endDate.setDate(startDate.getDate() + 6);
                        break;
                    case 'custom':
                        return;
                }
                fromDateInput.value = formatDate(startDate);
                toDateInput.value = formatDate(endDate);
            }
            quickSelect.addEventListener('change', (e) => {
                updateDateInputs(e.target.value);
            });

            // Chức năng xóa bộ lọc
            document.getElementById('clear-filters').addEventListener('click', function() {
                // Đặt lại bộ lọc ngày về tháng này
                document.getElementById('date-range-quick-select').value = 'this_month';
                // Đặt lại dữ liệu ngày về tháng hiện tại
                const now = new Date();
                const startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                const endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                document.getElementById('from-date').value = formatDate(startDate);
                document.getElementById('to-date').value = formatDate(endDate);

                // Đặt lại vị trí cửa hàng thành "tất cả"
                document.getElementById('store-location').value = 'all';

                // Đặt lại danh mục thành "tất cả"
                document.getElementById('product-category').value = 'all';
                
                // Submit the form to refresh data
                document.getElementById('filters').submit();
            });

            // --- CHART.JS IMPLEMENTATION ---
            document.addEventListener('DOMContentLoaded', () => {
                lucide.createIcons();
                updateDateInputs('this_month');

                // Chart 1 - Revenue Timeline
                const revenueProfitCtx = document.getElementById('revenueProfitChart').getContext('2d');
                new Chart(revenueProfitCtx, {
                    type: 'bar',
                    data: {
                        labels: @json(collect($chartData['revenue_timeline'])->pluck('label')),
                        datasets: [{
                                label: 'Lợi Nhuận',
                                data: @json(collect($chartData['revenue_timeline'])->pluck('profit')->map(function($value) { 
                                    $absValue = abs($value);
                                    if($absValue >= 1000000000) return $value / 1000000000;
                                    elseif($absValue >= 1000000) return $value / 1000000;
                                    else return $value;
                                })),
                                type: 'line',
                                borderColor: '#3b82f6',
                                backgroundColor: '#3b82f6',
                                tension: 0.3,
                                yAxisID: 'y',
                                order: 0
                            },
                            {
                                label: 'Doanh Thu',
                                data: @json(collect($chartData['revenue_timeline'])->pluck('revenue')),
                                backgroundColor: '#a5b4fc',
                                yAxisID: 'y',
                                order: 1
                            },
                            {
                                label: 'Giá Vốn',
                                data: @json(collect($chartData['revenue_timeline'])->pluck('cogs')),
                                backgroundColor: '#fcd34d',
                                yAxisID: 'y',
                                order: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(v) {
                                        return new Intl.NumberFormat('vi-VN').format(v) + ' VNĐ';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return `${ctx.dataset.label}: ${new Intl.NumberFormat('vi-VN').format(ctx.raw)} VNĐ`;
                                    }
                                }
                            }
                        }
                    }
                });

                // Chart 2 - Category Revenue
                const profitByCategoryCtx = document.getElementById('profitByCategoryChart').getContext('2d');
                new Chart(profitByCategoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json(collect($chartData['category_revenue'])->pluck('name')),
                        datasets: [{
                            label: 'Doanh thu',
                            data: @json(collect($chartData['category_revenue'])->pluck('revenue')),
                            backgroundColor: ['#6366f1', '#3b82f6', '#60a5fa', '#93c5fd', '#c084fc'],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => {
                                        const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((ctx.raw / total) * 100).toFixed(2) : 0;
                                        return `${ctx.label}: ${new Intl.NumberFormat('vi-VN').format(ctx.raw)} VNĐ (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
        {{-- @include('admin.dashboard.layouts.script') --}}
        </div>
    </div>
@endsection


