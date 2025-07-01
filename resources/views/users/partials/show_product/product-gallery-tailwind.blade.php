<div class="image-gallery">
    <div id="main-image-container" class="group rounded-lg border border-gray-200 relative cursor-pointer">
        <img id="mainImage" src="https://placehold.co/600x600/f0f0f0/333?text=iPhone+15+Pro"
            alt="iPhone 15 Pro Max - Ảnh chính" class="w-full h-auto object-cover rounded-lg">
        <button id="gallery-prev-btn" class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/70 p-2 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-opacity z-10">
            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button id="gallery-next-btn" class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/70 p-2 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-opacity z-10">
            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>
    <div class="relative items-center mt-4" style="max-width: 600px; min-width: 600px; margin: 0 auto; display: flex; align-items: center; height: 112px;">
        <button id="thumbs-prev-btn" class="absolute left-0 z-10 bg-white/70 p-1 rounded-full shadow-md">
            <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <div id="main-thumbnails"
    class="flex items-center gap-3 overflow-x-auto scrollbar-hide px-2 py-1"
    style="width: auto; max-width: 520px; height: auto; margin: 0 auto;">
    <!-- ảnh sẽ render ở đây -->
</div>

        <button id="thumbs-next-btn" class="absolute right-0 z-10 bg-white/70 p-1 rounded-full shadow-md">
            <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>
</div>
@php
    $initialImages = [];
    if ($defaultVariant && $defaultVariant->images->count()) {
        $initialImages = $defaultVariant->images->map(fn($img) => Storage::url($img->path))->toArray();
    } elseif ($product->coverImage) {
        $initialImages[] = Storage::url($product->coverImage->path);
    }
    foreach ($product->galleryImages as $galleryImage) {
        $initialImages[] = Storage::url($galleryImage->path);
    }
    $initialImages = array_unique($initialImages);
@endphp


