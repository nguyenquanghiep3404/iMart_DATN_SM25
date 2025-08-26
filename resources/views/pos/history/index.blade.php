@extends('pos.layouts.pos')

@section('title', 'Lịch sử Giao dịch POS')

@push('styles')
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f0f2f5;
    }
    .modal {
        transition: opacity 0.3s ease;
    }
    .modal-content {
        transition: transform 0.3s ease;
    }
    .payment-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25em 0.6em;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.375rem;
    }
    .payment-cash { background-color: #D1FAE5; color: #065F46; }
    .payment-card { background-color: #DBEAFE; color: #1E40AF; }
    .payment-qr { background-color: #FEF3C7; color: #92400E; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a3a3a3; }
</style>
@endpush

{{-- BẠN KHÔNG CẦN SECTION HEADER Ở ĐÂY VÌ LAYOUT ĐÃ CÓ RỒI --}}

@section('content')
{{-- THÊM DIV CONTAINER BỌC BÊN NGOÀI ĐỂ TẠO LỀ --}}
<div class="container mx-auto p-4 sm:p-6 lg:p-8">

    {{-- Nhúng phần header của trang --}}
    @include('pos.history._header')

    {{-- Nhúng phần bảng danh sách đơn hàng --}}
    @include('pos.history._order_list')

    {{-- Nhúng phần modal chi tiết đơn hàng --}}
    @include('pos.history._order_detail_modal')

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ordersTableBody = document.getElementById('orders-table-body');
        const modal = document.getElementById('order-detail-modal');

        function formatCurrency(value) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
        }

        function openModal(orderData) {
            if (!orderData) return;

            const orderDate = new Date(orderData.created_at);
            const formattedDate = orderDate.toLocaleDateString('vi-VN', {
                day: '2-digit', month: '2-digit', year: 'numeric'
            });
            const formattedTime = orderDate.toLocaleTimeString('vi-VN', {
                hour: '2-digit', minute: '2-digit'
            });

            document.getElementById('modal-order-code').textContent = orderData.order_code;
            document.getElementById('modal-order-date').textContent = `${formattedDate} ${formattedTime}`;
            document.getElementById('modal-order-customer').textContent = orderData.customer_name || 'Khách lẻ';
            document.getElementById('modal-order-staff').textContent = orderData.processor ? orderData.processor.name : 'N/A';
            document.getElementById('modal-order-payment').textContent = orderData.payment_method;

            const itemsContainer = document.getElementById('modal-order-items');
            itemsContainer.innerHTML = orderData.items.map(item => `
                <div class="flex justify-between items-center text-sm py-2 border-b border-gray-100">
                    <div>
                        <p class="font-semibold text-gray-800">${item.product_name}</p>
                        <p class="text-gray-500">${item.quantity} x ${formatCurrency(item.price)}</p>
                    </div>
                    <p class="font-medium text-gray-900">${formatCurrency(item.total_price)}</p>
                </div>
            `).join('');
            
            document.getElementById('modal-summary-subtotal').textContent = formatCurrency(orderData.sub_total);
            document.getElementById('modal-summary-discount').textContent = '- ' + formatCurrency(orderData.discount_amount);
            document.getElementById('modal-summary-grand-total').textContent = formatCurrency(orderData.grand_total);

            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.querySelector('.modal-content').classList.remove('-translate-y-10');
        }

        window.closeModal = function() {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.querySelector('.modal-content').classList.add('-translate-y-10');
        }

        ordersTableBody.addEventListener('click', function(e) {
            const viewButton = e.target.closest('.view-order-btn');
            if (viewButton) {
                const orderData = JSON.parse(viewButton.dataset.orderJson);
                openModal(orderData);
            }
        });
    });
</script>
@endpush