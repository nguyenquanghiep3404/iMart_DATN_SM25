@extends('admin.comments.layouts.main')

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <div class="bg-white shadow-md rounded-lg border border-gray-200">
        <div class="bg-yellow-100 px-6 py-5 border-b border-yellow-300">
            <h2 class="text-2xl font-bold text-yellow-800">✏️ Chỉnh sửa danh mục bài viết</h2>
        </div>

        <form action="{{ route('admin.categories_post.update', $categories_post->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-gray-700 font-semibold mb-2">📝 Tên danh mục</label>
                <input type="text" name="name" value="{{ old('name', $categories_post->name) }}"
                       class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring focus:ring-yellow-300">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">🔗 Slug (tùy chọn)</label>
                <input type="text" name="slug" value="{{ old('slug', $categories_post->slug) }}"
                       class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring focus:ring-yellow-300">
                @error('slug')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">📂 Danh mục cha</label>
                <select id="parent-category" name="parent_id"
                        class="w-full border border-gray-300 rounded-md px-4 py-2">
                    <option value="">-- Không có danh mục cha --</option>
                    @foreach ($allCategories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('parent_id', $categories_post->parent_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">🧾 Mô tả</label>
                <textarea name="description" rows="4"
                          class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring focus:ring-yellow-300">{{ old('description', $categories_post->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between items-center pt-4">
                <a href="{{ route('admin.categories_post.index') }}"
                   class="inline-block px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-md shadow transition">
                    ← Quay lại danh sách
                </a>

                <button type="submit"
                        class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-md shadow transition">
                    💾 Cập nhật danh mục
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        $('#parent-category').select2({
            placeholder: "🔍 Tìm danh mục cha...",
            allowClear: true,
            width: '100%',
            matcher: function (params, data) {
                if ($.trim(params.term) === '') return data;
                const term = params.term.toLowerCase();
                const text = data.text.toLowerCase();
                return text.includes(term) ? data : null;
            }
        });
    });
</script>

<style>
    .select2-container--default .select2-search--dropdown .select2-search__field {
        color: #000 !important;
        background-color: #fff !important;
        padding: 0.5rem;
        font-size: 1rem;
    }
</style>
@endsection
