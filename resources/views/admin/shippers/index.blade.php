@extends('admin.layouts.app')

@section('title', 'Quản lý Nhân viên Giao hàng')

@push('styles')
<style>
    .status-badge { padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .status-active { background-color: #dcfce7; color: #16a34a; }
    .status-inactive { background-color: #f3f4f6; color: #4b5563; }
    .status-banned { background-color: #fee2e2; color: #dc2626; }
</style>
@endpush

@section('content')
<div class="max-w-screen-2xl mx-auto p-4 md:p-8">
    <header class="mb-8 flex flex-col md:flex-row justify-between md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Quản lý Nhân viên Giao hàng</h1>
            <p class="text-gray-500 mt-1">Thêm mới, tìm kiếm và quản lý thông tin các tài xế.</p>
        </div>
        <a href="{{ route('admin.shippers.create') }}" class="mt-4 md:mt-0 px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center justify-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Thêm nhân viên mới</span>
        </a>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
            <div class="bg-blue-100 text-blue-600 p-4 rounded-full"><i class="fas fa-users fa-xl"></i></div>
            <div><p class="text-sm text-gray-500">Tổng tài xế</p><p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
            <div class="bg-green-100 text-green-600 p-4 rounded-full"><i class="fas fa-user-check fa-xl"></i></div>
            <div><p class="text-sm text-gray-500">Đang hoạt động</p><p class="text-2xl font-bold text-gray-800">{{ $stats['active'] }}</p></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
            <div class="bg-gray-100 text-gray-600 p-4 rounded-full"><i class="fas fa-user-clock fa-xl"></i></div>
            <div><p class="text-sm text-gray-500">Tạm nghỉ</p><p class="text-2xl font-bold text-gray-800">{{ $stats['inactive'] }}</p></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
            <div class="bg-yellow-100 text-yellow-600 p-4 rounded-full"><i class="fas fa-box fa-xl"></i></div>
            <div><p class="text-sm text-gray-500">Tổng đơn đã nhận</p><p class="text-2xl font-bold text-gray-800">{{ $stats['assigned'] }}</p></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm flex items-center space-x-4">
            <div class="bg-indigo-100 text-indigo-600 p-4 rounded-full"><i class="fas fa-truck-ramp-box fa-xl"></i></div>
            <div><p class="text-sm text-gray-500">Tổng đơn đã giao</p><p class="text-2xl font-bold text-gray-800">{{ $stats['delivered'] }}</p></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <form action="{{ route('admin.shippers.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label for="search-input" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                        <input type="text" name="search" id="search-input" placeholder="Tên, Email, SĐT..." value="{{ request('search') }}" class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select name="status" id="status-filter" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg">
                            <option value="">Tất cả</option>
                            <option value="active" @selected(request('status') == 'active')>Đang hoạt động</option>
                            <option value="inactive" @selected(request('status') == 'inactive')>Không hoạt động</option>
                            <option value="banned" @selected(request('status') == 'banned')>Đã khóa</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-3">
                        <button type="submit" class="w-full px-5 py-2 bg-indigo-600 text-white rounded-lg font-semibold">Áp dụng</button>
                        <a href="{{ route('admin.shippers.index') }}" class="w-full px-5 py-2 bg-gray-200 text-gray-700 rounded-lg text-center font-semibold">Xóa lọc</a>
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
                                <img src="https://placehold.co/40x40/6366F1/FFFFFF?text={{ strtoupper(substr($shipper->name, 0, 1)) }}" class="w-10 h-10 rounded-full mr-4">
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $shipper->name }}</div>
                                    <div class="text-gray-500">{{ $shipper->email }} / {{ $shipper->phone_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-6">
                            <div>Giao thành công: <strong>{{ $shipper->delivered_orders_count }}</strong></div>
                            <div>Đã nhận: <strong>{{ $shipper->assigned_orders_count }}</strong></div>
                        </td>
                        <td class="p-6">{{ $shipper->created_at->format('d/m/Y') }}</td>
                        <td class="p-6"><span class="status-badge status-{{ $shipper->status }}">{{ $shipper->status }}</span></td>
                        <td class="p-6 text-center">
                            <a href="{{ route('admin.shippers.edit', $shipper) }}" class="text-indigo-600 hover:text-indigo-900 text-lg" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.shippers.destroy', $shipper) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-lg" title="Xóa"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center p-12 text-gray-500">Không tìm thấy nhân viên nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $shippers->appends(request()->query())->links() }}</div>
</div>
@endsection
