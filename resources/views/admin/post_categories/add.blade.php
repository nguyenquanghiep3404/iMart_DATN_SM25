@extends('admin.comments.layouts.main') {{-- Hoặc layout bạn đang dùng --}}

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-6">Thêm Danh Mục Bài Viết</h2>

    <form action="{{ route('admin.categories_post.store') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Chọn danh mục cha --}}
        <div>
            <label for="parent_id" class="block text-sm font-medium text-gray-700">Chọn danh mục cha (nếu có)</label>
            <select name="parent_id" id="parent_id"
                    class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
                <option value="">-- Không chọn (Tạo danh mục cha mới) --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tên danh mục (chỉ hiện khi không chọn danh mục cha) --}}
        <div id="name-field">
            <label for="name" class="block text-sm font-medium text-gray-700">Tên danh mục</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                   class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300"
                   @if(old('parent_id')) disabled @endif
                   @if(!old('parent_id')) required @endif>
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700">Slug (tùy chọn)</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                   class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
            @error('slug')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Mô tả --}}
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Mô tả</label>
            <textarea name="description" id="description" rows="3"
                      class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Danh mục con (luôn hiển thị) --}}
        <div id="child-category-section" class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Danh mục con (nếu có)</label>
            <div id="child-category-wrapper" class="space-y-2">
                @php
                    $oldChildren = old('children', ['']);
                @endphp

                @foreach ($oldChildren as $childName)
                    <input type="text" name="children[]" 
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300"
                           placeholder="Tên danh mục con" value="{{ $childName }}">
                @endforeach
            </div>
            <button type="button" id="add-child-category"
                    class="mt-2 inline-block px-3 py-1 bg-gray-200 hover:bg-gray-300 text-sm rounded">
                + Thêm danh mục con
            </button>
        </div>

        <div>
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">
                Lưu danh mục
            </button>
        </div>
        <a href="{{ route('admin.categories_post.index') }}"
            class="inline-block px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-md shadow transition">
            ← Quay lại danh sách
        </a>
    </form>
</div>
@endsection


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const parentSelect = document.getElementById('parent_id');
        const nameField = document.getElementById('name-field');
        const nameInput = document.getElementById('name');
        const addBtn = document.getElementById('add-child-category');
        const wrapper = document.getElementById('child-category-wrapper');

        function toggleNameField() {
            if (parentSelect.value !== "") {
                nameField.style.display = 'none';
                nameInput.required = false;
                nameInput.value = '';
                nameInput.disabled = true;
            } else {
                nameField.style.display = 'block';
                nameInput.required = true;
                nameInput.disabled = false;
            }
        }

        toggleNameField();

        parentSelect.addEventListener('change', toggleNameField);

        addBtn.addEventListener('click', function () {
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'children[]';
            input.className = 'w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300';
            input.placeholder = 'Tên danh mục con';
            wrapper.appendChild(input);
        });
    });
</script>

