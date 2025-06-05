@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa danh mục')

@section('content')
<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <p class="text-lg text-gray-600">Cập nhật thông tin cho danh mục <span class="font-semibold text-indigo-600">{{ $category->name }}</span></p>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Progress Bar -->
            <div class="w-full h-2 bg-gray-100">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 w-2/3"></div>
            </div>

            <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Tên danh mục -->
                        <div class="group transform transition-all duration-300 ease-in-out hover:translate-x-2">
                            <label for="name" class="block text-base font-semibold text-gray-700 mb-2 flex items-center group-hover:text-indigo-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                Tên danh mục <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" 
                                class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm transition duration-200 ease-in-out focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:bg-white"
                                required>
                            @error('name')
                                <p class="mt-2 text-base text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div class="group transform transition-all duration-300 ease-in-out hover:translate-x-2">
                            <label for="slug" class="block text-base font-semibold text-gray-700 mb-2 flex items-center group-hover:text-indigo-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Slug
                            </label>
                            <div class="mt-1 relative rounded-lg shadow-sm">
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}" 
                                    class="block w-full rounded-lg border-gray-300 bg-gray-50 pr-10 transition duration-200 ease-in-out focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:bg-white"
                                    placeholder="Để trống để tự động tạo từ tên">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </div>
                            </div>
                            @error('slug')
                                <p class="mt-2 text-base text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Danh mục cha -->
                        <div class="group transform transition-all duration-300 ease-in-out hover:translate-x-2">
                            <label for="parent_id" class="block text-base font-semibold text-gray-700 mb-2 flex items-center group-hover:text-indigo-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Danh mục cha
                            </label>
                            <select name="parent_id" id="parent_id" 
                                class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 transition duration-200 ease-in-out focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:bg-white">
                                <option value="">Không có danh mục cha</option>
                                @foreach($parents as $id => $name)
                                    <option value="{{ $id }}" {{ old('parent_id', $category->parent_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <p class="mt-2 text-base text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Mô tả -->
                        <div class="group transform transition-all duration-300 ease-in-out hover:translate-x-2">
                            <label for="description" class="block text-base font-semibold text-gray-700 mb-2 flex items-center group-hover:text-indigo-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                Mô tả <span class="text-red-500 ml-1">*</span>
                            </label>
                            <textarea name="description" id="description" rows="4" 
                                class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 transition duration-200 ease-in-out focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:bg-white"
                                required>{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <p class="mt-2 text-base text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Thứ tự và Trạng thái -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="group transform transition-all duration-300 ease-in-out hover:translate-x-2">
                                <label for="order" class="block text-base font-semibold text-gray-700 mb-2 flex items-center group-hover:text-indigo-600 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                                    </svg>
                                    Thứ tự hiển thị
                                </label>
                                <input type="number" name="order" id="order" min="0" value="{{ old('order', $category->order) }}" 
                                    class="mt-1 block w-full h-10 rounded-lg border-gray-300 bg-gray-50 transition duration-200 ease-in-out focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:bg-white">
                            </div>

                            <div class="group transform transition-all duration-300 ease-in-out hover:translate-x-2">
                                <label for="status" class="block text-base font-semibold text-gray-700 mb-2 flex items-center group-hover:text-indigo-600 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Trạng thái <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="status" id="status" 
                                    class="mt-1 block w-full h-10 rounded-lg border-gray-300 bg-gray-50 transition duration-200 ease-in-out focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:bg-white"
                                    required>
                                    <option value="active" {{ old('status', $category->status) == 'active' ? 'selected' : '' }}>
                                        Hoạt động
                                    </option>
                                    <option value="inactive" {{ old('status', $category->status) == 'inactive' ? 'selected' : '' }}>
                                        Không hoạt động
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Thông tin SEO
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- SEO Left Column -->
                        <div class="space-y-6">
                            <!-- Meta Title -->
                            <div class="group transform transition-all duration-300 ease-in-out hover:translate-x-2">
                                <label for="meta_title" class="block text-base font-semibold text-gray-700 mb-2 flex items-center group-hover:text-indigo-600 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Tiêu đề SEO
                                </label>
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $category->meta_title) }}" 
                                    class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 transition duration-200 ease-in-out focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:bg-white">
                            </div>

                            <!-- Meta Keywords -->
                            <div class="group transform transition-all duration-300 ease-in-out hover:translate-x-2">
                                <label for="meta_keywords" class="block text-base font-semibold text-gray-700 mb-2 flex items-center group-hover:text-indigo-600 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Từ khóa SEO
                                </label>
                                <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $category->meta_keywords) }}" 
                                    class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 transition duration-200 ease-in-out focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:bg-white"
                                    placeholder="Các từ khóa cách nhau bởi dấu phẩy">
                            </div>
                        </div>

                        <!-- SEO Right Column -->
                        <div class="space-y-6">
                            <!-- Meta Description -->
                            <div class="group transform transition-all duration-300 ease-in-out hover:translate-x-2">
                                <label for="meta_description" class="block text-base font-semibold text-gray-700 mb-2 flex items-center group-hover:text-indigo-600 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                    </svg>
                                    Mô tả SEO
                                </label>
                                <textarea name="meta_description" id="meta_description" rows="4" 
                                    class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 transition duration-200 ease-in-out focus:ring-2 focus:ring-indigo-500 focus:border-transparent hover:bg-white">{{ old('meta_description', $category->meta_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 pt-8 border-t border-gray-200 flex items-center justify-end space-x-5">
                    <button class="tjp-btn bg-red-600 text-white px-4 py-2 rounded">
                        Cập nhật
                    </button>
                    <button type="reset" 
                        class="inline-block px-7 py-2 border border-yellow-500 text-yellow-600 rounded hover:bg-yellow-50 font-semibold shadow">
                        Reset
                    </button>
                    <a href="{{ route('admin.categories.index') }}" 
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tự động tạo slug từ tên
    document.getElementById('name').addEventListener('input', function() {
        if (!document.getElementById('slug').value) {
            let slug = this.value
                .toLowerCase()
                .replace(/đ/g, 'd')
                .replace(/[^a-z0-9-]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            document.getElementById('slug').value = slug;
        }
    });
</script>
@endpush
@endsection
2 