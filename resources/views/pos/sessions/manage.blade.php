@extends('pos.layouts.pos')

@section('title', 'Quản lý Ca Làm Việc')

@push('styles')
<style>
    body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; }
    .main-card { background-color: white; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; padding-bottom: 0.75rem; }
    .info-label { color: #4b5563; }
    .info-value { color: #111827; font-weight: 600; }
    .difference-positive { color: #059669; } /* a green color */
    .difference-negative { color: #DC2626; } /* a red color */
</style>
@endpush

@section('content')
<div class="w-full max-w-2xl mx-auto p-4">
    <header class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-800">Quản lý Ca</h1>
        <p class="text-gray-600 mt-1">
            Máy POS: <span class="font-semibold">{{ $register->name }}</span> tại 
            <span class="font-semibold">{{ $register->storeLocation->name }}</span>
        </p>
    </header>

    @if ($posSession)
        {{-- GIAO DIỆN ĐÓNG CA --}}
        <div class="main-card">
            <div class="p-6 sm:p-8">
                <div class="text-center">
                    <h1 class="text-3xl font-extrabold text-gray-800">Đóng Ca Làm Việc</h1>
                    <p class="text-gray-500 mt-2">Tổng kết và đối soát doanh thu cuối ca.</p>
                </div>
                
                <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200 text-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-600">Bắt đầu lúc:</span>
                        <span class="font-bold text-gray-900">{{ $posSession->opened_at->format('H:i:s - d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-600">Nhân viên:</span>
                        <span class="font-bold text-gray-900">{{ $posSession->user->name }}</span>
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-gray-700 mb-2">Báo cáo doanh thu</h2>
                    <div class="border border-gray-200 rounded-lg p-4 space-y-1">
                        <div class="info-row">
                            <span class="info-label">Tiền mặt đầu ca</span>
                            <span class="info-value">{{ number_format($posSession->opening_balance, 0, ',', '.') }} VNĐ</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Doanh thu tiền mặt</span>
                            <span class="info-value">{{ number_format($revenueDetails['cash'], 0, ',', '.') }} VNĐ</span>
                        </div>
                         <div class="info-row font-bold text-blue-700 bg-blue-50 -mx-4 px-4 rounded">
                            <span class="info-label">Tổng tiền mặt dự kiến</span>
                            <span class="info-value" id="expected-cash-value">{{ number_format($posSession->opening_balance + $revenueDetails['cash'], 0, ',', '.') }} VNĐ</span>
                        </div>
                        <div class="info-row pt-3 border-t mt-3">
                            <span class="info-label">Doanh thu thẻ</span>
                            <span class="info-value">{{ number_format($revenueDetails['card'], 0, ',', '.') }} VNĐ</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Doanh thu chuyển khoản</span>
                            <span class="info-value">{{ number_format($revenueDetails['qr'], 0, ',', '.') }} VNĐ</span>
                        </div>
                        <div class="info-row text-xl font-extrabold border-t-2 border-gray-300 pt-4 mt-4">
                            <span class="info-label">TỔNG DOANH THU CA</span>
                            <span class="info-value text-green-600">{{ number_format($revenueDetails['total'], 0, ',', '.') }} VNĐ</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('pos.sessions.close', $posSession->id) }}" method="POST" class="mt-8">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="closing_balance" class="block text-lg font-semibold text-gray-700 mb-2">Số tiền mặt thực tế đếm được (VNĐ)</label>
                        <input type="text" id="closing_balance" name="closing_balance" class="w-full text-2xl text-center font-bold p-4 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="0" required>
                    </div>
                    <div class="mt-4 p-4 rounded-lg bg-gray-100 text-center">
                        <span class="text-gray-600 font-medium">Chênh lệch:</span>
                        <span id="difference-amount" class="text-xl font-bold ml-2">0 VNĐ</span>
                    </div>
                     <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mt-6 mb-2">Ghi chú cuối ca</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full p-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Thêm ghi chú nếu có chênh lệch hoặc vấn đề phát sinh..."></textarea>
                    </div>
                    <button type="submit" class="w-full mt-8 bg-red-600 text-white font-bold py-4 rounded-lg shadow-lg hover:bg-red-700 transition-transform transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-lg">
                        XÁC NHẬN VÀ ĐÓNG CA
                    </button>
                </form>
                 <div class="mt-4 text-center">
                     <a href="{{ route('pos.dashboard.index') }}" class="text-sm text-blue-600 hover:underline">Quay lại màn hình bán hàng</a>
                </div>
            </div>
        </div>
    @else
        {{-- GIAO DIỆN MỞ CA --}}
        <div class="main-card">
            <div class="p-6 sm:p-8">
                <div class="text-center">
                    <h1 class="text-3xl font-extrabold text-gray-800">Mở Ca Làm Việc</h1>
                    <p class="text-gray-500 mt-2">Bắt đầu phiên bán hàng mới của bạn.</p>
                </div>

                <form action="{{ route('pos.sessions.open') }}" method="POST" class="mt-8">
                    @csrf
                    <input type="hidden" name="register_id" value="{{ $register->id }}">
                    <div>
                        <label for="opening_balance" class="block text-lg font-semibold text-gray-700 mb-2">Số tiền mặt đầu ca (VNĐ)</label>
                        <input type="text" id="opening_balance" name="opening_balance" class="w-full text-2xl text-center font-bold p-4 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="0" required>
                    </div>
                    <button type="submit" class="w-full mt-8 bg-blue-600 text-white font-bold py-4 rounded-lg shadow-lg hover:bg-blue-700 transition-transform transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-lg">
                        BẮT ĐẦU CA LÀM VIỆC
                    </button>
                </form>
                 <div class="mt-4 text-center">
                     <a href="{{ route('pos.selection.index') }}" class="text-sm text-gray-500 hover:underline">Chọn lại cửa hàng/máy POS</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const openingBalanceInput = document.getElementById('opening_balance');
    const closingBalanceInput = document.getElementById('closing_balance');

    // Hàm định dạng số
    function formatInputAsCurrency(inputElement) {
        if (!inputElement) return;
        
        let value = inputElement.value.replace(/[^0-9]/g, '');
        if (value) {
            const numericValue = parseInt(value, 10);
            inputElement.value = new Intl.NumberFormat('vi-VN').format(numericValue);
        } else {
            inputElement.value = '';
        }
    }

    // Gắn sự kiện cho ô nhập tiền mặt đầu ca
    if(openingBalanceInput) {
        openingBalanceInput.addEventListener('input', () => formatInputAsCurrency(openingBalanceInput));
    }
    
    // Xử lý cho màn hình đóng ca
    if (closingBalanceInput) {
        const differenceAmountEl = document.getElementById('difference-amount');
        @if(isset($posSession) && isset($revenueDetails['cash']))
        const expectedCash = {{ $posSession->opening_balance + $revenueDetails['cash'] }};
        @else
        const expectedCash = 0;
        @endif

        const updateDifference = () => {
            const closingBalance = parseFloat(closingBalanceInput.value.replace(/[^0-9,]/g, '').replace(',', '.')) || 0;
            const difference = closingBalance - expectedCash;
            
            differenceAmountEl.textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(difference);
            
            differenceAmountEl.classList.remove('difference-positive', 'difference-negative', 'text-gray-800');
            if (difference > 0) {
                differenceAmountEl.classList.add('difference-positive');
            } else if (difference < 0) {
                differenceAmountEl.classList.add('difference-negative');
            } else {
                 differenceAmountEl.classList.add('text-gray-800');
            }
        };
        
        closingBalanceInput.addEventListener('input', () => {
            formatInputAsCurrency(closingBalanceInput);
            updateDifference();
        });
        
        // Cập nhật chênh lệch lần đầu khi tải trang
        updateDifference();
    }
});
</script>
@endpush