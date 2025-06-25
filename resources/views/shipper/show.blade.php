@extends('layouts.shipper')

@section('title', 'Chi tiết đơn hàng ' . $order->order_code)

@push('styles')
<style>
    /* CSS cho trang chi tiết */
    .detail-container { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .detail-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #dee2e6; padding-bottom: 1rem; margin-bottom: 1.5rem; }
    .detail-header h2 { margin: 0; }
    .back-link { text-decoration: none; background: #6c757d; color: white; padding: 8px 16px; border-radius: 5px; transition: background-color 0.2s; }
    .back-link:hover { background-color: #5a6268; }
    .detail-section { margin-bottom: 2rem; }
    .detail-section h3 { margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
    .info-grid { display: grid; grid-template-columns: 150px 1fr; gap: 10px; align-items: center; }
    .info-grid > div:nth-child(odd) { font-weight: 600; color: #495057; }
    .cod-amount { font-weight: 700; color: #dc3545; font-size: 1.2rem; }
    .maps-link { color: #007bff; font-weight: 600; }
    .shipper-note-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; border-radius: 4px; }
    .action-buttons { display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap; }
    .action-buttons .btn, .action-buttons form button {
        flex-grow: 1; padding: 15px; font-size: 1.1rem; font-weight: 600;
        color: white; border: none; border-radius: 8px; cursor: pointer; text-align: center;
    }
    .btn-pickup { background-color: #0d6efd; }
    .btn-success { background-color: #198754; }
    .btn-fail { background-color: #dc3545; }

    /* CSS cho Bảng sản phẩm */
    .product-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    .product-table th, .product-table td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
    .product-table th { background-color: #f8f9fa; font-weight: 600; }
    .product-table td.text-right { text-align: right; }
    .product-table tbody tr:nth-child(odd) { background-color: #fdfdfd; }

    /* CSS cho Hộp thoại lý do */
    .status-alert { padding: 1rem; margin-bottom: 1.5rem; border-left: 5px solid; border-radius: 8px; }
    .status-alert strong { font-size: 1.1rem; display: block; margin-bottom: 0.5rem; }
    .status-alert p { margin: 0; font-size: 1rem; }
    .alert-danger { border-color: #dc3545; color: #721c24; background-color: #f8d7da; }
    .alert-secondary { border-color: #6c757d; color: #383d41; background-color: #e2e3e5; }

    /* CSS cho Modal */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.6); display: flex;
        justify-content: center; align-items: center; z-index: 1050; padding: 1rem;
    }
    .modal-content {
        background: white; border-radius: 8px; width: 100%; max-width: 500px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3); display: flex;
        flex-direction: column; max-height: 90vh;
    }
    .modal-header, .modal-footer {
        flex-shrink: 0; padding: 1rem 1.5rem;
    }
    .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #dee2e6; }
    .modal-body { overflow-y: auto; padding: 1.5rem; }
    .modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #dee2e6; background-color: #f8f9fa; }
    .modal-header h4 { margin: 0; font-size: 1.25rem; }
    .close-modal-btn { background: none; border: none; font-size: 1.75rem; cursor: pointer; color: #6c757d; }
    .reason-option { display: flex; align-items: center; margin-bottom: 1rem; }
    .reason-option input[type="radio"] { margin-right: 10px; width: 18px; height: 18px; }
    #other-reason-text { width: calc(100% - 20px); margin-top: 10px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; }
    .modal-footer .btn-cancel, .modal-footer .btn-confirm {
        padding: 8px 16px; font-size: 0.9rem; font-weight: 500;
        border-radius: 5px; border: none; cursor: pointer; color: white;
    }
    .modal-footer .btn-cancel { background-color: #6c757d; }
    .modal-footer .btn-confirm { background-color: #dc3545; }

    /* CSS Responsive */
    @media screen and (max-width: 768px) {
        .product-table thead { display: none; }
        .product-table tr { display: block; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 1rem; }
        .product-table td { display: block; text-align: right; border: none; border-bottom: 1px dotted #ccc; padding-left: 50%; position: relative; }
        .product-table td:last-child { border-bottom: 0; }
        .product-table td::before { content: attr(data-label); position: absolute; left: 12px; width: 45%; padding-right: 10px; white-space: nowrap; text-align: left; font-weight: 600; }
        .info-grid { display: block; }
        .info-grid > div:nth-child(even) { margin-bottom: 12px; }
    }
</style>
@endpush

@section('content')
    {{-- Gọi nội dung chính của trang từ file partial --}}
    @include('shipper.partials.order_details_content')

    {{-- Gọi modal từ file partial --}}
    @include('shipper.partials.failure_reason_modal')
@endsection

@push('page-scripts')
{{-- JavaScript chỉ dành cho trang này --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lấy các phần tử DOM cần thiết
        const failActionButton = document.getElementById('btn-fail-action');
        const modal = document.getElementById('failure-reason-modal');
        const closeButtons = document.querySelectorAll('.close-modal-btn');
        const confirmButton = document.getElementById('confirm-failure-btn');
        const otherReasonText = document.getElementById('other-reason-text');
        const reasonOptions = document.querySelectorAll('input[name="failure_reason_option"]');
        const failForm = document.getElementById('fail-delivery-form');
        const reasonInput = document.getElementById('fail-reason-input');

        if (failActionButton) {
            failActionButton.addEventListener('click', () => modal.style.display = 'flex');
        }
        closeButtons.forEach(button => {
            button.addEventListener('click', () => modal.style.display = 'none');
        });
        reasonOptions.forEach(radio => {
            radio.addEventListener('change', () => {
                otherReasonText.style.display = (radio.value === 'other') ? 'block' : 'none';
                if(radio.value === 'other') {
                    otherReasonText.focus();
                }
            });
        });
        if (confirmButton) {
            confirmButton.addEventListener('click', function() {
                const selectedOption = document.querySelector('input[name="failure_reason_option"]:checked');
                if (!selectedOption) {
                    alert('Vui lòng chọn một lý do.'); return;
                }
                let finalReason = selectedOption.value;
                if (finalReason === 'other') {
                    finalReason = otherReasonText.value.trim();
                    if (finalReason === '') {
                        alert('Vui lòng nhập lý do cụ thể.'); return;
                    }
                }
                reasonInput.value = finalReason;
                failForm.submit();
            });
        }
    });
</script>
@endpush
