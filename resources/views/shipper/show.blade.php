@extends('layouts.shipper')

@section('title', 'Chi tiết ĐH ' . $order->order_code)

@section('content')
    {{-- Gọi nội dung chính của trang từ file partial --}}
    @include('shipper.partials.order_details_content')
@endsection

@push('modals')
    {{-- Gọi modal từ file partial để nó nằm ngoài cấu trúc cuộn chính --}}
    @include('shipper.partials.failure_reason_modal')
@endpush

@push('scripts')
{{-- JavaScript sạch, chỉ dành cho trang này --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const failActionButton = document.getElementById('btn-fail-action');
        const modal = document.getElementById('failure-reason-modal');

        if (failActionButton && modal) {
            const closeButtons = modal.querySelectorAll('.close-modal-btn');
            const confirmButton = modal.querySelector('#confirm-failure-btn');
            const failForm = document.getElementById('failure-report-form');
            const reasonSelect = modal.querySelector('#failure-reason');
            const notesTextarea = modal.querySelector('#failure-notes');

            // Mở Modal
            failActionButton.addEventListener('click', () => {
                modal.classList.add('is-visible');
            });

            // Đóng Modal
            closeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    modal.classList.remove('is-visible');
                });
            });

            // Xử lý khi bấm nút "Xác nhận"
            if (confirmButton && failForm) {
                confirmButton.addEventListener('click', function() {
                    const reason = reasonSelect.value;
                    const notes = notesTextarea.value;

                    document.getElementById('fail-reason-input').value = reason;
                    document.getElementById('fail-notes-input').value = notes;

                    failForm.submit();
                });
            }
        }
    });
</script>
@endpush
