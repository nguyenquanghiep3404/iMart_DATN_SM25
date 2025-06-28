<div class="relative group border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
    <div class="w-full h-32 bg-gray-200 flex items-center justify-center">
        <img src="{{ $file->url }}" alt="{{ $file->alt_text ?? $file->original_name }}" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <p class="text-white text-xs text-center break-all">{{ $file->original_name }}</p>
        <div class="mt-2">
            {{-- Thêm các nút hành động ở đây nếu muốn, ví dụ: xóa, sửa --}}
        </div>
    </div>
</div>
