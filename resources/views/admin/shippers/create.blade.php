@extends('admin.layouts.app')

@section('title', 'Thêm Nhân viên Giao hàng')

@section('content')
<div class="max-w-screen-md mx-auto p-4 md:p-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Thêm nhân viên mới</h1>
        <p class="text-gray-500 mt-1">Điền thông tin chi tiết cho tài khoản shipper.</p>
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
        <form action="{{ route('admin.shippers.store') }}" method="POST">
            @csrf
            @if(request()->has('warehouse_id'))
                <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
            @endif
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và Tên <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"  class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                     <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"  class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                </div>
                 <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"  class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                 </div>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="password"  class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                     <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation"  class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                 </div>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành phố <span class="text-red-500">*</span></label>
                        <select name="province_code" id="province"  class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg" @if(isset($selectedProvince)) disabled @endif>
                            <option value="">Chọn Tỉnh/Thành phố</option>
                            @foreach($provinces ?? [] as $province)
                                <option value="{{ $province->code }}" 
                                    @selected(old('province_code', $selectedProvince->code ?? '') == $province->code)>
                                    {{ $province->name_with_type }}
                                </option>
                            @endforeach
                        </select>
                        @if(isset($selectedProvince))
                            <input type="hidden" name="province_code" value="{{ $selectedProvince->code }}">
                        @endif
                    </div>
                    <div>
                        <label for="warehouse" class="block text-sm font-medium text-gray-700 mb-1">Kho làm việc <span class="text-red-500">*</span></label>
                        <select name="warehouse_id" id="warehouse"  class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg" @if(count($warehouses) == 1) disabled @endif>
                            <option value="">Chọn Kho làm việc</option>
                            @foreach($warehouses ?? [] as $warehouse)
                                <option value="{{ $warehouse->id }}" 
                                    @selected(old('warehouse_id', $warehouse->id) == $warehouse->id)>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @if(count($warehouses) == 1)
                            <input type="hidden" name="warehouse_id" value="{{ $warehouses->first()->id }}">
                        @endif
                    </div>
                </div>
                 <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="status" id="status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg">
                        <option value="active" @selected(old('status') == 'active')>Đang hoạt động</option>
                        <option value="inactive" @selected(old('status') == 'inactive')>Không hoạt động</option>
                        <option value="banned" @selected(old('status') == 'banned')>Đã khóa</option>
                    </select>
                </div>
            </div>
            <div class="pt-8 flex justify-end space-x-3">
                @if(request()->has('warehouse_id'))
                    <a href="{{ route('admin.shippers.warehouse.show', request('warehouse_id')) }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">Hủy</a>
                @else
                    <a href="{{ route('admin.shippers.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">Hủy</a>
                @endif
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg font-semibold">Tạo nhân viên</button>
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
    
    // Chỉ chạy JavaScript nếu không có warehouse_id từ URL (tức là không bị disabled)
    if (!provinceSelect.disabled) {
        // Lưu trữ tất cả warehouses
        let allWarehouses = [];
        
        // Fetch warehouses khi trang load
        fetchWarehouses();
        
        // Khi chọn tỉnh/thành, filter warehouses
        provinceSelect.addEventListener('change', function() {
            const selectedProvince = this.value;
            filterWarehouses(selectedProvince);
        });
        
        function fetchWarehouses() {
            fetch('{{ route("admin.shippers.warehouses") }}')
                .then(response => response.json())
                .then(data => {
                    allWarehouses = data;
                    filterWarehouses(provinceSelect.value);
                })
                .catch(error => {
                    console.error('Error fetching warehouses:', error);
                });
        }
        
        function filterWarehouses(provinceCode) {
            // Clear current options
            warehouseSelect.innerHTML = '<option value="">Chọn Kho làm việc</option>';
            
            if (!provinceCode) return;
            
            // Filter warehouses by province
            const filteredWarehouses = allWarehouses.filter(warehouse => 
                warehouse.province_code === provinceCode
            );
            
            // Add filtered options
            filteredWarehouses.forEach(warehouse => {
                const option = document.createElement('option');
                option.value = warehouse.id;
                option.textContent = warehouse.name;
                warehouseSelect.appendChild(option);
            });
        }
    }
});
</script>
@endpush
