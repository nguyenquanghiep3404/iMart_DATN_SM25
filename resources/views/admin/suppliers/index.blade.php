@extends('admin.layouts.app')

@section('title', 'Quản lý Nhà cung cấp')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-full">

        <!-- PAGE HEADER -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Quản lý Nhà cung cấp</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Nhà cung cấp</li>
                </ol>
            </nav>
        </header>

        <div class="card-custom">
            <div class="card-custom-header flex justify-between items-center">
                <h3 class="card-custom-title">Danh sách nhà cung cấp ({{ count($suppliers) }})</h3>
                <a href="{{ route('admin.suppliers.trash') }}" class="btn btn-secondary right">
                    <i class="fas fa-trash-alt mr-1"></i> Thùng rác
                </a>
                <button onclick="document.getElementById('modal-add').classList.remove('hidden')" type="button" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Thêm nhà cung cấp
                </button>
            </div>

            <div class="card-custom-body">
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width: 50px;">STT</th>
                                <th>Tên Nhà cung cấp</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Địa chỉ</th>
                                <th style="width: 120px;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $index => $supplier)
                            <tr>
                                <td>{{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $loop->iteration }}</td>
                                <td class="font-semibold">{{ $supplier->name }}</td>
                                <td>{{ $supplier->email ?? 'N/A' }}</td>
                                <td>{{ $supplier->phone ?? 'N/A' }}</td>
                                <td>
                                    {{ $supplier->address_line }}
                                    @if ($supplier->ward || $supplier->district || $supplier->province)
                                    <br>
                                    <small class="text-gray-500">
                                        {{ implode(', ', array_filter([
                $supplier->ward->name_with_type ?? null,
                $supplier->district->name_with_type ?? null,
                $supplier->province->name_with_type ?? null
            ])) }}
                                    </small>
                                    @endif
                                </td>



                                <td class="text-center">
                                    <div class="inline-flex space-x-1">
                                        <button type="button"
                                            onclick='openEditModal(@json($supplier))'
                                            class="btn btn-primary btn-sm" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xoá?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-6 text-gray-500">Không có nhà cung cấp nào.</td>
                            </tr>
                            @endforelse
                        </tbody>

                    </table>
                    <div class="mt-4">
                        {{ $suppliers->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL THÊM NHÀ CUNG CẤP -->
<div id="modal-add" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <form id="add-supplier-form" method="POST" class="bg-white rounded-lg shadow-lg w-full max-w-2xl">
        @csrf
        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Thêm Nhà cung cấp mới</h3>

            <div class="mt-4 grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4">
                <!-- Tên nhà cung cấp -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Tên nhà cung cấp*</label>
                    <input type="text" name="name" class="form-input mt-1">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Tỉnh/Thành phố -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tỉnh/Thành phố*</label>
                    <select name="province_code" class="form-select mt-1">
                        <option value="">-- Chọn Tỉnh/Thành phố --</option>
                        @foreach ($provinces as $province)
                        <option value="{{ $province->code }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                    @error('province_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" class="form-input mt-1">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Quận/Huyện -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quận/Huyện*</label>
                    <select name="district_code" class="form-select mt-1">
                        <option value="">-- Chọn Quận/Huyện --</option>
                        {{-- Sẽ được JS load bằng loadDistricts() --}}
                    </select>
                    @error('district_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Số điện thoại -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                    <input type="text" name="phone" class="form-input mt-1">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Phường/Xã -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phường/Xã*</label>
                    <select name="ward_code" class="form-select mt-1">
                        <option value="">-- Chọn Phường/Xã --</option>
                        {{-- Sẽ được JS load bằng loadWards() --}}
                    </select>
                    @error('ward_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Địa chỉ -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Địa chỉ chi tiết</label>
                    <input type="text" name="address_line" class="form-input mt-1" placeholder="VD: Số nhà, đường, khu phố...">
                    @error('address_line') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button type="submit" class="btn btn-primary w-full sm:ml-3 sm:w-auto">Lưu</button>
            <button type="button" onclick="closeModal('modal-add')" class="btn btn-secondary">Hủy</button>
        </div>
    </form>
</div>


<!-- MODAL CHỈNH SỬA NHÀ CUNG CẤP -->
<div id="modal-edit" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-xl">
        <form id="edit-form" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Chỉnh sửa Nhà cung cấp</h2>

            <input type="hidden" id="edit-id" name="id">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên nhà cung cấp*</label>
                    <input type="text" id="edit-name" name="name" class="form-input mt-1">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành phố*</label>
                    <select name="province_code" class="form-select mt-1">
                        <option value="">-- Chọn Tỉnh/Thành phố --</option>
                        @foreach ($provinces as $province)
                        <option value="{{ $province->code }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                    @error('province_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="edit-email" name="email" class="form-input mt-1">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện*</label>
                    <select name="district_code" class="form-select mt-1">
                        <option value="">-- Chọn Quận/Huyện --</option>
                    </select>
                    @error('district_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                    <input type="text" id="edit-phone" name="phone" class="form-input mt-1">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phường/Xã*</label>
                    <select name="ward_code" class="form-select mt-1">
                        <option value="">-- Chọn Phường/Xã --</option>
                    </select>
                    @error('ward_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ chi tiết*</label>
                    <input type="text" id="edit-address" name="address_line" class="form-input mt-1">
                    @error('address_line') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-2 mt-6">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="btn btn-secondary">Huỷ</button>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f3f4f6;
    }

    .card-custom {
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        background-color: #fff;
    }

    .card-custom-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background-color: #f9fafb;
    }

    .card-custom-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }

    .card-custom-body {
        padding: 1.5rem;
    }

    .btn {
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1.25rem;
        height: 35px;
        border: 1px solid transparent;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .btn-primary {
        background-color: #4f46e5;
        color: white;
    }

    .btn-primary:hover {
        background-color: #4338ca;
    }

    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        border-color: #d1d5db;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
    }

    .btn-danger {
        background-color: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background-color: #dc2626;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        font-size: 0.875rem;
        background-color: white;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        border-color: #4f46e5;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, .25);
    }

    .table-custom {
        width: 100%;
        min-width: 700px;
        color: #374151;
    }

    .table-custom th,
    .table-custom td {
        padding: 0.75rem 1rem;
        vertical-align: middle !important;
        border-bottom-width: 1px;
        border-color: #e5e7eb;
    }

    .table-custom td {
        white-space: normal;
    }

    .table-custom thead th {
        font-weight: 600;
        color: #4b5563;
        background-color: #f9fafb;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        text-align: left;
        white-space: nowrap;
    }

    [x-cloak] {
        display: none !important;
    }
</style>
@endsection


<script>
    const districts = @json($districts ?? []);
    const wards = @json($wards ?? []);

    function loadDistricts(provinceCode, selected = null) {
        const districtSelects = document.querySelectorAll('select[name="district_code"]');
        districtSelects.forEach(select => {
            select.innerHTML = '';
            const filtered = districts.filter(d => d.parent_code == provinceCode); // <-- Sửa ở đây
            filtered.forEach(d => {
                const option = document.createElement('option');
                option.value = d.code;
                option.text = d.name_with_type;
                if (selected && selected == d.code) option.selected = true;
                select.appendChild(option);
            });
        });
    }

    function loadWards(districtCode, selected = null) {
        const wardSelects = document.querySelectorAll('select[name="ward_code"]');
        wardSelects.forEach(select => {
            select.innerHTML = '';
            const filtered = wards.filter(w => w.parent_code == districtCode);
            filtered.forEach(w => {
                const option = document.createElement('option');
                option.value = w.code;
                option.text = w.name_with_type;
                if (selected && selected == w.code) option.selected = true;
                select.appendChild(option);
            });
        });
    }


    // Tự động load khi chọn tỉnh/thành trong form thêm và sửa
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelects = document.querySelectorAll('select[name="province_code"]');
        provinceSelects.forEach(select => {
            select.addEventListener('change', function() {
                loadDistricts(this.value);
                loadWards(null);
            });
        });

        const districtSelects = document.querySelectorAll('select[name="district_code"]');
        districtSelects.forEach(select => {
            select.addEventListener('change', function() {
                loadWards(this.value);
            });
        });
    });

    function openEditModal(supplier) {
        document.getElementById('edit-id').value = supplier.id;
        document.getElementById('edit-name').value = supplier.name || '';
        document.getElementById('edit-email').value = supplier.email || '';
        document.getElementById('edit-phone').value = supplier.phone || '';
        document.getElementById('edit-address').value = supplier.address_line || '';
        document.getElementById('edit-form').action = `/admin/suppliers/${supplier.id}`;

        // Gán tỉnh
        const provinceSelect = document.querySelector('#edit-form select[name="province_code"]');
        provinceSelect.value = supplier.province_code;

        // Gọi loadDistricts và loadWards
        loadDistricts(supplier.province_code, supplier.district_code); // Gọi xong thì gán tiếp
        setTimeout(() => {
            loadWards(supplier.district_code, supplier.ward_code);
        }, 10); // Đợi 1 chút để select render xong


        document.getElementById('modal-edit').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const addForm = document.getElementById('add-supplier-form');
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Xoá lỗi cũ
            document.querySelectorAll('.field-error').forEach(el => el.remove());

            const formData = new FormData(this);

            fetch('{{ route('admin.suppliers.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                .then(async res => {
                    const contentType = res.headers.get('content-type');

                    if (contentType && contentType.includes('application/json')) {
                        const data = await res.json();
                        if (data.success) {
                            toastr.success(data.message);
                            closeModal('modal-add');
                            setTimeout(() => location.reload(), 300);
                        } else if (data.errors) {
                            for (const field in data.errors) {
                                data.errors[field].forEach(msg => {
                                    const input = addForm.querySelector(`[name="${field}"]`);
                                    if (input) {
                                        const error = document.createElement('p');
                                        error.classList.add('field-error', 'mt-1', 'text-sm', 'text-red-600');
                                        error.innerText = msg;
                                        input.insertAdjacentElement('afterend', error);
                                    }
                                });
                            }
                            toastr.error('Vui lòng kiểm tra lại các trường bị lỗi');
                        } else {
                            toastr.error(data.message || 'Có lỗi xảy ra');
                        }

                    } else {
                        const html = await res.text();
                        toastr.error("Server không trả về JSON hợp lệ");
                    }
                })
                .catch(err => {
                    console.error("Lỗi kết nối hoặc xử lý fetch:", err);
                    toastr.error("Lỗi kết nối máy chủ");
                });

        });
    });

    document.addEventListener('DOMContentLoaded', function () {
    const editForm = document.getElementById('edit-form');
    editForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const id = document.getElementById('edit-id').value;
        const formData = new FormData(editForm);

        // Xoá lỗi cũ
        editForm.querySelectorAll('.field-error').forEach(el => el.remove());

        fetch(`/admin/suppliers/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
        .then(async res => {
            const contentType = res.headers.get('content-type');
            let data = {};
            if (contentType && contentType.includes('application/json')) {
                data = await res.json();
            } else {
                const text = await res.text();
                toastr.error("Server không trả về JSON hợp lệ");
                console.error(text);
                return;
            }

            if (res.ok && data.success) {
                toastr.success(data.message);
                closeModal('modal-edit'); // ✅ Chỉ đóng khi thành công
                setTimeout(() => location.reload(), 300);
            } else if (res.status === 422 && data.errors) {
                // ❌ Không đóng form
                for (const field in data.errors) {
                    data.errors[field].forEach(msg => {
                        const input = editForm.querySelector(`[name="${field}"]`);
                        if (input) {
                            const error = document.createElement('p');
                            error.classList.add('field-error', 'mt-1', 'text-sm', 'text-red-600');
                            error.innerText = msg;
                            input.insertAdjacentElement('afterend', error);
                        }
                    });
                }
                toastr.error('Vui lòng kiểm tra lại các trường bị lỗi');
            } else {
                toastr.error(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(err => {
            toastr.error('Lỗi kết nối máy chủ');
            console.error(err);
        });
    });
    });
    function closeModal(id) {
        document.getElementById(id)?.classList.add('hidden');
    }
</script>