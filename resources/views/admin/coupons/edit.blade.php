@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa mã giảm giá')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Chỉnh sửa mã giảm giá: <span class="text-indigo-600">{{ $coupon->code }}</span></h2>
        <a href="{{ route('admin.coupons.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg flex items-center transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Quay lại danh sách
        </a>
    </div>



    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-5">
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Mã giảm giá <span class="text-red-500">*</span></label>
                        <div class="flex rounded-md shadow-sm">
                            <input type="text" id="code" name="code" 
                                class="block w-full rounded-l-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('code') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                value="{{ old('code', $coupon->code) }}"
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
                                Mã duy nhất không trùng lặp. <strong>Tối thiểu 6 ký tự, tối đa 20 ký tự.</strong> Chỉ nên dùng chữ và số.
                                <button type="button" class="text-indigo-600 hover:text-indigo-500 underline ml-1" onclick="document.getElementById('random-code-btn').click()">
                                    Tạo ngẫu nhiên
                                </button>
                            </p>
                        @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea id="description" name="description" rows="3" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror">{{ old('description', $coupon->description) }}</textarea>
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
                                <option value="fixed_amount" {{ old('type', $coupon->type) == 'fixed_amount' ? 'selected' : '' }}>Số tiền cố định</option>
                                <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Phần trăm</option>
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
                                    value="{{ old('value', $coupon->value) }}">
                                <span class="inline-flex items-center rounded-r-md border border-l-0 bg-gray-50 px-3 text-gray-500 @error('value') border-red-300 @else border-gray-300 @enderror" id="value-addon">{{ $coupon->type == 'percentage' ? '%' : 'VND' }}</span>
                            </div>
                            @error('value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500" id="value-help">
                                    {{ $coupon->type == 'percentage' ? 'Phần trăm giảm giá (1-100).' : 'Số tiền giảm (VND).' }}
                                </p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                        <div class="mb-5">
                            <label for="max_uses" class="block text-sm font-medium text-gray-700 mb-1">Số lần sử dụng tối đa</label>
                            <input type="number" min="1" id="max_uses" name="max_uses" 
                                class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_uses') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                value="{{ old('max_uses', $coupon->max_uses) }}">
                            @error('max_uses')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500">
                                    Để trống nếu không giới hạn.
                                    <span class="text-indigo-600 font-medium">
                                        Đã sử dụng: {{ $coupon->usages->count() }} lần
                                    </span>
                                </p>
                            @enderror
                        </div>
                        
                        <div class="mb-5">
                            <label for="max_uses_per_user" class="block text-sm font-medium text-gray-700 mb-1">Số lần sử dụng tối đa/người</label>
                            <input type="number" min="1" id="max_uses_per_user" name="max_uses_per_user" 
                                class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('max_uses_per_user') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                value="{{ old('max_uses_per_user', $coupon->max_uses_per_user) }}">
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
                                value="{{ old('min_order_amount', $coupon->min_order_amount) }}">
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
                            <span class="text-gray-500 text-xs">(Không thể chỉnh sửa)</span>
                        </label>
                        <input type="datetime-local" id="start_date" name="start_date" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm bg-gray-100 text-gray-600 cursor-not-allowed @error('start_date') border-red-300 @else border-gray-300 @enderror" 
                            value="{{ old('start_date', $coupon->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}"
                            step="60"
                            disabled
                            readonly>
                        <!-- Hidden input để gửi giá trị -->
                        <input type="hidden" name="start_date" value="{{ $coupon->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '' }}">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">
                                <span class="text-amber-600"></span>Thông tin:</span> Ngày bắt đầu không thể chỉnh sửa để đảm bảo tính nhất quán của mã giảm giá.
                            </p>
                        @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Ngày kết thúc 
                            <span class="text-red-500">*</span>
                            @if($coupon->end_date && $coupon->end_date->isPast())
                                <span class="text-red-500 text-xs">(Đã hết hạn - Không thể chỉnh sửa)</span>
                            @endif
                        </label>
                        @if($coupon->end_date && $coupon->end_date->isPast())
                            <!-- Mã đã hết hạn - Disable input -->
                            <input type="datetime-local" id="end_date" name="end_date" 
                                class="block w-full rounded-md border py-2.5 px-3 shadow-sm bg-red-50 text-red-700 cursor-not-allowed border-red-300" 
                                value="{{ old('end_date', $coupon->end_date ? $coupon->end_date->format('Y-m-d\TH:i:s') : '') }}"
                                disabled
                                readonly>
                            <!-- Hidden input để giữ giá trị cũ -->
                            <input type="hidden" name="end_date" value="{{ $coupon->end_date ? $coupon->end_date->format('Y-m-d\TH:i:s') : '' }}">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-sm text-red-600">
                                    <span class="text-red-600">Thông báo:</span> Mã giảm giá này đã hết hạn vào ngày {{ $coupon->end_date->format('d/m/Y H:i') }}. 
                                    Không thể gia hạn hoặc chỉnh sửa ngày kết thúc.
                                </p>
                            @enderror
                        @else
                            <!-- Mã chưa hết hạn - Cho phép chỉnh sửa -->
                            <input type="datetime-local" id="end_date" name="end_date" 
                                class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                                value="{{ old('end_date', $coupon->end_date ? $coupon->end_date->format('Y-m-d\TH:i:s') : '') }}"
                                min="{{ $coupon->start_date ? $coupon->start_date->copy()->addMinute()->format('Y-m-d\TH:i') : '' }}">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500">
                                    <span class="text-blue-600">Lưu ý:</span> Ngày kết thúc phải sau ngày bắt đầu ({{ $coupon->start_date ? $coupon->start_date->format('d/m/Y H:i') : '' }}).
                                </p>
                            @enderror
                            <div id="end-date-validation-message" class="mt-1 text-sm text-amber-600 hidden"></div>
                        @endif
                    </div>
                    
                    <div class="mb-5">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái <span class="text-red-500">*</span></label>
                        <select id="status" name="status" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror">
                            <option value="">Chọn trạng thái</option>
                            <option value="active" {{ old('status', $coupon->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ old('status', $coupon->status) == 'inactive' ? 'selected' : '' }}>Vô hiệu</option>
                            <option value="expired" {{ old('status', $coupon->status) == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_public" value="1" 
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 @error('is_public') border-red-300 focus:ring-red-500 @enderror" 
                                {{ old('is_public', $coupon->is_public) ? 'checked' : '' }}>
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
                    Cập nhật
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
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const endDateMessage = document.getElementById('end-date-validation-message');
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
        
        function updateDiscountType() {
            if (typeSelect.value === 'percentage') {
                valueAddon.textContent = '%';
                valueHelp.textContent = 'Phần trăm giảm giá (1-100).';
                valueInput.setAttribute('max', '100');
            } else {
                valueAddon.textContent = 'VND';
                valueHelp.textContent = 'Số tiền giảm (VND).';
                valueInput.removeAttribute('max');
            }
        }
        
        // Hàm validation ngày kết thúc cho edit mode
        function validateEndDate() {
            // Kiểm tra xem end_date có bị disable không (mã đã hết hạn)
            if (endDateInput.disabled) {
                return true; // Không validation nếu đã hết hạn
            }
            
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            // Clear previous messages
            if (endDateMessage) {
                endDateMessage.classList.add('hidden');
                endDateMessage.textContent = '';
            }
            
            // Reset border colors
            endDateInput.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            endDateInput.classList.add('border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500');
            
            // Kiểm tra ngày kết thúc bắt buộc
            if (!endDateInput.value) {
                showEndDateError('Ngày kết thúc là bắt buộc.');
                return false;
            }
            
            // Kiểm tra ngày kết thúc phải sau ngày bắt đầu
            if (startDateInput.value && endDate <= startDate) {
                showEndDateError('Ngày kết thúc phải sau ngày bắt đầu.');
                return false;
            }
            
            return true;
        }
        
        // Hàm hiển thị lỗi cho end_date
        function showEndDateError(message) {
            endDateInput.classList.remove('border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500');
            endDateInput.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            if (endDateMessage) {
                endDateMessage.textContent = message;
                endDateMessage.classList.remove('hidden');
            }
        }
        
        // Event listeners - Chỉ add nếu end_date không bị disable
        if (!endDateInput.disabled) {
            endDateInput.addEventListener('change', validateEndDate);
            endDateInput.addEventListener('blur', validateEndDate);
        }
        
        // Validation khi submit form
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            // Chỉ validate nếu end_date không bị disable
            if (!endDateInput.disabled && !validateEndDate()) {
                e.preventDefault();
                // Scroll to error
                if (endDateMessage) {
                    endDateMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        // Cập nhật ban đầu
        updateDiscountType();
        
        // Cập nhật khi thay đổi type
        typeSelect.addEventListener('change', updateDiscountType);
        
        // Tooltip cho random button
        randomCodeBtn.setAttribute('title', 'Tạo mã giảm giá ngẫu nhiên (6-20 ký tự)');
        
        // Hiển thị thông tin ngày bắt đầu không thể chỉnh sửa
        console.log('Edit mode: Ngày bắt đầu đã bị khóa để đảm bảo tính nhất quán.');

        // Hiển thị thông tin về trạng thái mã giảm giá
        @if($coupon->end_date && $coupon->end_date->isPast())
            console.log('Edit mode: Mã giảm giá đã hết hạn - Không thể chỉnh sửa ngày kết thúc.');
            
            // Thêm badge warning vào form
            const form = document.querySelector('form');
            if (form) {
                const warningBadge = document.createElement('div');
                warningBadge.className = 'bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center';
                warningBadge.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <strong>Mã giảm giá đã hết hạn:</strong> Ngày kết thúc không thể được chỉnh sửa sau khi mã đã hết hạn.
                `;
                form.insertBefore(warningBadge, form.firstChild);
            }
        @else
            console.log('Edit mode: Mã giảm giá chưa hết hạn - Có thể chỉnh sửa ngày kết thúc.');
        @endif
    });
</script>
@endpush
@endsection
