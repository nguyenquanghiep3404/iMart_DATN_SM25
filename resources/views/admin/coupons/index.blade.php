@extends('admin.layouts.app')

@section('title', 'Quản lý mã giảm giá')

@section('content')
<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Quản lý Mã giảm giá</h1>
        <nav aria-label="breadcrumb" class="mt-2">
            <ol class="flex text-sm text-gray-500">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Mã giảm giá</li>
            </ol>
        </nav>
    </div>
    
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Danh sách mã giảm giá</h2>
        <div class="flex space-x-2">
            <a href="{{ route('admin.coupons.trash') }}" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg flex items-center transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd" />
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 3a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                </svg>
                Thùng rác
            </a>
            <a href="{{ route('admin.coupons.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg flex items-center transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Thêm mã giảm giá
            </a>
        </div>
    </div>

    <!-- Thông báo -->
    <div id="notification" class="fixed top-20 right-6 z-50 hidden">
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-md">
            <div class="flex justify-between items-center">
                <p class="font-medium" id="notification-message"></p>
                <button onclick="closeNotification()" class="text-green-700 hover:text-green-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotification("{{ session('success') }}", "success");
            });
        </script>
    @endif
    
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotification("{{ session('error') }}", "error");
            });
        </script>
    @endif
    
    <!-- Bộ lọc -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6 p-4">
        <form action="{{ route('admin.coupons.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="w-full md:w-auto">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <select id="status" name="status" class="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tất cả</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Vô hiệu</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                </select>
            </div>
            <div class="w-full md:w-auto">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Loại giảm giá</label>
                <select id="type" name="type" class="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tất cả</option>
                    <option value="percentage" {{ request('type') == 'percentage' ? 'selected' : '' }}>Phần trăm</option>
                    <option value="fixed_amount" {{ request('type') == 'fixed_amount' ? 'selected' : '' }}>Số tiền cố định</option>
                </select>
            </div>
            <div class="w-full md:w-auto">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã..." class="block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="w-full md:w-auto flex gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md">
                    Lọc
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-md">
                    Đặt lại
                </a>
            </div>
        </form>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-16 text-center">STT</th>
                        <th scope="col" class="px-6 py-4">Mã</th>
                        <th scope="col" class="px-6 py-4">Loại</th>
                        <th scope="col" class="px-6 py-4">Giá trị</th>
                        <th scope="col" class="px-6 py-4">Đã dùng</th>
                        <th scope="col" class="px-6 py-4">Đơn tối thiểu</th>
                        <th scope="col" class="px-6 py-4">Ngày bắt đầu</th>
                        <th scope="col" class="px-6 py-4">Ngày hết hạn</th>
                        <th scope="col" class="px-6 py-4">Trạng thái</th>
                        <th scope="col" class="px-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($coupons as $index => $coupon)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-center">
                                {{ $coupons->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $coupon->code }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($coupon->type == 'percentage')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Phần trăm
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Số tiền
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium">
                                @if ($coupon->type == 'percentage')
                                    {{ $coupon->value }}%
                                @else
                                    {{ number_format($coupon->value) }} VND
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-700">
                                    {{ $coupon->usages->count() }}
                                    @if ($coupon->max_uses)
                                        <span class="text-gray-500">/ {{ $coupon->max_uses }}</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($coupon->min_order_amount)
                                    {{ number_format($coupon->min_order_amount) }} VND
                                @else
                                    <span class="text-gray-400">Không</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($coupon->start_date)
                                    <span class="text-gray-600">
                                        {{ $coupon->start_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($coupon->end_date)
                                    <span class="text-gray-600">
                                        {{ $coupon->end_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($coupon->status == 'active')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-600 mr-1"></span>
                                        Hoạt động
                                    </span>
                                @elseif ($coupon->status == 'inactive')
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-yellow-600 mr-1"></span>
                                        Vô hiệu
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-600 mr-1"></span>
                                        Hết hạn
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.coupons.show', $coupon->id) }}" class="p-1.5 bg-blue-50 rounded-lg text-blue-600 hover:bg-blue-100" title="Xem chi tiết">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="p-1.5 bg-indigo-50 rounded-lg text-indigo-600 hover:bg-indigo-100" title="Chỉnh sửa">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>
                                    <button type="button" 
                                            onclick="document.getElementById('delete-modal-{{ $coupon->id }}').classList.remove('hidden')"
                                            class="p-1.5 bg-red-50 rounded-lg text-red-600 hover:bg-red-100" title="Xóa">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                    
                                    <div class="dropdown relative">
                                        <button type="button" 
                                                class="p-1.5 bg-gray-50 rounded-lg text-gray-600 hover:bg-gray-100 dropdown-toggle" title="Thay đổi trạng thái">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                            </svg>
                                        </button>
                                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu">
                                            <div class="py-1" role="none">
                                                <a href="{{ route('admin.coupons.changeStatus', ['coupon' => $coupon->id, 'status' => 'active']) }}" 
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                   <span class="h-2 w-2 rounded-full bg-green-600 mr-2"></span> Hoạt động
                                                </a>
                                                <a href="{{ route('admin.coupons.changeStatus', ['coupon' => $coupon->id, 'status' => 'inactive']) }}" 
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                   <span class="h-2 w-2 rounded-full bg-yellow-600 mr-2"></span> Vô hiệu
                                                </a>
                                                <a href="{{ route('admin.coupons.changeStatus', ['coupon' => $coupon->id, 'status' => 'expired']) }}" 
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                   <span class="h-2 w-2 rounded-full bg-red-600 mr-2"></span> Hết hạn
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Delete Modal -->
                            <div id="delete-modal-{{ $coupon->id }}" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50" x-transition>
                                <div class="relative p-4 w-full max-w-md h-auto">
                                    <div class="relative bg-white rounded-lg shadow">
                                        <button type="button" onclick="document.getElementById('delete-modal-{{ $coupon->id }}').classList.add('hidden')" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        </button>
                                        <div class="p-6 text-center">
                                            <svg class="mx-auto mb-4 w-14 h-14 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <h3 class="mb-5 text-lg font-medium text-gray-900">Xoá mã giảm giá</h3>
                                            <p class="text-gray-500 mb-4">Bạn có chắc chắn muốn xoá mã giảm giá <span class="font-semibold text-red-600">{{ $coupon->code }}</span>?</p>
                                            <p class="text-gray-500 text-sm mb-6">Thao tác này sẽ xoá tất cả lịch sử sử dụng mã giảm giá này.</p>
                                            <div class="flex justify-center gap-3">
                                                <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                                        Xoá
                                                    </button>
                                                </form>
                                                <button type="button" onclick="document.getElementById('delete-modal-{{ $coupon->id }}').classList.add('hidden')" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                                                    Huỷ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                                Chưa có mã giảm giá nào. Hãy tạo mã giảm giá đầu tiên.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($coupons->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $coupons->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Xử lý dropdown menu
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const menu = this.nextElementSibling;
            menu.classList.toggle('hidden');
        });
    });
    
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
});

// Xử lý thông báo
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const messageElement = document.getElementById('notification-message');
    
    // Đặt nội dung thông báo
    messageElement.textContent = message;
    
    // Đặt kiểu thông báo
    const notificationBox = notification.querySelector('div');
    if (type === 'success') {
        notificationBox.classList.remove('bg-red-100', 'border-red-500', 'text-red-700');
        notificationBox.classList.add('bg-green-100', 'border-green-500', 'text-green-700');
    } else {
        notificationBox.classList.remove('bg-green-100', 'border-green-500', 'text-green-700');
        notificationBox.classList.add('bg-red-100', 'border-red-500', 'text-red-700');
    }
    
    // Hiển thị thông báo
    notification.classList.remove('hidden');
    
    // Tự động ẩn sau 5 giây
    setTimeout(function() {
        notification.classList.add('hidden');
    }, 5000);
}

function closeNotification() {
    document.getElementById('notification').classList.add('hidden');
}
</script>
@endpush
@endsection