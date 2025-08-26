@extends('admin.layouts.app')

@section('title', 'Xử lý Yêu cầu Hủy cho đơn #' . $cancellationRequest->order->order_code)

@section('content')
<style>
    body { font-family: 'Inter', sans-serif; }
    .modal-backdrop { transition: opacity 0.3s ease; }
    .modal-content { transition: transform 0.3s ease, opacity 0.3s ease; }
</style>

<div class="w-full max-w-4xl mx-auto bg-white rounded-2xl shadow-lg my-10">
    <div class="p-6 md:p-8 border-b border-gray-200">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Xử lý Yêu cầu Hủy Đơn hàng</h1>
        <p class="mt-1 text-gray-500">
            Mã đơn hàng: <a href="{{ route('admin.orders.view', $cancellationRequest->order->id) }}" class="font-semibold text-blue-600 hover:underline">#{{ $cancellationRequest->order->order_code }}</a>
        </p>
    </div>

    <div class="p-6 md:p-8 space-y-6">
        <div class="border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-800 mb-3 text-md">Thông tin Yêu cầu từ Khách hàng</h3>
            <div class="space-y-2 text-sm text-gray-600">
               <div class="flex justify-between"><span>Khách hàng:</span> <span class="font-medium text-gray-900">{{ $cancellationRequest->user->name }}</span></div>
               <div class="flex justify-between"><span>Ngày yêu cầu:</span> <span class="font-medium text-gray-900">{{ $cancellationRequest->created_at->format('d/m/Y H:i') }}</span></div>
               <div class="flex justify-between"><span>Lý do hủy:</span> <span class="font-medium text-gray-900">{{ $cancellationRequest->reason }}</span></div>
            </div>
        </div>

        <div class="border border-gray-200 rounded-lg p-4">
             <h3 class="font-semibold text-gray-800 mb-3 text-md">Thông tin Tài khoản Nhận tiền</h3>
             <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between"><span>Ngân hàng:</span> <span class="font-medium text-gray-900">{{ $cancellationRequest->bank_name }}</span></div>
                <div class="flex justify-between"><span>Số tài khoản:</span> <span class="font-medium text-gray-900">{{ $cancellationRequest->bank_account_number }}</span></div>
                <div class="flex justify-between"><span>Chủ tài khoản:</span> <span class="font-medium text-gray-900">{{ $cancellationRequest->bank_account_name }}</span></div>
             </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-800 mb-2">Chi tiết Hoàn tiền</h3>
            <div class="flex justify-between items-center text-gray-700">
                <span>Số tiền hoàn trả:</span>
                <span class="font-bold text-lg text-green-600">{{ number_format($cancellationRequest->refund_amount, 0, ',', '.') }} VNĐ</span>
            </div>
            <div class="flex justify-between items-center text-gray-700 mt-1">
                <span>Phương thức thanh toán gốc:</span>
                <span class="font-semibold">{{ $cancellationRequest->order->payment_method }}</span>
            </div>
        </div>

        <div class="mt-6 border-t pt-6 flex flex-col-reverse sm:flex-row justify-end gap-3">
             <button type="button" onclick="history.back()" class="px-6 py-2.5 bg-white border border-gray-300 rounded-md">Quay lại</button>
            <form action="{{ route('admin.orders.cancellation.approve', $cancellationRequest->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn DUYỆT yêu cầu này? Đơn hàng sẽ bị hủy và tồn kho được cập nhật.')">
    @csrf
    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold transition-colors shadow-sm">
        Đồng ý Hủy và Hoàn tiền
    </button>
</form>
        </div>
    </div>
</div>

<!-- <div id="reject-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div id="reject-modal-backdrop" class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl z-10">
        <form action="{{ route('admin.orders.cancellation.reject', $cancellationRequest->id) }}" method="POST">
            @csrf
            <div class="p-6 border-b"><h2 class="text-xl font-bold text-gray-800">Từ chối Yêu cầu Hủy</h2></div>
            <div class="p-6">
                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do từ chối <span class="text-red-500">*</span></label>
                <textarea id="rejection_reason" name="rejection_reason" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md" required></textarea>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                <button type="button" id="close-reject-modal-button" class="px-6 py-2.5 bg-white border border-gray-300 rounded-md">Hủy</button>
                <button type="submit" class="px-6 py-2.5 bg-red-600 text-white rounded-md hover:bg-red-700">Xác nhận Từ chối</button>
            </div>
        </form>
    </div>
</div> -->

<script>
    const rejectModal = document.getElementById('reject-modal');
    const openRejectBtn = document.getElementById('open-reject-modal-button');
    const closeRejectBtn = document.getElementById('close-reject-modal-button');
    const rejectBackdrop = document.getElementById('reject-modal-backdrop');

    openRejectBtn.addEventListener('click', () => rejectModal.classList.remove('hidden'));
    closeRejectBtn.addEventListener('click', () => rejectModal.classList.add('hidden'));
    rejectBackdrop.addEventListener('click', () => rejectModal.classList.add('hidden'));
</script>
@endsection
