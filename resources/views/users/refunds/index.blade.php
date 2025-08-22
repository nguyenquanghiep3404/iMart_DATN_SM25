@extends('users.layouts.profile') {{-- nếu bạn có layout, giữ lại --}}
@section('content')

<div class="mt-8">
    <div class="space-y-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 fw-bold">Yêu cầu trả hàng</h3>
            <div class="search-bar" style="max-width: 400px; margin-left: auto;">
                <form action="{{ route('orders.returns') }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Tìm theo mã yêu cầu, đơn hàng..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="border-bottom mb-4">
            <nav class="d-flex flex-wrap" style="gap: 1.5rem;">
                <a href="{{ route('orders.index') }}" class="tab-link {{ empty($status) ? 'active' : '' }}">Tất cả</a>
                <a href="{{ route('orders.index', ['status' => 'pending_confirmation']) }}" class="tab-link {{ $status == 'pending_confirmation' ? 'active' : '' }}">Chờ xác nhận</a>
                <a href="{{ route('orders.index', ['status' => 'processing']) }}" class="tab-link {{ $status == 'processing' ? 'active' : '' }}">Đang xử lý</a>
                <a href="{{ route('orders.index', ['status' => 'shipped']) }}" class="tab-link {{ $status == 'shipped' ? 'active' : '' }}">Đang giao</a>
                <a href="{{ route('orders.index', ['status' => 'delivered']) }}" class="tab-link {{ $status == 'delivered' ? 'active' : '' }}">Hoàn tất</a>
                <a href="{{ route('orders.index', ['status' => 'cancelled']) }}" class="tab-link {{ $status == 'cancelled' ? 'active' : '' }}">Đã hủy</a>
                <a href="{{ route('orders.returns') }}" class="tab-link {{ request()->routeIs('orders.returns') ? 'active' : '' }}">Trả hàng</a>
            </nav>
        </div>
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

            <div class="refund-card">
                <div class="p-4 flex items-center space-x-4">
                    @php
                    $firstItem = $refund->returnItems->first();
                    $variant = $firstItem?->orderItem?->variant;
                    $product = $variant?->product;

                    if ($variant && $variant->primaryImage && Storage::disk('public')->exists($variant->primaryImage->path)) {
                    $imageUrl = Storage::url($variant->primaryImage->path);
                    } elseif ($product && $product->coverImage && Storage::disk('public')->exists($product->coverImage->path)) {
                    $imageUrl = Storage::url($product->coverImage->path);
                    } else {
                    $imageUrl = asset('images/placeholder.jpg'); 
                    }
                    @endphp
                    
                    <img src="{{ $imageUrl }}" alt="{{ $variant?->name ?? $product?->name ?? 'Sản phẩm' }}" class="w-24 h-24 rounded-md">

                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">
                            {{ $product?->name ?? '---' }}
                            @if($refund->returnItems->count() > 1)
                            và {{ $refund->returnItems->count() - 1 }} sản phẩm khác
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">
                            Số tiền hoàn lại:
                            <span class="font-medium text-green-600">
                                {{ number_format($refund->refund_amount, 0, ',', '.') }} VNĐ
                            </span>
                        </p>
                    </div>
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
        <div class="empty-orders-container">
            <img src="https://fptshop.com.vn/img/empty_state.png?w=640&q=75" alt="Không có đơn hàng">
            <p>Bạn chưa có đơn hàng nào.</p>
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

    .empty-orders-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 60px 20px;
        background-color: #f9fafb;
        border-radius: 8px;
        margin-top: 2rem;
        text-align: center;
    }

    .empty-orders-container img {
        width: 250px;
        margin-bottom: 1.5rem;
    }

    .empty-orders-container p {
        font-size: 1.1rem;
        color: #6c757d;
    }
</style>

@endsection