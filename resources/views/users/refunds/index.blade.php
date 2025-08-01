@extends('users.layouts.profile') {{-- nếu bạn có layout, giữ lại --}}
@section('content')

<div class="mt-8">
    <div class="space-y-6">
        @forelse ($refunds as $refund)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
            <div class="p-4 flex flex-col sm:flex-row justify-between sm:items-center border-b bg-gray-50">
                <div class="flex items-center space-x-4">
                    <span class="font-semibold text-gray-800">Yêu cầu #{{ $refund->return_code }}</span>
                    <span class="text-gray-400">|</span>
                    <span class="text-sm text-gray-600">Từ đơn hàng
                        <a href="{{ route('orders.show', $refund->order->id) }}" class="text-red-600 font-medium hover:underline">
                            #{{ $refund->order->order_code }}
                        </a>
                    </span>
                </div>
                <div class="mt-2 sm:mt-0">
                    <span class="status-badge status-{{ $refund->status }}">
                        {{ $refund->status_text }}
                    </span>
                </div>
            </div>

            @php
            $firstItem = $refund->returnItems->first();
            $variant = $firstItem?->orderItem?->variant;
            $product = $variant?->product;
            $image = $product?->coverImage?->url ?? 'https://placehold.co/80x80/e2e8f0/e2e8f0?text=Sản+phẩm';
            @endphp

            <div class="p-4 flex items-center space-x-4">
                <img src="{{ $image }}" alt="Product Image" class="w-20 h-20 rounded-md object-cover flex-shrink-0">
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">{{ $product->name ?? '---' }}</p>
                    <p class="text-sm text-gray-500">
                        Số tiền hoàn lại:
                        <span class="font-medium text-green-600">
                            {{ number_format($refund->refund_amount, 0, ',', '.') }} VNĐ
                        </span>
                    </p>
                </div>
            </div>

            <div class="p-4 bg-gray-50 text-right">
                <a href="{{ route('refunds.show', $refund->id) }}"
                    class="inline-block bg-red-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-700 transition-colors text-sm">
                    Xem chi tiết
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <img src="https://i.imgur.com/3a83g2R.png" alt="Empty Box" class="mx-auto h-40">
            <p class="mt-4 text-gray-600">Bạn chưa có yêu cầu trả hàng nào.</p>
        </div>
        @endforelse

        <div class="mt-6">
            {{ $refunds->links() }}
        </div>
    </div>
</div>
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

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-pending {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .status-approved {
        background-color: #e0f7ec;
        color: #047857;
    }

    .status-processing {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-completed {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-rejected {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .status-cancelled {
        background-color: #f3f4f6;
        color: #6b7280;
    }
</style>

@endsection