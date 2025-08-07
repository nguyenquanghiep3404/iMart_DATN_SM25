<div id="sticky-bar"
    class="fixed bottom-4 left-1/2 -translate-x-1/2 w-[calc(100%-2rem)] max-w-5xl bg-white/80 backdrop-blur-lg p-3 sm:p-4 rounded-2xl shadow-2xl transform translate-y-full transition-transform duration-300 z-40">
    <div class="flex items-center justify-between gap-4 w-full">
        <div class="flex items-center gap-4 overflow-hidden">
            <img src="{{ $tradeInItem->productVariant->primaryImage?->url ?? asset('assets/admin/img/placeholder-image.png') }}"
                alt="{{ $tradeInItem->productVariant->product->name ?? 'Sản phẩm' }}"
                class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
            <div class="hidden sm:block">
                <p class="font-semibold text-sm text-gray-900 truncate">
                    {{ $tradeInItem->productVariant->product->name ?? 'Không rõ tên' }}
                    ({{ $tradeInItem->type === 'used' ? 'Cũ' : 'New 99%' }})
                </p>
                <p id="sticky-bar-variant-info" class="text-xs text-gray-600">
                    @foreach ($tradeInItem->productVariant->attributeValues as $attributeValue)
                        {{ $attributeValue->value }}{{ $loop->last ? '' : ', ' }}
                    @endforeach
                    @if ($tradeInItem->productVariant->attributeValues->isEmpty())
                        Không có thông tin phân loại
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
            <div class="hidden lg:block text-right">
                <p class="font-bold text-red-600 text-lg">
                    {{ number_format($tradeInItem->selling_price) }}₫
                </p>
                @if ($tradeInItem->productVariant->price)
                    <p class="text-xs text-gray-500 line-through">
                        {{ number_format($tradeInItem->productVariant->price) }}₫
                    </p>
                @endif
            </div>
            <button
                class="hidden sm:flex items-center justify-center p-3 border-2 border-blue-600 text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l1.838-6.839a1.5 1.5 0 00-1.087-1.835H4.215">
                    </path>
                </svg>
            </button>
            <button
                class="w-full sm:w-auto px-4 sm:px-6 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors text-sm sm:text-base">
                Mua Ngay
            </button>
        </div>
    </div>
</div>
