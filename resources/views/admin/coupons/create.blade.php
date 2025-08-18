@extends('admin.layouts.app')

@section('title', 'Tạo mã giảm giá')

@section('content')
<div class="p-6">
        <div class="card-custom mb-6">
            <div class="card-custom-header">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Thêm mã giảm giá</h1>
                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="flex text-sm text-gray-500">
                                <li><a href="{{ route('admin.coupons.index') }}" class="text-indigo-600 hover:text-indigo-800">Danh sách Mã giảm giá</a></li>
                                <li class="text-gray-400 mx-2">/</li>
                                <li class="text-gray-700 font-medium" aria-current="page">Thêm mã giảm giá</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại Danh sách
                    </a>
                </div>
            </div>
        </div>



    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('admin.coupons.store') }}" method="POST" novalidate>
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-5">
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Mã giảm giá <span class="text-red-500">*</span></label>
                        <div class="flex rounded-md shadow-sm">
                            <input type="text" id="code" name="code" 
                                class="block w-full rounded-l-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('code') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                value="{{ old('code') }}" 
                                placeholder="Nhập mã giảm giá (VD: SUMMER2024)"
                                minlength="6"
                                maxlength="20">
                            <button type="button" id="random-code-btn" 
                                class="inline-flex items-center rounded-r-md border border-l-0 bg-gray-50 px-3 py-2.5 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('code') border-red-300 @else border-gray-300 @enderror transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Random
                            </button>
                        </div>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">
                                Mã duy nhất không trùng lặp. <strong>Tối thiểu 6 ký tự, tối đa 20 ký tự.</strong>
                            </p>
                        @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea id="description" name="description" rows="3" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                            placeholder="Nhập mô tả về chương trình giảm giá này">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">Mô tả ngắn về chương trình khuyến mãi.</p>
                        @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                        <div class="mb-5">
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Loại giảm giá <span class="text-red-500">*</span></label>
                            <select id="type" name="type" 
                                class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('type') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror">
                                <option value="">Chọn loại giảm giá</option>
                                <option value="fixed_amount" {{ old('type') == 'fixed_amount' ? 'selected' : '' }}>Số tiền cố định</option>
                                <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Phần trăm</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500">Giảm theo số tiền hoặc phần trăm.</p>
                            @enderror
                        </div>
                        
                        <div class="mb-5">
                            <label for="value" class="block text-sm font-medium text-gray-700 mb-1">Giá trị giảm <span class="text-red-500">*</span></label>
                            <div class="flex rounded-md">
                                <input type="number" step="0.01" min="0" id="value" name="value" 
                                    class="block w-full rounded-l-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('value') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                    value="{{ old('value') }}">
                                <span class="inline-flex items-center rounded-r-md border border-l-0 bg-gray-50 px-3 text-gray-500 @error('value') border-red-300 @else border-gray-300 @enderror" id="value-addon">VND</span>
                            </div>
                            @error('value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500" id="value-help">Số tiền giảm (VND).</p>
                            @enderror
                        </div>

                        <!-- Số tiền giảm tối đa (chỉ hiển thị khi type = percentage) -->
                        <div class="mb-5 hidden" id="max-discount-amount-field">
                            <label for="max_discount_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                Số tiền giảm tối đa
                                <span class="text-blue-600 text-xs">(tuỳ chọn)</span>
                            </label>
                            <div class="flex rounded-md">
                                <input type="number" step="1000" min="1000" id="max_discount_amount" name="max_discount_amount" 
                                    class="block w-full rounded-l-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_discount_amount') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                    value="{{ old('max_discount_amount') }}"
                                    placeholder="Ví dụ: 100000">
                                <span class="inline-flex items-center rounded-r-md border border-l-0 bg-gray-50 px-3 text-gray-500 @error('max_discount_amount') border-red-300 @else border-gray-300 @enderror">VND</span>
                            </div>
                            @error('max_discount_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500">
                                    <span class="text-blue-600">Ví dụ:</span> Mã giảm 20% nhưng tối đa chỉ 100.000 VND.
                                </p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                        <div class="mb-5">
                            <label for="max_uses" class="block text-sm font-medium text-gray-700 mb-1">Số lần sử dụng tối đa</label>
                            <input type="number" min="1" id="max_uses" name="max_uses" 
                                class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_uses') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                value="{{ old('max_uses') }}">
                            @error('max_uses')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500">Để trống nếu không giới hạn.</p>
                            @enderror
                        </div>
                        
                        <div class="mb-5">
                            <label for="max_uses_per_user" class="block text-sm font-medium text-gray-700 mb-1">Số lần sử dụng tối đa/người</label>
                            <input type="number" min="1" id="max_uses_per_user" name="max_uses_per_user" 
                                class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_uses_per_user') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                value="{{ old('max_uses_per_user') }}">
                            @error('max_uses_per_user')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500">Để trống nếu không giới hạn.</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="mb-5">
                        <label for="min_order_amount" class="block text-sm font-medium text-gray-700 mb-1">Giá trị đơn hàng tối thiểu</label>
                        <div class="flex rounded-md">
                            <input type="number" min="0" id="min_order_amount" name="min_order_amount" 
                                class="block w-full rounded-l-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('min_order_amount') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                value="{{ old('min_order_amount') }}">
                            <span class="inline-flex items-center rounded-r-md border border-l-0 bg-gray-50 px-3 text-gray-500 @error('min_order_amount') border-red-300 @else border-gray-300 @enderror">VND</span>
                        </div>
                        @error('min_order_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">Giá trị đơn hàng tối thiểu để áp dụng mã giảm giá.</p>
                        @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Ngày bắt đầu 
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="start_date" name="start_date" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                            value="{{ old('start_date') }}"
                            min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}"
                            step="60"
                            >
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">
                                <span class="text-blue-600">Lưu ý:</span> Ngày bắt đầu là bắt buộc và không được là quá khứ.
                            </p>
                        @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Ngày kết thúc 
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="end_date" name="end_date" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                            value="{{ old('end_date') }}"
                            min="{{ \Carbon\Carbon::now()->addMinute()->format('Y-m-d\TH:i') }}"
                            step="60"
                            >
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">
                                <span class="text-blue-600">Lưu ý:</span> Ngày kết thúc là bắt buộc, phải sau thời điểm hiện tại.
                            </p>
                        @enderror
                        <div id="date-validation-message" class="mt-1 text-sm text-amber-600 hidden"></div>
                    </div>
                    
                    <div class="mb-5">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái <span class="text-red-500">*</span></label>
                        <select id="status" name="status" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror">
                            <option value="">Chọn trạng thái</option>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Vô hiệu</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="mb-5">
                        <input type="hidden" name="is_public" value="0">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_public" value="1" 
                                class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 @error('is_public') border-red-300 focus:ring-red-500 @enderror" 
                                {{ old('is_public') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Mã giảm giá công khai</span>
                        </label>
                        @error('is_public')
                            <p class="mt-1 ml-6 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 ml-6 text-sm text-gray-500">Nếu không chọn, mã giảm giá này chỉ dành cho người dùng được chọn.</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 border-t border-gray-200 pt-5 mt-5">
                <a href="{{ route('admin.coupons.index') }}" class="rounded-md border border-gray-300 bg-white py-2.5 px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Huỷ
                </a>
                <button type="submit" class="rounded-md border border-transparent bg-indigo-600 py-2.5 px-5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Tạo mã giảm giá
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const valueAddon = document.getElementById('value-addon');
        const valueHelp = document.getElementById('value-help');
        const valueInput = document.getElementById('value');
        const maxDiscountAmountField = document.getElementById('max-discount-amount-field');
        const maxDiscountAmountInput = document.getElementById('max_discount_amount');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const dateValidationMessage = document.getElementById('date-validation-message');
        const codeInput = document.getElementById('code');
        const randomCodeBtn = document.getElementById('random-code-btn');
        
        // Hàm tạo mã giảm giá ngẫu nhiên
        function generateRandomCode() {
            const prefixes = ['SALE', 'DEAL', 'SAVE', 'OFF', 'DISC', 'PROMO', 'MEGA', 'SUPER', 'VIP', 'HOT'];
            const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const numbers = '0123456789';
            
            // Chọn prefix ngẫu nhiên
            const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
            
            // Tạo số ngẫu nhiên (2-3 chữ số)
            const numberLength = Math.random() > 0.5 ? 2 : 3;
            let numberPart = '';
            for (let i = 0; i < numberLength; i++) {
                numberPart += numbers.charAt(Math.floor(Math.random() * numbers.length));
            }
            
            // Tạo ký tự ngẫu nhiên nếu cần (để đảm bảo >= 6 ký tự)
            let randomCode = prefix + numberPart;
            while (randomCode.length < 6) {
                const useNumber = Math.random() > 0.7;
                if (useNumber) {
                    randomCode += numbers.charAt(Math.floor(Math.random() * numbers.length));
                } else {
                    randomCode += letters.charAt(Math.floor(Math.random() * letters.length));
                }
            }
            
            // Đảm bảo không quá 20 ký tự
            if (randomCode.length > 20) {
                randomCode = randomCode.substring(0, 20);
            }
            
            return randomCode;
        }
        
        // Event listener cho button random
        randomCodeBtn.addEventListener('click', function() {
            const newCode = generateRandomCode();
            codeInput.value = newCode;
            
            // Hiệu ứng visual
            codeInput.classList.add('bg-green-50', 'border-green-300');
            setTimeout(() => {
                codeInput.classList.remove('bg-green-50', 'border-green-300');
            }, 1000);
            
            // Trigger validation nếu có
            codeInput.dispatchEvent(new Event('input'));
        });
        
        // Validation real-time cho code
        codeInput.addEventListener('input', function() {
            const value = this.value;
            const isValid = value.length >= 6 && value.length <= 20;
            
            // Reset classes
            this.classList.remove('border-red-300', 'border-green-300', 'focus:border-red-500', 'focus:border-green-500', 'focus:ring-red-500', 'focus:ring-green-500');
            
            if (value.length > 0) {
                if (isValid) {
                    this.classList.add('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
                } else {
                    this.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
                }
            } else {
                this.classList.add('border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500');
            }
        });
        
        // Hàm cập nhật loại giảm giá
        function updateDiscountType() {
            if (typeSelect.value === 'percentage') {
                valueAddon.textContent = '%';
                valueHelp.textContent = 'Phần trăm giảm giá (1-100).';
                valueInput.setAttribute('max', '100');
                // Hiển thị field số tiền giảm tối đa
                maxDiscountAmountField.classList.remove('hidden');
            } else {
                valueAddon.textContent = 'VND';
                valueHelp.textContent = 'Số tiền giảm (VND).';
                valueInput.removeAttribute('max');
                // Ẩn field số tiền giảm tối đa và clear value
                maxDiscountAmountField.classList.add('hidden');
                maxDiscountAmountInput.value = '';
            }
        }
        
        // Hàm validation ngày
        function validateDates() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            const now = new Date();
            
            // Clear previous messages
            dateValidationMessage.classList.add('hidden');
            dateValidationMessage.textContent = '';
            
            // Reset border colors
            startDateInput.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            endDateInput.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            startDateInput.classList.add('border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500');
            endDateInput.classList.add('border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500');
            
            // Validation logic
            if (!startDateInput.value) {
                showDateError(startDateInput, 'Ngày bắt đầu là bắt buộc.');
                return false;
            }
            
            if (!endDateInput.value) {
                showDateError(endDateInput, 'Ngày kết thúc là bắt buộc.');
                return false;
            }
            
            // Kiểm tra ngày bắt đầu không được là quá khứ
            if (startDate < now) {
                showDateError(startDateInput, 'Ngày bắt đầu không được là quá khứ.');
                return false;
            }
            
            // Kiểm tra ngày kết thúc phải sau thời điểm hiện tại
            if (endDate <= now) {
                showDateError(endDateInput, 'Ngày kết thúc phải sau thời điểm hiện tại.');
                return false;
            }
            
            // Kiểm tra ngày kết thúc phải sau ngày bắt đầu
            if (endDate <= startDate) {
                showDateError(endDateInput, 'Ngày kết thúc phải sau ngày bắt đầu.');
                return false;
            }
            
            return true;
        }
        
        // Hàm hiển thị lỗi ngày
        function showDateError(input, message) {
            input.classList.remove('border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500');
            input.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            dateValidationMessage.textContent = message;
            dateValidationMessage.classList.remove('hidden');
        }
        
        // Hàm cập nhật min attribute cho end_date dựa trên start_date
        function updateEndDateMin() {
            if (startDateInput.value) {
                const startDate = new Date(startDateInput.value);
                startDate.setMinutes(startDate.getMinutes() + 1); // Thêm 1 phút để khác với start_date
                const minEndDate = startDate.toISOString().slice(0, 16);
                endDateInput.setAttribute('min', minEndDate);
            } else {
                // Nếu không có start_date, min là thời điểm hiện tại + 1 phút
                const now = new Date();
                now.setMinutes(now.getMinutes() + 1);
                const minEndDate = now.toISOString().slice(0, 16);
                endDateInput.setAttribute('min', minEndDate);
            }
        }
        // Event listeners
        typeSelect.addEventListener('change', updateDiscountType);
        startDateInput.addEventListener('change', function() {
            updateEndDateMin();
            validateDates();
        });
        endDateInput.addEventListener('change', validateDates);
        
        // Validation khi submit form
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            if (!validateDates()) {
                e.preventDefault();
                // Scroll to error
                dateValidationMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
        
        // Khởi tạo
        updateDiscountType();
        updateEndDateMin();
        
        // Thêm tooltip cho các trường ngày
        const startDateLabel = document.querySelector('label[for="start_date"]');
        const endDateLabel = document.querySelector('label[for="end_date"]');
        
        startDateLabel.setAttribute('title', 'Ngày bắt đầu hiệu lực của mã giảm giá (BẮT BUỘC). Không được là quá khứ.');
        endDateLabel.setAttribute('title', 'Ngày hết hạn của mã giảm giá (BẮT BUỘC). Phải sau thời điểm hiện tại và khác với ngày bắt đầu.');
        
        // Tooltip cho random button
        randomCodeBtn.setAttribute('title', 'Tạo mã giảm giá ngẫu nhiên (6-20 ký tự)');
    });
</script>
@endpush
@endsection
