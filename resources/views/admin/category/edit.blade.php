@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa danh mục')

@push('styles')
<style>
    /* Tối ưu hóa hiển thị cho grid nhóm thông số */
    .spec-group-grid {
        display: grid;
        /* Tự động điều chỉnh số cột dựa trên không gian có sẵn */
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }
    .spec-group-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border: 1px solid #e5e7eb; /* cool-gray-200 */
        border-radius: 0.5rem; /* rounded-lg */
        transition: all 0.2s ease-in-out;
        background-color: #fff;
        cursor: pointer;
    }
    .spec-group-item:hover {
        border-color: #4f46e5; /* indigo-600 */
        background-color: #f9fafb; /* cool-gray-50 */
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .spec-group-item input[type="checkbox"] {
        height: 1.25rem;
        width: 1.25rem;
        border-radius: 0.25rem;
        border-color: #d1d5db; /* cool-gray-300 */
        color: #4f46e5; /* indigo-600 */
        margin-right: 0.75rem;
        transition: all 0.2s ease-in-out;
    }
     .spec-group-item input[type="checkbox"]:focus {
        ring: 2px;
        ring-color: #4f46e5; /* indigo-500 */
        ring-offset: 2px;
     }
</style>
@endpush

@section('content')
<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Chỉnh sửa Danh mục</h1>
            <p class="text-lg text-gray-600 mt-1">Cập nhật thông tin cho danh mục <span class="font-semibold text-indigo-600">{{ $category->name }}</span></p>
        </div>

        <!-- Notification Area -->
        <div class="mb-6 space-y-4">
            <!-- Success Message -->
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">Thành công!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <!-- Error Message -->
            @if (session('error'))
                 <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">Đã xảy ra lỗi!</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">Vui lòng kiểm tra lại dữ liệu nhập vào:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="w-full h-2 bg-gray-100">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500"></div>
            </div>

            <!-- Main Update Form -->
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="p-6 sm:p-8">
                @csrf
                @method('PUT')

                <!-- Main Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Category Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Tên danh mục <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>

                        <!-- Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-semibold text-gray-700 mb-1">Slug (URL thân thiện)</label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Để trống để tự động tạo">
                        </div>

                        <!-- Parent Category -->
                        <div>
                            <label for="parent_id" class="block text-sm font-semibold text-gray-700 mb-1">Danh mục cha</label>
                            <select name="parent_id" id="parent_id" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Không có danh mục cha</option>
                                @foreach($parents as $id => $name)
                                    <option value="{{ $id }}" {{ old('parent_id', $category->parent_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Mô tả</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $category->description) }}</textarea>
                        </div>

                        <!-- Order and Status -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="order" class="block text-sm font-semibold text-gray-700 mb-1">Thứ tự</label>
                                <input type="number" name="order" id="order" min="0" value="{{ old('order', $category->order) }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Trạng thái <span class="text-red-500">*</span></label>
                                <select name="status" id="status" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="active" {{ old('status', $category->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="inactive" {{ old('status', $category->status) == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specification Groups Section -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Nhóm thông số kỹ thuật</h3>
                    <p class="text-sm text-gray-500">Chọn các nhóm thông số sẽ được áp dụng cho sản phẩm thuộc danh mục này.</p>

                    <!-- !!! FIX: Search functionality without a nested form -->
                    <div class="my-6">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                            </div>
                            <input type="text" id="spec_search_input" value="{{ $specSearch ?? '' }}" placeholder="Tìm kiếm nhóm thông số..." class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm pl-10 pr-32 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" id="spec_search_button" class="absolute inset-y-0 right-0 flex items-center px-4 bg-indigo-600 text-white font-semibold rounded-r-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Tìm kiếm</button>
                        </div>
                    </div>

                    <!-- Checkbox List (Inside main form) -->
                    <div class="spec-group-grid">
                        @forelse ($specificationGroups as $group)
                            <label for="spec_group_{{ $group->id }}" class="spec-group-item">
                                <input type="checkbox" name="specification_groups[]" value="{{ $group->id }}" id="spec_group_{{ $group->id }}" @if(in_array($group->id, old('specification_groups', $categorySpecificationGroupIds))) checked @endif>
                                <span class="font-medium text-gray-700">{{ $group->name }}</span>
                            </label>
                        @empty
                            <div class="col-span-full text-center py-8 px-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-500">Không tìm thấy nhóm thông số nào.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Tối ưu hóa công cụ tìm kiếm (SEO)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <label for="meta_title" class="block text-sm font-semibold text-gray-700 mb-1">Tiêu đề SEO</label>
                            <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $category->meta_title) }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="meta_keywords" class="block text-sm font-semibold text-gray-700 mb-1">Từ khóa SEO</label>
                            <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $category->meta_keywords) }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Các từ khóa cách nhau bởi dấu phẩy">
                        </div>
                        <div class="md:col-span-2">
                            <label for="meta_description" class="block text-sm font-semibold text-gray-700 mb-1">Mô tả SEO</label>
                            <textarea name="meta_description" id="meta_description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('meta_description', $category->meta_description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 pt-5 border-t border-gray-200 flex items-center justify-end space-x-4">
                    <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Hủy</a>
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Cập nhật danh mục</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Slug Generation ---
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    function toSlug(str) {
        str = str.toLowerCase();
        str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
        str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
        str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
        str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
        str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
        str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
        str = str.replace(/đ/g, "d");
        str = str.replace(/[^a-z0-9\s-]/g, '');
        str = str.replace(/\s+/g, '-').replace(/^-+|-+$/g, '');
        return str;
    }

    let userHasEditedSlug = slugInput.value.trim() !== '';

    slugInput.addEventListener('input', function() {
        userHasEditedSlug = true;
    });

    nameInput.addEventListener('input', function() {
        if (!userHasEditedSlug) {
            slugInput.value = toSlug(this.value);
        }
    });

    // --- !!! FIX: Specification Group Search ---
    const searchButton = document.getElementById('spec_search_button');
    const searchInput = document.getElementById('spec_search_input');

    const performSearch = () => {
        const baseUrl = "{{ route('admin.categories.edit', $category->id) }}";
        const searchTerm = searchInput.value;
        // Reload the page with the search query parameter
        window.location.href = `${baseUrl}?spec_search=${encodeURIComponent(searchTerm)}`;
    };

    // Handle search on button click
    searchButton.addEventListener('click', performSearch);

    // Handle search on pressing Enter key in the input field
    searchInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent the main form from submitting
            performSearch();
        }
    });
});
</script>
@endpush
