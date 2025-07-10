<div class="container mx-auto flex items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <img id="sticky-image" src="{{ $imageUrl }}" alt="Ảnh sản phẩm" class="w-12 h-12 rounded-md object-cover">
        <div>
            <p id="sticky-name" class="font-semibold text-sm text-gray-800">{{ $product->name }}</p>
            <p id="sticky-variant" class="text-xs text-gray-500">
                {{ collect($initialVariantAttributes)->values()->join(', ') }}
            </p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <div>
            <p id="sticky-price" class="font-bold text-red-600 text-right">
                {{ number_format($priceToDisplay) }}₫
            </p>
            @if ($isSale || $isFlashSale)
                <p id="sticky-original-price" class="text-xs text-gray-500 line-through text-right">
                    {{ number_format($originalPrice) }}₫
                </p>
            @endif
        </div>
        <button
            class="hidden sm:flex items-center justify-center p-3 border-2 border-red-600 text-red-600 font-bold rounded-lg hover:bg-red-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </button>
        <button
            class="flex-1 w-full sm:w-auto px-6 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors">
            Mua ngay
        </button>
    </div>
</div>
