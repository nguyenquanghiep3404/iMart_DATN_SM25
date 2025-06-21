@extends('profile.layouts.profile') {{-- Hoặc layout tùy theo hệ thống của bạn --}}

@section('content')
<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row gap-4">
                {{-- Ảnh sản phẩm --}}
                <div class="flex-shrink-0 text-center">
                    <a href="{{ route('users.products.show', $product->slug) }}">
                        <img src="{{ $product->coverImageUrl }}" alt="{{ $product->name }}" width="180">
                    </a>
                </div>

                {{-- Thông tin sản phẩm và đánh giá --}}
                <div class="flex-grow-1">
                    <h4 class="mb-2">{{ $product->name }}</h4>
                    <div class="text-muted mb-2">Giá: {{ number_format($product->defaultVariant->price, 0, ',', '.') }}đ</div>
                    @php
                    $colorValue = $variant->attributeValues
                    ->firstWhere('attribute.slug', 'mau-sac')
                    ->value ?? 'N/A';
                    $storageValue = $variant->attributeValues
                    ->firstWhere('attribute.slug', 'dung-luong-luu-tru')
                    ->value ?? 'N/A';
                    @endphp

                    <div class="mb-3">
                        <span class="text-dark-emphasis fw-medium me-2">Màu: {{ $colorValue }}</span>
                        <span class="text-dark-emphasis fw-medium me-2">Dung lượng: {{ $storageValue }}</span>
                    </div>

                    {{-- Đánh giá --}}
                    <div class="mt-3">
                        <div class="mb-2 text-warning fs-4">
                            {!! str_repeat('★', $review->rating) !!}
                            {!! str_repeat('☆', 5 - $review->rating) !!}
                        </div>
                        <h5 class="fw-semibold">{{ $review->title }}</h5>
                        <p class="mb-0">{{ $review->comment }}</p>
                        <div class="text-muted fs-sm mt-2">
                            Ngày đánh giá: {{ $review->created_at->format('d/m/Y') }}
                        </div>
                    </div>

                    {{-- Ảnh kèm đánh giá nếu có --}}
                    @if($review->images && count($review->images))
                    <div class="mt-4">
                        <h6>Hình ảnh đính kèm:</h6>
                        <div class="d-flex gap-3 flex-wrap">
                            @foreach($review->images as $image)
                            <a href="{{ asset('storage/' . $image->path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $image->path) }}" width="100" class="rounded border">
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection