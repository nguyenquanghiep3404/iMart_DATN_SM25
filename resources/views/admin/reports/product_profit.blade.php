@extends('admin.layouts.app')
@section('content')
    <div class="bg-gray-100 text-gray-800">
        <div class="container mx-auto p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">Báo cáo Lợi nhuận theo Sản phẩm</h1>
                <a href="{{ route('admin.business-analysis.index', [
                    'date_filter' => $dateFilter ?? request('date_filter'),
                    'start_date' => $startDate ?? request('start_date'),
                    'end_date' => $endDate ?? request('end_date'),
                    'store_location' => $storeLocation ?? request('store_location'),
                    'category' => $category ?? request('category'),
                ]) }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-lg text-base font-semibold">← Quay lại thống kê</a>
            </div>

            <form method="GET" action="{{ route('admin.reports.product-profit.index') }}"
                class="bg-white rounded-t-xl shadow-md border border-gray-200 p-4 mb-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Tìm kiếm</label>
                        <input type="text" name="q" value="{{ $search }}" placeholder="SKU / Tên sản phẩm"
                            class="w-full rounded-md border-slate-300" />
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Phạm vi thời gian</label>
                        <select name="date_filter" class="w-full h-[35px] rounded-md border-slate-300">
                            <option value="this_month" {{ $dateFilter == 'this_month' ? 'selected' : '' }}>Tháng này
                            </option>
                            <option value="last_month" {{ $dateFilter == 'last_month' ? 'selected' : '' }}>Tháng trước
                            </option>
                            <option value="this_week" {{ $dateFilter == 'this_week' ? 'selected' : '' }}>Tuần này</option>
                            <option value="last_week" {{ $dateFilter == 'last_week' ? 'selected' : '' }}>Tuần trước</option>
                            <option value="custom" {{ $dateFilter == 'custom' ? 'selected' : '' }}>Tùy chỉnh</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Từ ngày</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                            class="w-full rounded-md border-slate-300" />
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Đến ngày</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                            class="w-full rounded-md border-slate-300" />
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Cửa hàng</label>
                        <select name="store_location" class="w-full h-[35px] rounded-md border-slate-300">
                            <option value="all" {{ $storeLocation == 'all' ? 'selected' : '' }}>Tất cả</option>
                            @foreach ($storeLocations as $loc)
                                <option value="{{ $loc->id }}"
                                    {{ (string) $storeLocation === (string) $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Danh mục</label>
                        <select name="category" class="w-full h-[35px] rounded-md border-slate-300">
                            <option value="all" {{ $category == 'all' ? 'selected' : '' }}>Tất cả</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ (string) $category === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-md">Áp dụng</button>
                    <a href="{{ route('admin.reports.product-profit.index') }}"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md">Xóa lọc</a>
                </div>
            </form>

            <div class="bg-white rounded-b-xl shadow-md border border-gray-200 border-t-0">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <div class="text-sm text-gray-700"><span class="font-semibold">Khoảng:</span> {{ $start->format('d/m/Y') }} -
                            {{ $end->format('d/m/Y') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.reports.product-profit.export', [
                            'date_filter' => $dateFilter,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'store_location' => $storeLocation,
                            'category' => $category,
                            'q' => $search,
                        ]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">Xuất Excel</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Tên sản phẩm</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Danh mục</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Đơn vị bán</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Doanh thu</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Giá vốn (COGS)</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Lợi nhuận gộp</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Tỷ suất LN (%)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($report as $row)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-900">{{ $row->sku }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ $row->product_name }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 font-medium">{{ $row->category_name }}</td>
                                    <td class="px-6 py-3 text-sm text-right">{{ number_format($row->total_quantity) }}</td>
                                    <td class="px-6 py-3 text-sm text-right">{{ number_format($row->revenue) }}</td>
                                    <td class="px-6 py-3 text-sm text-right">{{ number_format($row->cogs) }}</td>
                                    <td class="px-6 py-3 text-sm text-right font-semibold text-green-600">
                                        {{ number_format($row->gross_profit) }}</td>
                                    <td class="px-6 py-3 text-sm text-right">{{ number_format($row->profit_margin, 1) }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $report->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
