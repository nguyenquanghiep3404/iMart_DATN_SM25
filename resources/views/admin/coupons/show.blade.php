@extends('admin.layouts.app')

@section('title', 'Chi tiết mã giảm giá')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Chi tiết mã giảm giá: <span class="text-indigo-600">{{ $coupon->code }}</span></h2>
        <a href="{{ route('admin.coupons.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg flex items-center transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Quay lại danh sách
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Thông tin cơ bản -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Thông tin cơ bản</h3>
            </div>
            <div class="px-6 py-5">
                <dl class="divide-y divide-gray-200">
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Mã giảm giá</dt>
                        <dd class="text-sm font-semibold text-gray-900 col-span-2">{{ $coupon->code }}</dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Mô tả</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $coupon->description ?: 'Không có mô tả' }}</dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Loại giảm giá</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @if ($coupon->type == 'percentage')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Phần trăm ({{ $coupon->value }}%)
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Số tiền cố định ({{ number_format($coupon->value) }} VND)
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Trạng thái</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @if ($coupon->status == 'active')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-600 mr-1"></span> Hoạt động
                                </span>
                            @elseif ($coupon->status == 'inactive')
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-600 mr-1"></span> Vô hiệu
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-600 mr-1"></span> Hết hạn
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Hiển thị công khai</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @if ($coupon->is_public)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Có</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Không</span>
                            @endif
                        </dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Người tạo</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            {{ $coupon->createdBy ? $coupon->createdBy->name : 'Hệ thống' }}
                        </dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Ngày tạo</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $coupon->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Cập nhật gần nhất</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $coupon->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        
        <!-- Điều kiện và giới hạn -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Điều kiện & giới hạn</h3>
                </div>
                <div class="px-6 py-5">
                    <dl class="divide-y divide-gray-200">
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Tổng lượt sử dụng</dt>
                            <dd class="text-sm text-gray-900 col-span-2">
                                <span class="font-semibold">{{ $totalUsages }}</span>
                                @if ($coupon->max_uses)
                                    <span class="text-gray-500">/ {{ $coupon->max_uses }}</span>
                                @else
                                    <span class="text-gray-500">(không giới hạn)</span>
                                @endif
                            </dd>
                        </div>
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Giới hạn lượt/người</dt>
                            <dd class="text-sm text-gray-900 col-span-2">
                                @if ($coupon->max_uses_per_user)
                                    {{ $coupon->max_uses_per_user }} lần
                                @else
                                    <span class="text-gray-500">Không giới hạn</span>
                                @endif
                            </dd>
                        </div>
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Đơn hàng tối thiểu</dt>
                            <dd class="text-sm text-gray-900 col-span-2">
                                @if ($coupon->min_order_amount)
                                    {{ number_format($coupon->min_order_amount) }} VND
                                @else
                                    <span class="text-gray-500">Không yêu cầu</span>
                                @endif
                            </dd>
                        </div>
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Thời gian hiệu lực</dt>
                            <dd class="text-sm text-gray-900 col-span-2">
                                @if ($coupon->start_date && $coupon->end_date)
                                    {{ $coupon->start_date->format('d/m/Y') }} đến {{ $coupon->end_date->format('d/m/Y') }}
                                @elseif ($coupon->start_date)
                                    Từ {{ $coupon->start_date->format('d/m/Y') }} (không hạn chế)
                                @elseif ($coupon->end_date)
                                    Đến {{ $coupon->end_date->format('d/m/Y') }}
                                @else
                                    <span class="text-gray-500">Không giới hạn thời gian</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <!-- Thống kê sử dụng -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Thống kê sử dụng</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.coupons.usageHistory', $coupon->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Xem lịch sử sử dụng
                        </a>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Chỉnh sửa
                            </a>
                            <button type="button" onclick="document.getElementById('delete-modal').classList.remove('hidden')" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Xóa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lượt sử dụng gần đây -->
    @if($coupon->usages->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mt-6">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-medium text-gray-900">Lượt sử dụng gần đây (10 gần nhất)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Ngày sử dụng</th>
                        <th scope="col" class="px-6 py-3">Mã đơn hàng</th>
                        <th scope="col" class="px-6 py-3">Khách hàng</th>
                        <th scope="col" class="px-6 py-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($coupon->usages->take(10) as $usage)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                                                                            {{ $usage->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            <a href="{{ route('admin.orders.view', $usage->order_id) }}" class="text-indigo-600 hover:text-indigo-900">{{ $usage->order->order_code ?? '#' . $usage->order_id }}</a>
                        </td>
                        <td class="px-6 py-4">
                            @if($usage->user)
                                <div>
                                    <div class="font-medium text-gray-900">{{ $usage->user->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $usage->user->email }}</div>
                                </div>
                            @else
                                <span class="italic text-gray-500">Khách vãng lai</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.orders.view', $usage->order_id) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-0.5 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Xem đơn hàng
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Delete Modal -->
<div id="delete-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50" x-transition>
    <div class="relative p-4 w-full max-w-md h-auto">
        <div class="relative bg-white rounded-lg shadow">
            <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
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
                    <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                        Huỷ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
