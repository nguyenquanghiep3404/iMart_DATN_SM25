@extends('layouts.shipper')

@section('title', 'Đơn hàng hôm nay')

@push('styles')
<style>
    .tab-indicator {
        transition: left 0.3s ease-in-out, width 0.3s ease-in-out;
    }
</style>
@endpush

@section('content')
    <header class="page-header p-5 bg-white flex justify-between items-center">
        <div>
            <p class="text-sm text-gray-500">Xin chào,</p>
            <h1 class="text-xl font-bold text-gray-800">{{ $shipper->name }}</h1>
        </div>
        <button class="relative text-gray-500 hover:text-indigo-600 h-10 w-10 flex items-center justify-center">
            <i class="fas fa-bell fa-lg"></i>
            <span class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-red-500 border-2 border-white"></span>
        </button>
    </header>

    <nav class="page-header sticky top-0 bg-white z-10">
        <div class="relative flex border-b border-gray-200">
            <button data-tab="pickup" class="tab-btn flex-1 p-4 text-sm font-semibold text-indigo-600">Cần Lấy ({{ $ordersToPickup->count() }})</button>
            <button data-tab="shipping" class="tab-btn flex-1 p-4 text-sm font-semibold text-gray-500">Đang Giao ({{ $ordersInTransit->count() }})</button>
            <div id="tab-indicator" class="tab-indicator absolute bottom-0 h-1 bg-indigo-600 rounded-t-full"></div>
        </div>
    </nav>

    <main class="page-content p-4 space-y-3 bg-gray-50">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div id="pickup-list" class="tab-content space-y-3">
            @forelse($ordersToPickup as $order)
                @include('shipper.partials.order_card', ['order' => $order])
            @empty
                <p class="text-center text-gray-500 pt-10">Không có đơn hàng nào cần lấy.</p>
            @endforelse
        </div>
        <div id="shipping-list" class="tab-content hidden space-y-3">
             @forelse($ordersInTransit as $order)
                @include('shipper.partials.order_card', ['order' => $order])
            @empty
                <p class="text-center text-gray-500 pt-10">Không có đơn hàng nào đang giao.</p>
            @endforelse
        </div>
    </main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        const tabIndicator = document.getElementById('tab-indicator');

        function updateTabIndicator(selectedTab) {
            tabIndicator.style.left = `${selectedTab.offsetLeft}px`;
            tabIndicator.style.width = `${selectedTab.offsetWidth}px`;
        }

        // Set initial indicator position
        const initialTab = document.querySelector('.tab-btn[data-tab="pickup"]');
        if (initialTab) {
            updateTabIndicator(initialTab);
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.dataset.tab;

                // Update button styles
                tabButtons.forEach(btn => btn.classList.replace('text-indigo-600', 'text-gray-500'));
                button.classList.replace('text-gray-500', 'text-indigo-600');

                // Update indicator
                updateTabIndicator(button);

                // Show content
                tabContents.forEach(content => content.classList.add('hidden'));
                document.getElementById(`${tabId}-list`).classList.remove('hidden');
            });
        });
    });
</script>
@endpush
