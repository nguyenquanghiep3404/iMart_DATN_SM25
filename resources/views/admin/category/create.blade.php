@extends('admin.layouts.app')

@section('content')
<div class="body-content px-8 py-8 bg-gray-100">
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="flex justify-between mb-10 items-end">
                <div class="page-title">
                    <h3 class="text-3xl font-extrabold text-gray-900">Add Category</h3>
                    <ul class="text-sm font-medium flex items-center space-x-3 text-gray-500 mt-2">
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
                            <label class="block text-sm font-bold text-gray-700 mb-2">Upload Image</label>
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
                                <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Name" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-500 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="font-medium">{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="slug" class="block text-sm font-bold text-gray-700 mb-1">Slug</label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="Slug" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('slug')
                                    <span class="text-red-500 text-sm mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div>
                                <label for="parent_id" class="block text-sm font-bold text-gray-700 mb-1">Parent Category</label>
                                <select name="parent_id" id="parent_id" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="">-- None (Parent) --</option>
                                    @foreach ($parents as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <span class="text-red-500 text-sm mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div>
                                <label for="order" class="block text-sm font-bold text-gray-700 mb-1">Order</label>
                                <input type="number" name="order" id="order" min="0" value="{{ old('order', 0) }}" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('order')
                                    <span class="text-red-500 text-sm mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="" selected disabled>-- Select Status --</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <span class="mt-1 text-sm text-red-600 dark:text-red-500 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <label for="description" class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                                <textarea name="description" id="description" rows="3" placeholder="Description here" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="text-red-500 text-sm mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <label for="meta_title" class="block text-sm font-bold text-gray-700 mb-1">Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" placeholder="Enter Meta Title" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('meta_title')
                                    <span class="text-red-500 text-sm mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <label for="meta_description" class="block text-sm font-bold text-gray-700 mb-1">Meta Description</label>
                                <textarea name="meta_description" id="meta_description" rows="2" placeholder="Enter Meta Description" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none">{{ old('meta_description') }}</textarea>
                                @error('meta_description')
                                    <span class="text-red-500 text-sm mt-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="col-span-2">
                                <label for="meta_keywords" class="block text-sm font-bold text-gray-700 mb-1">Meta Keywords</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="Enter Meta Keywords" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error('meta_keywords')
                                    <span class="text-red-500 text-sm mt-1 flex items-center">
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
                    <div class="mt-8 text-right">
                        <button type="submit" class="tp-btn px-7 py-2">Add Category</button>
                        <a href="{{ route('admin.categories.index') }}" 
                        class="ml-4 inline-block px-7 py-2 border border-red-500 text-red-500 rounded hover:bg-red-50 font-semibold shadow">
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
