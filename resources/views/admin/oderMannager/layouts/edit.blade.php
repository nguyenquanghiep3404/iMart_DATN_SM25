@extends('admin.layouts.app')
@section('content')
    <div class="max-w-7xl mt-4 mx-auto p-8 bg-white rounded-2xl shadow-xl overflow-x-auto">
        <h2 class="text-3xl mb-8 text-gray-900 tracking-wide">Chỉnh sửa nhân viên</h2>

        <form method="POST" action="{{ route('admin.order-manager.update', $user->id) }}" class="w-full" novalidate>
            @csrf
            @method('PUT')

            <table class="w-full min-w-full border-separate border-spacing-y-4 border-spacing-x-0">
                <tbody>
                    <tr class="border-b border-gray-200">
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Họ và Tên <span
                                class="text-red-500">*</span></th>
                        <td class="p-4">
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                required
                                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Nguyễn Văn A" />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                    <tr class="border-b border-gray-200">
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Email <span
                                class="text-red-500">*</span></th>
                        <td class="p-4">
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                required
                                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="example@email.com" />
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                    <tr class="border-b border-gray-200">
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Số điện thoại</th>
                        <td class="p-4">
                            <input type="text" name="phone_number" id="phone_number"
                                value="{{ old('phone_number', $user->phone_number) }}"
                                class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="0987654321" />
                            @error('phone_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                    <tr class="border-b border-gray-200">
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Tỉnh/Thành phố <span
                                class="text-red-500">*</span></th>
                        <td class="p-4">
                            <select name="province_code" id="province_code" 
                                class="w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Chọn Tỉnh/Thành phố</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->code }}" 
                                        {{ old('province_code', $user->assignedStoreLocations->first()?->province->code ?? '') == $province->code ? 'selected' : '' }}>
                                        {{ $province->name_with_type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('province_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                    <tr class="border-b border-gray-200">
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Kho làm việc <span
                                class="text-red-500">*</span></th>
                        <td class="p-4">
                            <select name="warehouse_id" id="warehouse_id" 
                                class="w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Chọn Kho</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" 
                                        {{ old('warehouse_id', $user->assignedStoreLocations->first()?->id ?? '') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} - {{ $warehouse->province->name_with_type ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                    <tr class="border-b border-gray-200">
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Trạng thái <span
                                class="text-red-500">*</span></th>
                        <td class="p-4">
                            <select name="status" id="status"
                                class="w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Đang
                                    hoạt động</option>
                                <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>
                                    Không hoạt động</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                    <tr class="border-b border-gray-200">
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">
                            Mật khẩu mới <small class="text-gray-500">(để trống nếu không đổi)</small>
                        </th>
                        <td class="p-4">
                            <div class="relative">
                                <input type="password" name="password" id="password"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2 pr-12
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="••••••••" />
                                <span class="absolute inset-y-0 right-3 flex items-center cursor-pointer text-gray-500"
                                    onclick="togglePassword('password', this)">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                    <tr>
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Xác nhận mật khẩu</th>
                        <td class="p-4">
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2 pr-12
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="••••••••" />
                                <span class="absolute inset-y-0 right-3 flex items-center cursor-pointer text-gray-500"
                                    onclick="togglePassword('password_confirmation', this)">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                </tbody>
            </table>

            <div class="mt-8 flex items-center justify-end space-x-5">
                <a href="{{ $currentWarehouse ? route('admin.order-manager.warehouse.show', $currentWarehouse->id) : route('admin.order-manager.index') }}"
                    class="inline-block px-8 py-3 font-semibold text-gray-600 rounded-lg hover:bg-gray-100 transition duration-300">
                    Hủy
                </a>
                <button type="submit"
                    class="inline-block px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-lg
                    hover:bg-indigo-700 hover:shadow-xl transition duration-300">
                    Lưu
                </button>
            </div>
        </form>
    </div>
@endsection
<script>
    function togglePassword(id, el) {
        const input = document.getElementById(id);
        const icon = el.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    // Cập nhật dropdown kho khi thay đổi tỉnh/thành phố
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('province_code');
        const warehouseSelect = document.getElementById('warehouse_id');
        
        if (provinceSelect && warehouseSelect) {
            // Lấy tất cả warehouses từ server
            let allWarehouses = [];
            @foreach($warehouses as $warehouse)
                allWarehouses.push({
                    id: {{ $warehouse->id }},
                    name: '{{ $warehouse->name }}',
                    province_code: '{{ $warehouse->province_code }}',
                    province_name: '{{ $warehouse->province->name_with_type ?? '' }}'
                });
            @endforeach
            // Filter warehouses khi province thay đổi
            provinceSelect.addEventListener('change', function() {
                filterWarehouses(this.value);
            });
            // Nếu đã có province_code thì filter luôn khi load trang
            if (provinceSelect.value) {
                filterWarehouses(provinceSelect.value);
            }
            function filterWarehouses(provinceCode) {
                warehouseSelect.innerHTML = '<option value="">Chọn Kho</option>';
                if (!provinceCode) return;
                
                const filteredWarehouses = allWarehouses.filter(warehouse =>
                    warehouse.province_code === provinceCode
                );
                filteredWarehouses.forEach(warehouse => {
                    const option = document.createElement('option');
                    option.value = warehouse.id;
                    option.textContent = warehouse.name + ' - ' + warehouse.province_name;
                    warehouseSelect.appendChild(option);
                });
            }
        }
    });
</script>
