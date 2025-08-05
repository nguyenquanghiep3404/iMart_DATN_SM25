@extends('admin.layouts.app')

@section('title', 'Quản lý Nhân viên Giao hàng - ' . $warehouse->name)

@push('styles')
    <style>
        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .status-inactive {
            background-color: #f3f4f6;
            color: #4b5563;
        }

        .status-banned {
            background-color: #fee2e2;
            color: #dc2626;
        }
    </style>
@endpush
@section('content')
    <div class="max-w-screen-2xl mx-auto p-4 md:p-8">
        @include('admin.partials.flash_message')
        <header class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="flex text-sm text-gray-500">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng
                                    điều khiển</a>
                            </li>
                            <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.shippers.index') }}"
                                    class="text-indigo-600 hover:text-indigo-800">Quản lý Nhân viên Giao hàng</a>
                            </li>
                            <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                            <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">
                                {{ $warehouse->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-800">Quản lý Nhân viên Giao hàng</h1>
                    <p class="text-gray-500 mt-1">Kho: {{ $warehouse->name }} -
                        {{ $warehouse->province->name_with_type ?? '' }}</p>
                    <a href="{{ route('admin.shippers.index') }}">
                        <p class="text-indigo-600"><i class="fas fa-arrow-left"> Quay lại </i></p>
                    </a>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.shippers.trash') }}"
                        class="px-4 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-semibold flex items-center justify-center space-x-2">
                        <i class="fas fa-trash"></i>
                        <span>Thùng rác</span>
                    </a>
                    <a href="{{ route('admin.shippers.create', ['warehouse_id' => $warehouse->id]) }}"
                        class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center justify-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Thêm nhân viên mới</span>
                    </a>
                </div>
            </div>
        </header>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
                <div class="bg-blue-100 text-blue-600 p-4 rounded-full"><i class="fas fa-users fa-xl"></i></div>
                <div>
                    <p class="text-sm text-gray-500">Tổng tài xế</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
                <div class="bg-green-100 text-green-600 p-4 rounded-full"><i class="fas fa-user-check fa-xl"></i></div>
                <div>
                    <p class="text-sm text-gray-500">Đang hoạt động</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['active'] }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
                <div class="bg-gray-100 text-gray-600 p-4 rounded-full"><i class="fas fa-user-clock fa-xl"></i></div>
                <div>
                    <p class="text-sm text-gray-500">Tạm nghỉ</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['inactive'] }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
                <div class="bg-yellow-100 text-yellow-600 p-4 rounded-full"><i class="fas fa-box fa-xl"></i></div>
                <div>
                    <p class="text-sm text-gray-500">Tổng đơn đã nhận</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['assigned'] }}</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
                <div class="bg-indigo-100 text-indigo-600 p-4 rounded-full"><i class="fas fa-truck-ramp-box fa-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tổng đơn đã giao</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['delivered'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <form action="{{ route('admin.shippers.warehouse.show', $warehouse) }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label for="search-input" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <input type="text" name="search" id="search-input" placeholder="Tên, Email, SĐT..."
                                value="{{ request('search') }}" class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Trạng
                                thái</label>
                            <select name="status" id="status-filter"
                                class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg">
                                <option value="">Tất cả</option>
                                <option value="active" @selected(request('status') == 'active')>Đang hoạt động</option>
                                <option value="inactive" @selected(request('status') == 'inactive')>Không hoạt động</option>
                                <option value="banned" @selected(request('status') == 'banned')>Đã khóa</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-3">
                            <button type="submit"
                                class="w-full px-5 py-2 bg-indigo-600 text-white rounded-lg font-semibold">Áp dụng</button>
                            <a href="{{ route('admin.shippers.warehouse.show', $warehouse) }}"
                                class="w-full px-5 py-2 bg-gray-200 text-gray-700 rounded-lg text-center font-semibold">Xóa
                                lọc</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="p-6">Nhân viên</th>
                            <th class="p-6">Thống kê</th>
                            <th class="p-6">Ngày tham gia</th>
                            <th class="p-6">Trạng thái</th>
                            <th class="p-6 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shippers as $shipper)
                            <tr class="bg-white border-b last:border-b-0 hover:bg-gray-50">
                                <td class="p-6">
                                    <div class="flex items-center">
                                        <img src="https://placehold.co/40x40/6366F1/FFFFFF?text={{ strtoupper(substr($shipper->name, 0, 1)) }}"
                                            class="w-10 h-10 rounded-full mr-4">
                                        <div>
                                            <div class="font-semibold text-gray-800">{{ $shipper->name }}</div>
                                            <div class="text-gray-500">{{ $shipper->email }} /
                                                {{ $shipper->phone_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div>Giao thành công: <strong>{{ $shipper->delivered_orders_count }}</strong></div>
                                    <div>Đã nhận: <strong>{{ $shipper->assigned_orders_count }}</strong></div>
                                </td>
                                <td class="p-6">{{ $shipper->created_at->format('d/m/Y') }}</td>
                                <td class="p-6"><span class="status-badge status-{{ $shipper->status }}">
                                        @switch($shipper->status)
                                            @case('active')
                                                Đang hoạt động
                                            @break

                                            @case('inactive')
                                                Không hoạt động
                                            @break

                                            @case('banned')
                                                Đã khóa
                                            @break

                                            @default
                                                {{ $shipper->status }}
                                        @endswitch
                                    </span></td>
                                <td class="p-6 text-center">
                                    <a href="{{ route('admin.shippers.edit', ['shipper' => $shipper, 'warehouse_id' => $warehouse->id]) }}"
                                        class="text-indigo-600 hover:text-indigo-900 text-lg" title="Chỉnh sửa"><i
                                            class="fas fa-edit"></i></a>
                                    <button type="button"
                                        onclick="showDeleteModal({{ $shipper->id }}, '{{ $shipper->name }}')"
                                        class="text-red-600 hover:text-red-900 text-lg ml-4" title="Xóa"><i
                                            class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center p-12 text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">Không tìm thấy nhân viên nào
                                            </h3>
                                            <p class="text-gray-500">Vui lòng thêm nhân viên mới hoặc kiểm tra lại bộ lọc.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $shippers->appends(request()->query())->links() }}</div>
        </div>
        <!-- Modal Xác nhận Xóa -->
        <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-center">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Xóa nhân viên</h3>
                        <div class="mt-2 px-7 py-3">
                            <p class="text-sm text-gray-500">
                                Bạn có chắc chắn muốn xóa nhân viên <span id="shipperName"
                                    class="font-semibold text-gray-900"></span> không?
                            </p>
                        </div>
                        <div class="flex justify-end space-x-3 mt-4">
                            <button onclick="hideDeleteModal()"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                                Hủy
                            </button>
                            <form id="deleteForm" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="warehouse_id" value="{{ $warehouse->id }}">
                                <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700">
                                    Xác nhận Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function showDeleteModal(shipperId, shipperName) {
                document.getElementById('shipperName').textContent = shipperName;
                document.getElementById('deleteForm').action = '{{ route('admin.shippers.destroy', ':id') }}'.replace(':id',
                    shipperId);
                document.getElementById('deleteModal').classList.remove('hidden');
            }

            function hideDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
            }
            // Đóng modal khi click bên ngoài
            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideDeleteModal();
                }
            });
        </script>
    @endsection
