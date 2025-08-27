@extends('admin.layouts.app')

@section('title', 'Lịch sử sử dụng mã giảm giá')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">Lịch sử sử dụng mã giảm giá</h2>
            <p class="mt-1 text-sm text-gray-600">Mã: <span class="font-medium text-indigo-600">{{ $coupon->code }}</span></p>
        </div>
        <a href="{{ route('admin.coupons.show', $coupon->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg flex items-center transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Quay lại chi tiết
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif
    
    <!-- Thẻ tóm tắt -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="h-12 w-12 flex-shrink-0 bg-indigo-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm font-medium">Tổng lượt sử dụng</h3>
                    <span class="text-2xl font-semibold text-gray-900 block mt-1">{{ $usages->total() }}</span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="h-12 w-12 flex-shrink-0 bg-green-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm font-medium">Tổng tiết kiệm cho khách hàng</h3>
                    <span class="text-2xl font-semibold text-gray-900 block mt-1">{{ number_format($totalSavings) }} VND</span>
                    <p class="text-xs text-gray-400 mt-1">(Không bao gồm đơn hàng đã hủy, trả hàng, giao thất bại)</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Chi tiết lượt sử dụng</h3>
            
            <form action="{{ route('admin.coupons.usageHistory', $coupon->id) }}" method="GET" class="flex gap-2">
                <select name="sort" class="rounded-md border-gray-300 py-2 pl-3 pr-10 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                    <option value="highest_amount" {{ request('sort') == 'highest_amount' ? 'selected' : '' }}>Tiết kiệm cao nhất</option>
                    <option value="lowest_amount" {{ request('sort') == 'lowest_amount' ? 'selected' : '' }}>Tiết kiệm thấp nhất</option>
                </select>
                <button type="submit" class="px-3 py-2 bg-gray-100 rounded-md border border-gray-300 text-gray-600 hover:bg-gray-200 text-sm">
                    Lọc
                </button>
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Ngày sử dụng</th>
                        <th scope="col" class="px-6 py-3">Mã đơn hàng</th>
                        <th scope="col" class="px-6 py-3">Khách hàng</th>
                        <th scope="col" class="px-6 py-3">Giá trị đơn hàng</th>
                        <th scope="col" class="px-6 py-3">Tiết kiệm</th>
                        <th scope="col" class="px-6 py-3">Trạng thái đơn hàng</th>
                        <th scope="col" class="px-6 py-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($usages as $usage)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                                                    {{ $usage->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <a href="{{ route('admin.orders.view', $usage->order_id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    #{{ $usage->order_id }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                @if ($usage->user)
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $usage->user->name }}</div>
                                        <div class="text-gray-500 text-xs">{{ $usage->user->email }}</div>
                                    </div>
                                @else
                                    <span class="italic text-gray-500">Khách vãng lai</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($usage->order)
                                    {{ number_format($usage->order->total_amount) }} VND
                                @else
                                    <span class="text-gray-400">--</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-green-600">
                                @if ($usage->order)
                                    {{ number_format($usage->order->discount_amount) }} VND
                                @else
                                    <span class="text-gray-400">--</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($usage->order)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-600 mr-1"></span>
                                        {{ $usage->order->status_text }}
                                    </span>
                                @else
                                    <span class="text-gray-400">--</span>
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
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Chưa có lượt sử dụng nào cho mã giảm giá này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($usages->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $usages->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 