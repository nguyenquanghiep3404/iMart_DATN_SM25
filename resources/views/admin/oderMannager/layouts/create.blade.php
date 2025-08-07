@extends('admin.layouts.app')

@section('content')
    <div class="max-w-7xl mt-4 mx-auto p-8 bg-white rounded-2xl shadow-xl overflow-x-auto">
        <h2 class="text-3xl mb-8 text-gray-900 tracking-wide">Thêm mới nhân viên</h2>

        <form method="POST" action="{{ route('admin.order-manager.store') }}" class="w-full" novalidate>
            @csrf
            @if(request()->has('warehouse_id'))
                <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
            @endif

            <table class="w-full min-w-full border-separate border-spacing-y-4 border-spacing-x-0">
                <tbody>
                    <tr class="border-b border-gray-200">
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Họ và Tên <span
                                class="text-red-500">*</span></th>
                        <td class="p-4">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
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
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
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
                            <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"
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
                                class="w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                {{ request()->has('warehouse_id') ? 'disabled' : '' }}>
                                <option value="">Chọn Tỉnh/Thành phố</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->code }}" 
                                        {{ old('province_code', $selectedProvince ?? '') == $province->code ? 'selected' : '' }}>
                                        {{ $province->name_with_type }}
                                    </option>
                                @endforeach
                            </select>
                            @if(request()->has('warehouse_id'))
                                <input type="hidden" name="province_code" value="{{ $selectedProvince }}">
                            @endif
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
                                class="w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                {{ request()->has('warehouse_id') ? 'disabled' : '' }}>
                                <option value="">Chọn Kho</option>
                                @if(request()->has('warehouse_id'))
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" 
                                            {{ old('warehouse_id', request('warehouse_id')) == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }} - {{ $warehouse->province->name_with_type ?? '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @if(request()->has('warehouse_id'))
                                <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
                            @endif
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
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Đang hoạt động
                                </option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                    <tr class="border-b border-gray-200">
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Mật khẩu <span
                                class="text-red-500">*</span></th>
                        <td class="p-4">
                            <div class="relative">
                                <input type="password" name="password" id="password"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2 pr-12
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="••••••••" required />
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
                        <th class="text-left p-4 font-semibold text-gray-700 w-1/3 align-top">Xác nhận mật khẩu <span
                                class="text-red-500">*</span></th>
                        <td class="p-4">
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2 pr-12
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="••••••••" required />
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
                <a href="{{ request()->has('warehouse_id') ? route('admin.order-manager.warehouse.show', request('warehouse_id')) : route('admin.order-manager.index') }}"
                    class="inline-block px-8 py-3 font-semibold text-gray-600 rounded-lg hover:bg-gray-100 transition duration-300">
                    Hủy
                </a>
                <button type="submit"
                    class="inline-block px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-lg
                        hover:bg-indigo-700 hover:shadow-xl transition duration-300">
                    Thêm
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('province_code');
        const warehouseSelect = document.getElementById('warehouse_id');
        // Chỉ chạy  nếu không có warehouse_id từ URL
        if (provinceSelect && warehouseSelect && !provinceSelect.disabled) {
            let allWarehouses = [];
            // Fetch warehouses khi trang load
            fetchWarehouses();
            // Khi chọn tỉnh/thành, filter warehouses
            provinceSelect.addEventListener('change', function() {
                filterWarehouses(this.value);
            });
            function fetchWarehouses() {
                fetch("{{ route('admin.order-manager.warehouses') }}", {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        allWarehouses = data;
                        filterWarehouses(provinceSelect.value);
                    })
                    .catch(error => {
                        allWarehouses = @json($warehouses->map(function($warehouse) {
                            return [
                                'id' => $warehouse->id,
                                'name' => $warehouse->name,
                                'province_code' => $warehouse->province_code
                            ];
                        }));
                        filterWarehouses(provinceSelect.value);
                    });
            }
            function filterWarehouses(provinceCode) {
                // Thêm tùy chọn mặc định
                warehouseSelect.innerHTML = '<option value="">Chọn Kho</option>';
                if (!provinceCode) return;
                // Lọc warehouses theo province_code
                const filteredWarehouses = allWarehouses.filter(warehouse =>
                    warehouse.province_code === provinceCode
                );
                // Thêm các tùy chọn kho vào select
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
</script>
