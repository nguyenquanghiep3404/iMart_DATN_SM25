@extends('users.layouts.profile')

@section('styles')
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f9fafb;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        transition: background-color 0.2s, color 0.2s;
        color: #374151;
    }

    .sidebar-link:hover {
        background-color: #f3f4f6;
    }

    .sidebar-link.active {
        background-color: #fee2e2;
        color: #dc2626;
        font-weight: 600;
    }

    .tab-link {
        padding: 0.5rem 1rem;
        border-bottom: 2px solid transparent;
        transition: border-color 0.2s, color 0.2s;
        color: #6b7280;
    }

    .tab-link:hover {
        color: #111827;
    }

    .tab-link.active {
        color: #dc2626;
        border-color: #dc2626;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto flex space-x-8">

    <main class="w-3/4 xl:w-4/5">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Chi tiết yêu cầu trả hàng</h1>

            <a href="{{ route('orders.returns') }}" class="text-sm font-medium text-gray-600 hover:text-red-600 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Quay lại danh sách trả hàng
            </a>
        </div>


        <div class="mt-8">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <main class="p-6 md:p-8">
                    @php
                    $statusClass = match($returnRequest->status) {
                    'rejected' => 'bg-red-100 border-red-500 text-red-800',
                    'completed', 'refunded' => 'bg-green-100 border-green-500 text-green-800',
                    'pending' => 'bg-yellow-100 border-yellow-500 text-yellow-800',
                    'approved', 'processing' => 'bg-blue-100 border-blue-500 text-blue-800',
                    default => 'bg-gray-100 border-gray-500 text-gray-800',
                    };
                    @endphp

                    <div class="{{ $statusClass }} border-l-4 p-4 rounded-md mb-8" role="alert">
                        <p class="font-bold text-lg">
                            Yêu cầu trả hàng/hoàn tiền #{{ $returnRequest->return_code }} đã {{ $returnRequest->status_text }}
                        </p>
                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8 text-center">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Mã đơn hàng gốc</h3>
                            <p class="text-base font-semibold text-red-600 hover:underline">
                                <a href="{{ route('orders.show', $returnRequest->order->id) }}">
                                    {{ $returnRequest->order->order_code }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Ngày gửi yêu cầu</h3>
                            <p class="text-base font-semibold text-gray-800">
                                {{ $returnRequest->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Ngày hoàn tất</h3>
                            <p class="text-base font-semibold text-gray-800">
                                {{ $returnRequest->refunded_at ? \Carbon\Carbon::parse($returnRequest->refunded_at)->format('d/m/Y') : 'Chưa hoàn tất' }}
                            </p>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Quá trình xử lý</h3>
                        <ol class="relative border-l border-gray-300">
                            @foreach ($returnRequest->logs ?? [] as $log)
                            <li class="mb-10 ml-8">
                                <span class="absolute flex items-center justify-center w-6 h-6 bg-green-200 rounded-full -left-3 ring-4 ring-white">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                <h4 class="font-semibold text-gray-800">{{ $log->action }}</h4>
                                <time class="block mb-2 text-sm font-normal leading-none text-gray-500">
                                    {{ $log->created_at->format('d/m/Y - H:i') }}
                                </time>
                                <p class="text-sm font-normal text-gray-600">{{ $log->description }}</p>
                            </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Sản phẩm được hoàn tiền</h3>
                        <div class="border rounded-lg">
                            @foreach ($returnRequest->returnItems as $item)
                            @php
                            $variant = $item->orderItem->variant;
                            $product = $variant->product;
                            $image = $product->coverImage->url ?? 'https://placehold.co/80x80';
                            @endphp
                            <div class="flex items-center space-x-4 p-4">
                                <img src="{{ asset('storage/' . $image) }}" class="w-20 h-20 rounded-md object-cover">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800">{{ $product->name }}</p>
                                    <p class="text-sm text-gray-500">Phân loại: {{ $variant->name }}</p>
                                    <p class="text-sm text-gray-500">Số lượng: {{ $item->quantity }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-800">
                                        {{ number_format($item->orderItem->price * $item->quantity, 0, ',', '.') }} VNĐ
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Chi tiết hoàn tiền</h3>
                        @if ($returnRequest->status === 'rejected' && $returnRequest->rejection_reason)
                        <div class="mt-10">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Lý do từ chối</h3>
                            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-md">
                                {{ $returnRequest->rejection_reason }}
                            </div>
                        </div>
                        @else

                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tổng giá trị sản phẩm:</span>
                                    <span class="font-medium text-gray-800">
                                        {{ number_format($returnRequest->refund_amount, 0, ',', '.') }} VNĐ
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Phí xử lý/vận chuyển trả hàng:</span>
                                    <span class="font-medium text-gray-800">- 0 VNĐ</span>
                                </div>
                                <hr>
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-bold text-gray-900">Tổng số tiền đã hoàn lại:</span>
                                    <span class="text-xl font-bold text-green-600">
                                        {{ number_format($returnRequest->refund_amount, 0, ',', '.') }} VNĐ
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm pt-2">
                                    <span class="text-gray-600">Phương thức hoàn tiền:</span>
                                    <span class="font-medium text-gray-800">
                                        @switch($returnRequest->refund_method)
                                        @case('points') Điểm tích lũy @break
                                        @case('coupon') Mã giảm giá @break
                                        @case('bank') {{ $returnRequest->bank_name }} - {{ $returnRequest->bank_account_number }} @break
                                        @default Khác
                                        @endswitch
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </main>
                <footer class="p-6 bg-gray-50 text-center">
                    <a href="/" class="inline-block bg-red-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-red-700 transition-colors">
                        Tiếp tục mua sắm
                    </a>
                </footer>
            </div>
        </div>
    </main>
</div>
@endsection