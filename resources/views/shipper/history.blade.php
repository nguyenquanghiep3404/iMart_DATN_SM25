@extends('layouts.shipper')

@section('title', 'Lịch sử giao hàng')

@section('content')
    <header class="page-header p-5 bg-white border-b flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800">Lịch sử đơn hàng</h1>
    </header>

    <main class="page-content p-4 space-y-3 bg-gray-50">
        @forelse($ordersHistory as $order)
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <a href="{{ route('shipper.orders.show', $order) }}" class="block">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-bold text-gray-800">{{ $order->order_code }}</span>
                        <span class="text-xs text-gray-400">{{ $order->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 truncate">{{ $order->customer_name }}</p>
                    <div class="mt-2 pt-2 border-t border-dashed">
                        @if ($order->status === 'delivered')
                            <p class="text-sm text-green-600 font-bold flex items-center"><i class="fas fa-check-circle mr-2"></i> Giao thành công</p>
                        @elseif ($order->status === 'failed_delivery')
                            <p class="text-sm text-red-600 font-bold flex items-center"><i class="fas fa-times-circle mr-2"></i> Giao thất bại</p>
                        @else
                            <p class="text-sm text-gray-500 font-bold flex items-center"><i class="fas fa-ban mr-2"></i> {{ $order->status }}</p>
                        @endif
                    </div>
                </a>
            </div>
        @empty
            <p class="text-center text-gray-500 pt-10">Chưa có lịch sử giao hàng.</p>
        @endforelse

        {{-- Hiển thị các nút phân trang của Laravel --}}
        <div class="mt-6 px-4">
            {{ $ordersHistory->links() }}
        </div>
    </main>
@endsection
