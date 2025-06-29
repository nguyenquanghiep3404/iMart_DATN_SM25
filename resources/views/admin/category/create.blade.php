@extends('admin.layouts.app')

@section('content')
<div class="body-content px-8 py-8 bg-gray-100">
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="flex justify-between mb-10 items-end">
                <div class="page-title">
                    <h3 class="text-3xl font-extrabold text-gray-900">Add Category</h3>
                    <ul class="text-base font-medium flex items-center space-x-3 text-gray-500 mt-2">
                        <li>
                            <a href="" class="text-blue-600 hover:text-blue-500 font-semibold">Home</a>
                        </li>
                        <li><span class="inline-block bg-gray-400 w-[4px] h-[4px] rounded-full"></span></li>
                        <li class="text-gray-700 font-semibold">Add Category</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12">
            <div class="bg-white rounded-lg shadow p-8">
                <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {{-- Upload Image --}}
                        {{-- <div class="col-span-1">
                            <label class="block text-base font-bold text-gray-700 mb-2">Upload Image</label>
                            <div class="flex flex-col items-center justify-center border border-dashed border-gray-400 rounded-lg p-6 bg-gray-50">
                                <img src="{{ asset('assets/img/icons/upload.png') }}" class="w-[100px] mb-2" alt="">
                                <span class="text-xs text-gray-500 mb-3">Image size must be less than 5MB</span>
                                <input type="file" name="image" id="productImage" class="hidden">
                                <label for="productImage" class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded text-sm">Upload Image</label>
                            </div>
                        </div> --}}

                        {{-- Form Fields --}}
                        <div class="col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-base font-bold text-gray-700 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                    Name
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Name" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('name')
                                    <p class="mt-1 text-base text-red-600 dark:text-red-500 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="font-medium">{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="slug" class="block text-base font-bold text-gray-700 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    Slug
                                </label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="Slug" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('slug')
                                    <span class="text-red-500 text-base mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div>
                                <label for="parent_id" class="block text-base font-bold text-gray-700 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Parent Category
                                </label>
                                <select name="parent_id" id="parent_id" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="">-- None (Parent) --</option>
                                    @foreach ($parents as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <span class="text-red-500 text-base mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div>
                                <label for="order" class="block text-base font-bold text-gray-700 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                                    </svg>
                                    Order
                                </label>
                                <input type="number" name="order" id="order" min="0" value="{{ old('order', 0) }}" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('order')
                                    <span class="text-red-500 text-base mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-base font-bold text-gray-700 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Status
                                </label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="" selected disabled>-- Select Status --</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <span class="mt-1 text-base text-red-600 dark:text-red-500 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <label for="description" class="block text-base font-bold text-gray-700 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                    </svg>
                                    Description
                                </label>
                                <textarea name="description" id="description" rows="3" placeholder="Description here" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="text-red-500 text-base mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                            <h3 class="text-lg font-bold text-red-500 mb-6 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                SEO
                            </h3>

                            <div class="col-span-2">
                                <label for="meta_title" class="block text-base font-bold text-gray-700 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Meta Title
                                </label>
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" placeholder="Enter Meta Title" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('meta_title')
                                    <span class="text-red-500 text-base mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <label for="meta_description" class="block text-base font-bold text-gray-700 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                    </svg>
                                    Meta Description
                                </label>
                                <textarea name="meta_description" id="meta_description" rows="2" placeholder="Enter Meta Description" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none">{{ old('meta_description') }}</textarea>
                                @error('meta_description')
                                    <span class="text-red-500 text-base mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <label for="meta_keywords" class="block text-base font-bold text-gray-700 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Meta Keywords
                                </label>
                                <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="Enter Meta Keywords" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('meta_keywords')
                                    <span class="text-red-500 text-base mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="mt-8 text-right space-x-4">
                        <button type="submit" class="tp-btn px-7 py-2">Add Category</button>

                        <button type="reset" 
                            class="space-x-6 inline-block px-7 py-2 border border-yellow-500 text-yellow-600 rounded hover:bg-yellow-50 font-semibold shadow">
                            Reset
                        </button>

                        <a href="{{ route('admin.categories.index') }}" 
                            class="space-x-6 inline-block px-7 py-2 border border-red-500 text-red-500 rounded hover:bg-red-50 font-semibold shadow">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Hàm chuyển đổi chuỗi thành slug
    function stringToSlug(str) {
        // Chuyển về lowercase
        str = str.toLowerCase();
        
        // Chuyển đổi các ký tự có dấu thành không dấu
        str = str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        
        // Thay thế các ký tự đặc biệt và khoảng trắng thành dấu gạch ngang
        str = str.replace(/[^a-z0-9\s-]/g, '')
                 .replace(/\s+/g, '-')
                 .replace(/-+/g, '-')
                 .replace(/^-+|-+$/g, '');
                 
        return str;
    }

    // Lắng nghe sự kiện khi người dùng nhập vào trường name
    document.getElementById('name').addEventListener('input', function() {
        // Lấy giá trị từ trường name
        let nameValue = this.value;
        
        // Chuyển đổi thành slug
        let slugValue = stringToSlug(nameValue);
        
        // Gán giá trị slug vào trường slug
        document.getElementById('slug').value = slugValue;
    });
</script>
@endpush
@endsection
