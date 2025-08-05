@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa Nhân viên Giao hàng')

@section('content')
<div class="max-w-screen-md mx-auto p-4 md:p-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Chỉnh sửa thông tin: {{ $shipper->name }}</h1>
        <p class="text-gray-500 mt-1">Cập nhật thông tin chi tiết cho tài khoản shipper.</p>
    </header>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-8 rounded-xl shadow-sm">
        <form action="{{ route('admin.shippers.update', $shipper) }}" method="POST">
            @csrf
            @method('PUT') {{-- Quan trọng: Dùng phương thức PUT cho việc update --}}
            @if(request()->has('warehouse_id'))
                <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
            @endif
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và Tên <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $shipper->name) }}" required class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                     <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', $shipper->phone_number) }}" required class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                </div>
                 <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $shipper->email) }}" required class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                 </div>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                        <input type="password" name="password" id="password" class="w-full py-2 px-3 border border-gray-300 rounded-lg" placeholder="Để trống nếu không đổi">
                    </div>
                     <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                 </div>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành phố <span class="text-red-500">*</span></label>
                        <select name="province_code" id="province" required class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg" disabled>
                            <option value="">Chọn Tỉnh/Thành phố</option>
                            @foreach($provinces ?? [] as $province)
                                <option value="{{ $province->code }}" 
                                    @selected(old('province_code', $currentWarehouse?->province_code) == $province->code)>
                                    {{ $province->name_with_type }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="province_code" value="{{ $currentWarehouse?->province_code }}">
                    </div>
                    <div>
                        <label for="warehouse" class="block text-sm font-medium text-gray-700 mb-1">Kho làm việc <span class="text-red-500">*</span></label>
                        <select name="warehouse_id" id="warehouse" required class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg">
                            <option value="">Chọn Kho làm việc</option>
                            @foreach($warehouses ?? [] as $warehouse)
                                <option value="{{ $warehouse->id }}" 
                                    @selected(old('warehouse_id', $currentWarehouse?->id) == $warehouse->id)>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                 <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="status" id="status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg">
                        <option value="active" @selected(old('status', $shipper->status) == 'active')>Đang hoạt động</option>
                        <option value="inactive" @selected(old('status', $shipper->status) == 'inactive')>Không hoạt động</option>
                        <option value="banned" @selected(old('status', $shipper->status) == 'banned')>Đã khóa</option>
                    </select>
                </div>
            </div>
            <div class="pt-8 flex justify-end space-x-3">
                @if(request()->has('warehouse_id'))
                    <a href="{{ route('admin.shippers.warehouse.show', request('warehouse_id')) }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">Hủy</a>
                @else
                    <a href="{{ route('admin.shippers.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">Hủy</a>
                @endif
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg font-semibold">Cập nhật</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('province');
    const warehouseSelect = document.getElementById('warehouse');
    
    // Trong form edit, province luôn bị disabled nên không cần JavaScript
    // Chỉ cần đảm bảo warehouse dropdown hiển thị đúng kho hiện tại
    if (warehouseSelect) {
        // Tự động chọn kho hiện tại nếu có
        const currentWarehouseId = '{{ $currentWarehouse?->id }}';
        if (currentWarehouseId) {
            warehouseSelect.value = currentWarehouseId;
        }
    }
});
</script>
@endpush
