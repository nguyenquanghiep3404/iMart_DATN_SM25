@extends('admin.layouts.app')

@section('title', 'Báo cáo tồn kho')

@push('styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom styles for select arrows and date inputs */
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
        }
    </style>
@endpush

@section('content')
    <main class="max-w-screen-2xl mx-auto">
        <!-- Header -->
        <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Sổ cái Kho (Lịch sử Luân chuyển)</h1>
                <p class="text-gray-600 mt-1">Truy vết mọi giao dịch làm thay đổi tồn kho của sản phẩm.</p>
            </div>
            <a href="{{ route('admin.inventory-ledger.export') }}"
                class="flex-shrink-0 flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg shadow-sm hover:bg-green-700 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Xuất file Excel</span>
            </a>
        </header>

        <!-- Main Content Block -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <!-- Filter Bar -->
            <form method="GET" action="{{ route('admin.inventory-ledger.index') }}">
                <div class="p-4 border-b border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-12 gap-4">
                        <!-- Province Filter -->
                        <select id="province-filter" name="province_code"
                            class="xl:col-span-2 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Tất cả Tỉnh/Thành</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->code }}"
                                    {{ old('province_code', request('province_code')) == $province->code ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- District Filter -->
                        <select id="district-filter" name="district_code"
                            class="xl:col-span-2 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            {{ request('province_code') ? '' : 'disabled' }}>
                            <option value="">Tất cả Quận/Huyện</option>
                        </select>

                        <!-- Transaction Type -->
                        <select id="transaction-type-filter" name="transaction_type"
                            class="xl:col-span-2 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Tất cả giao dịch</option>
                            <option value="sale"
                                {{ old('transaction_type', request('transaction_type')) == 'sale' ? 'selected' : '' }}>
                                Bán hàng
                            </option>
                            <option value="Nhập hàng từ NCC"
                                {{ old('transaction_type', request('transaction_type')) == 'Nhập hàng từ NCC' ? 'selected' : '' }}>
                                Nhập hàng
                            </option>
                            <option value="Xuất kho chuyển đi"
                                {{ old('transaction_type', request('transaction_type')) == 'Xuất kho chuyển đi' ? 'selected' : '' }}>
                                Chuyển kho
                            </option>
                            <option value="Nhận kho chuyển đến"
                                {{ old('transaction_type', request('transaction_type')) == 'Nhận kho chuyển đến' ? 'selected' : '' }}>
                                Nhận kho
                            <option value="adjustment"
                                {{ old('transaction_type', request('transaction_type')) == 'adjustment' ? 'selected' : '' }}>
                                Điều chỉnh
                            </option>
                        </select>

                        <!-- Date Start -->
                        <input id="date-start-filter" type="date" name="date_start"
                            value="{{ old('date_start', request('date_start')) }}"
                            class="xl:col-span-2 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">

                        <!-- Date End -->
                        <input id="date-end-filter" type="date" name="date_end"
                            value="{{ old('date_end', request('date_end')) }}"
                            class="xl:col-span-2 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">

                        <!-- Search Input -->
                        <div class="relative xl:col-span-2">
                            <input id="search-input" type="text" name="search"
                                value="{{ old('search', request('search')) }}"
                                placeholder="Tìm SKU / Tên sản phẩm / Tham chiếu..."
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-start gap-2 mt-4">
                        <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center gap-2">
                            Tìm kiếm
                        </button>

                        <a href="{{ route('admin.inventory-ledger.index') }}"
                            class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 flex items-center gap-2">
                            Xóa lọc
                        </a>
                    </div>
                </div>
            </form>

            <!-- Ledger Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-xs text-gray-600 uppercase">
                        <tr>
                            <th class="px-6 py-3">Ngày giờ</th>
                            <th class="px-6 py-3">Sản phẩm</th>
                            <th class="px-6 py-3">Địa điểm</th>
                            <th class="px-6 py-3">Giao dịch</th>
                            <th class="px-6 py-3">Tham chiếu</th>
                            <th class="px-6 py-3">Thay đổi</th>
                            <th class="px-6 py-3">Tồn sau thay đổi</th>
                            <th class="px-6 py-3">Người thực hiện</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @forelse ($movements as $movement)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $movement->created_at ? $movement->created_at->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($movement->productVariant && $movement->productVariant->product)
                                        <div class="font-semibold text-gray-900">
                                            {{ $movement->productVariant->product->name }}
                                            @php
                                                $attributes = $movement->productVariant->attributeValues
                                                    ->pluck('value')
                                                    ->toArray();
                                            @endphp
                                            @if (count($attributes))
                                                {{ implode(' ', $attributes) }}
                                            @endif
                                        </div>

                                        <div class="text-xs text-gray-500">SKU:
                                            {{ $movement->productVariant->sku ?? 'N/A' }}</div>
                                    @else
                                        <div class="font-semibold text-gray-900">N/A</div>
                                        <div class="text-xs text-gray-500">SKU: N/A</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    {{ $movement->storeLocation?->name ?? 'Không xác định' }}
                                </td>
                                <td class="px-6 py-4">
                                    @switch($movement->reason)
                                        @case('sale')
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                {{ $movement->reason_label }}
                                            </span>
                                        @break

                                        @case('Nhập hàng từ NCC')
                                            <span
                                                class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                {{ $movement->reason_label }}
                                            </span>
                                        @break

                                        @case('Xuất kho chuyển đi')
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                {{ $movement->reason_label }}
                                            </span>
                                        @break

                                        @case('Nhận kho chuyển đến')
                                            <span
                                                class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                {{ $movement->reason_label }}
                                            </span>
                                        @break

                                        @case('adjustment')
                                            <span
                                                class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                {{ $movement->reason_label }}
                                            </span>
                                        @break

                                        @default
                                            {{ $movement->reason_label }}
                                    @endswitch

                                </td>
                                <td class="px-6 py-4">
                                    <a href="#"
                                        class="text-indigo-600 hover:underline">{{ $movement->reference_code ?? 'N/A' }}</a>
                                </td>
                                <td
                                    class="px-6 py-4 text-center font-semibold {{ $movement->quantity_change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change ?? '0' }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold">{{ $movement->quantity_after_change ?? '0' }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $movement->user?->name ?? 'N/A' }}
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-16 text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Không tìm thấy giao dịch</h3>
                                        <p class="mt-1 text-sm text-gray-500">Vui lòng thử lại với bộ lọc khác.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 flex items-center justify-between border-t border-gray-200">
                    <span class="text-sm text-gray-600">
                        Hiển thị {{ $movements->firstItem() }}-{{ $movements->lastItem() }} của {{ $movements->total() }} kết
                        quả
                    </span>
                    <div class="flex items-center space-x-2">
                        {{ $movements->links() }}
                    </div>
                </div>
        </main>
    @endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const provinceFilterEl = document.getElementById('province-filter');
                const districtFilterEl = document.getElementById('district-filter');

                // Cập nhật danh sách Quận/Huyện khi chọn Tỉnh/Thành
                provinceFilterEl.addEventListener('change', () => {
                    const provinceCode = provinceFilterEl.value;
                    districtFilterEl.innerHTML = '<option value="">Tất cả Quận/Huyện</option>';
                    if (provinceCode) {
                        fetch(`/admin/api/districts/${provinceCode}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                districtFilterEl.disabled = false;
                                data.forEach(district => {
                                    const option = document.createElement('option');
                                    option.value = district.code;
                                    option.textContent = district.name;
                                    districtFilterEl.appendChild(option);
                                });
                                // Khôi phục giá trị quận/huyện nếu có trong request
                                const currentDistrict =
                                    '{{ old('district_code', request('district_code')) }}';
                                if (currentDistrict) {
                                    districtFilterEl.value = currentDistrict;
                                }
                            })
                            .catch(() => {
                                alert('Không thể tải danh sách Quận/Huyện.');
                                districtFilterEl.disabled = true;
                            });
                    } else {
                        districtFilterEl.disabled = true;
                    }
                });

                // Kích hoạt sự kiện change khi tải trang để khôi phục danh sách Quận/Huyện
                if (provinceFilterEl.value) {
                    provinceFilterEl.dispatchEvent(new Event('change'));
                }
            });
        </script>
    @endpush
