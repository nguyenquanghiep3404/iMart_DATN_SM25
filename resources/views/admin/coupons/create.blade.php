@extends('admin.layouts.app')

@section('title', 'Tạo mã giảm giá')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Tạo mã giảm giá mới</h2>
        <a href="{{ route('admin.coupons.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg flex items-center transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Quay lại danh sách
        </a>
    </div>



    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('admin.coupons.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-5">
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Mã giảm giá <span class="text-red-500">*</span></label>
                        <input type="text" id="code" name="code" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('code') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                            value="{{ old('code') }}" 
                            placeholder="Nhập mã giảm giá (VD: SUMMER30)">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">Mã duy nhất không trùng lặp. Chỉ nên dùng chữ và số.</p>
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
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
                        <input type="datetime-local" id="start_date" name="start_date" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                            value="{{ old('start_date') }}">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">Để trống nếu mã giảm giá có hiệu lực ngay lập tức.</p>
                        @enderror
                    </div>
                    
                    <div class="mb-5">
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                        <input type="datetime-local" id="end_date" name="end_date" 
                            class="block w-full rounded-md border py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-300 @enderror" 
                            value="{{ old('end_date') }}">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">Để trống nếu mã giảm giá không hết hạn.</p>
                        @enderror
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
                        <label class="flex items-center">
                            <input type="checkbox" name="is_public" value="1" 
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 @error('is_public') border-red-300 focus:ring-red-500 @enderror" 
                                {{ old('is_public', '1') == '1' ? 'checked' : '' }}>
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
        
        // Cập nhật ban đầu
        updateDiscountType();
        
        // Cập nhật khi thay đổi
        typeSelect.addEventListener('change', updateDiscountType);
    });
</script>
@endpush
@endsection
