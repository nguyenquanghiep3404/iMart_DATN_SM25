@extends('admin.layouts.app')

@section('title', 'Thêm danh mục mới')

@push('styles')
<style>
    .spec-group-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }
    .spec-group-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
    }
    .spec-group-item:hover {
        border-color: #4f46e5;
        background-color: #f9fafb;
    }
    .spec-group-item input[type="checkbox"] {
        height: 1.25rem;
        width: 1.25rem;
        border-radius: 0.25rem;
        border-color: #d1d5db;
        color: #4f46e5;
        margin-right: 0.75rem;
        transition: all 0.2s ease-in-out;
    }
    .spec-group-item input[type="checkbox"]:focus {
        ring: 2px;
        ring-color: #4f46e5;
        ring-offset: 2px;
    }
</style>
@endpush

@section('content')
<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Thêm danh mục mới</h2>
            <p class="text-lg text-gray-600">Điền thông tin chi tiết cho danh mục sản phẩm của bạn.</p>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Progress Bar -->
            <div class="w-full h-2 bg-gray-100">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: 10%;"></div>
            </div>

            <form action="{{ route('admin.categories.store') }}" method="POST" class="p-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Tên danh mục -->
                        <div class="group">
                            <label for="name" class="block text-base font-semibold text-gray-700 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                Tên danh mục <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @error('name')
                                <p class="mt-2 text-base text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div class="group">
                            <label for="slug" class="block text-base font-semibold text-gray-700 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                Slug
                            </label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 pr-10" placeholder="Để trống để tự động tạo từ tên">
                            @error('slug')
                                <p class="mt-2 text-base text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Danh mục cha -->
                        <div class="group">
                            <label for="parent_id" class="block text-base font-semibold text-gray-700 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                Danh mục cha
                            </label>
                            <select name="parent_id" id="parent_id" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">Không có danh mục cha</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
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
                        <div class="group">
                            <label for="description" class="block text-base font-semibold text-gray-700 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                Mô tả
                            </label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('description') }}</textarea>
                        </div>

                        <!-- Thứ tự và Trạng thái -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="group">
                                <label for="order" class="block text-base font-semibold text-gray-700 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                                    Thứ tự hiển thị
                                </label>
                                <input type="number" name="order" id="order" min="0" value="{{ old('order', 0) }}" class="mt-1 block w-full h-10 rounded-lg border-gray-300 bg-gray-50">
                            </div>

                            <div class="group">
                                <label for="status" class="block text-base font-semibold text-gray-700 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Trạng thái <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="status" id="status" class="mt-1 block w-full h-10 rounded-lg border-gray-300 bg-gray-50">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specification Groups Section -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Nhóm thông số kỹ thuật
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">Chọn các nhóm thông số sẽ được áp dụng cho tất cả sản phẩm thuộc danh mục này.</p>
                    
                    <!-- Search for Specification Groups -->
                    <div class="mb-6">
                        <div class="relative">
                             <input type="text" id="spec-search-input" value="{{ $specSearch ?? '' }}" placeholder="Tìm kiếm nhóm thông số..." class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm pl-10 pr-24">
                             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <button type="button" id="spec-search-button" class="absolute inset-y-0 right-0 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-r-lg hover:bg-indigo-700">Tìm kiếm</button>
                        </div>
                    </div>

                    <div class="spec-group-grid">
                        @forelse ($specificationGroups as $group)
                            <label for="spec_group_{{ $group->id }}" class="spec-group-item cursor-pointer">
                                <input type="checkbox" name="specification_groups[]" value="{{ $group->id }}" id="spec_group_{{ $group->id }}"
                                       @if(is_array(old('specification_groups')) && in_array($group->id, old('specification_groups'))) checked @endif>
                                <span class="font-medium text-gray-700">{{ $group->name }}</span>
                            </label>
                        @empty
                            <p class="text-gray-500 col-span-full">
                                @if(!empty($specSearch))
                                    Không tìm thấy nhóm thông số nào với từ khóa "{{ $specSearch }}".
                                @else
                                    Chưa có nhóm thông số nào. Vui lòng <a href="{{ route('admin.specification-groups.create') }}" class="text-indigo-600 hover:underline">tạo nhóm mới</a>.
                                @endif
                            </p>
                        @endforelse
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Thông tin SEO
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <label for="meta_title" class="block text-base font-semibold text-gray-700 mb-2">Tiêu đề SEO</label>
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50">
                            </div>
                            <div>
                                <label for="meta_keywords" class="block text-base font-semibold text-gray-700 mb-2">Từ khóa SEO</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords') }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50" placeholder="Các từ khóa cách nhau bởi dấu phẩy">
                            </div>
                        </div>
                        <div>
                            <label for="meta_description" class="block text-base font-semibold text-gray-700 mb-2">Mô tả SEO</label>
                            <textarea name="meta_description" id="meta_description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50">{{ old('meta_description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 pt-8 border-t border-gray-200 flex items-center justify-end space-x-5">
                    <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">Hủy</a>
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Thêm danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tự động tạo slug từ tên
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function() {
            let nameValue = this.value;
            let slug = nameValue.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Bỏ dấu
                .replace(/đ/g, 'd')
                .replace(/[^a-z0-9\s-]/g, '') // Bỏ ký tự đặc biệt
                .trim()
                .replace(/\s+/g, '-') // Thay khoảng trắng bằng gạch ngang
                .replace(/-+/g, '-');
            slugInput.value = slug;
        });
    }

    // Xử lý tìm kiếm nhóm thông số
    const searchButton = document.getElementById('spec-search-button');
    const searchInput = document.getElementById('spec-search-input');

    if (searchButton && searchInput) {
        // Hàm thực hiện tìm kiếm
        const performSearch = () => {
            const query = searchInput.value;
            const url = new URL(window.location.href);
            url.searchParams.set('spec_search', query);
            window.location.href = url.toString();
        };

        // Tìm kiếm khi nhấn nút
        searchButton.addEventListener('click', performSearch);
        
        // Tìm kiếm khi nhấn Enter trong ô input
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Ngăn form chính submit
                performSearch();
            }
        });
    }
});
</script>
@endpush
