@extends('users.layouts.app')

@section('title', $product->name . ' - iMart')

@section('meta')
<meta name="description" content="{{ $product->meta_description }}">
<meta name="keywords" content="{{ $product->meta_keywords }}">
@endsection

@section('content')
<!-- Product details -->
<section class="container pt-4 mt-md-2 mb-lg-4">
  <div class="row">
    <!-- Product gallery -->
    <div class="col-md-7 pe-md-4 mb-4">
      <div class="product-gallery">
        <div class="product-gallery-preview order-sm-2">
          <div class="product-gallery-preview-item active" id="first">
            <img class="image-zoom" src="{{ asset('assets/users/img/shop/single/gallery/01.jpg') }}" data-zoom="{{ asset('assets/users/img/shop/single/gallery/01.jpg') }}" alt="Product image">
            <div class="image-zoom-pane"></div>
          </div>
          <div class="product-gallery-preview-item" id="second">
            <img class="image-zoom" src="{{ asset('assets/users/img/shop/single/gallery/02.jpg') }}" data-zoom="{{ asset('assets/users/img/shop/single/gallery/02.jpg') }}" alt="Product image">
            <div class="image-zoom-pane"></div>
          </div>
          <div class="product-gallery-preview-item" id="third">
            <img class="image-zoom" src="{{ asset('assets/users/img/shop/single/gallery/03.jpg') }}" data-zoom="{{ asset('assets/users/img/shop/single/gallery/03.jpg') }}" alt="Product image">
            <div class="image-zoom-pane"></div>
          </div>
          <div class="product-gallery-preview-item" id="fourth">
            <img class="image-zoom" src="{{ asset('assets/users/img/shop/single/gallery/04.jpg') }}" data-zoom="{{ asset('assets/users/img/shop/single/gallery/04.jpg') }}" alt="Product image">
            <div class="image-zoom-pane"></div>
          </div>
        </div>
        <div class="product-gallery-thumblist order-sm-1">
          <a class="product-gallery-thumblist-item active" href="#first">
            <img src="{{ asset('assets/users/img/shop/single/gallery/th01.jpg') }}" alt="Product thumb">
          </a>
          <a class="product-gallery-thumblist-item" href="#second">
            <img src="{{ asset('assets/users/img/shop/single/gallery/th02.jpg') }}" alt="Product thumb">
          </a>
          <a class="product-gallery-thumblist-item" href="#third">
            <img src="{{ asset('assets/users/img/shop/single/gallery/th03.jpg') }}" alt="Product thumb">
          </a>
          <a class="product-gallery-thumblist-item" href="#fourth">
            <img src="{{ asset('assets/users/img/shop/single/gallery/th04.jpg') }}" alt="Product thumb">
          </a>
        </div>
      </div>
    </div>
    <!-- Product info -->
    <div class="col-md-5 pt-1 pt-md-4 pt-lg-5">
      <!-- Nav tabs -->
      <div class="d-flex justify-content-between pb-3">
        <div class="d-flex align-items-center">
          <a class="nav-link-style nav-link-light me-3" href="#">
            <i class="ci-arrow-left"></i>
          </a>
          <span class="fs-md text-light">1 / 4</span>
        </div>
        <div class="d-flex">
          <a class="nav-link-style nav-link-light me-2" href="#">
            <i class="ci-edit"></i>
          </a>
          <a class="nav-link-style nav-link-light" href="#">
            <i class="ci-trash"></i>
          </a>
        </div>
      </div>
      <div class="product-meta d-flex flex-wrap pb-2">
        <a class="product-meta-item" href="#">
          <i class="ci-download"></i>
          <span>Product details</span>
        </a>
        <a class="product-meta-item" href="#">
          <i class="ci-action-undo"></i>
          <span>Restore</span>
        </a>
        <a class="product-meta-item" href="#">
          <i class="ci-eye"></i>
          <span>Hide</span>
        </a>
      </div>
      <h1 class="h2 text-light pb-2">{{ $product->name }}</h1>
      <div class="h3 fw-normal text-light pb-2">
        @if($product->variants && $product->variants->isNotEmpty())
        {{ number_format($product->variants->first()->price) }}đ
        @endif
      </div>
      <div class="d-flex flex-wrap align-items-center pb-2">
        <div class="border-end border-light pe-3 me-3">
          <div class="text-light opacity-70 fs-sm">Category:</div>
          <a class="nav-link-style fs-sm" href="#">{{ $product->category->name ?? 'N/A' }}</a>
        </div>
        <div class="border-end border-light pe-3 me-3">
          <div class="text-light opacity-70 fs-sm">Brand:</div>
          <a class="nav-link-style fs-sm" href="#">Apple</a>
        </div>
        <div>
          <div class="text-light opacity-70 fs-sm">Tags:</div>
          <div class="fs-sm pt-1">
            <a class="btn btn-sm btn-outline-light btn-pill" href="#">Electronics</a>
            <a class="btn btn-sm btn-outline-light btn-pill" href="#">Smartphones</a>
          </div>
        </div>
      </div>
      <div class="d-flex flex-wrap align-items-center pb-4">
        <div class="border-end border-light pe-3 me-3">
          <div class="text-light opacity-70 fs-sm">Rating:</div>
          <div class="star-rating">
            <i class="star-rating-icon ci-star-filled active"></i>
            <i class="star-rating-icon ci-star-filled active"></i>
            <i class="star-rating-icon ci-star-filled active"></i>
            <i class="star-rating-icon ci-star-filled active"></i>
            <i class="star-rating-icon ci-star"></i>
          </div>
        </div>
        <div>
          <div class="text-light opacity-70 fs-sm">Status:</div>
          <span class="badge bg-success">{{ $product->status }}</span>
        </div>
      </div>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="general">
          <div class="row g-4">
            <div class="col-sm-6">
              <div class="mb-3 pb-2">
                <label class="form-label">Product name</label>
                <input class="form-control" type="text" value="{{ $product->name }}">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3 pb-2">
                <label class="form-label">Slug</label>
                <input class="form-control" type="text" value="{{ $product->slug }}">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3 pb-2">
                <label class="form-label">Category</label>
                <select class="form-select">
                  <option>{{ $product->category->name }}</option>
                </select>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3 pb-2">
                <label class="form-label">Brand</label>
                <select class="form-select">
                  <option>Apple</option>
                </select>
              </div>
            </div>
            <div class="col-12">
              <div class="mb-3 pb-2">
                <label class="form-label">Description</label>
                <textarea class="form-control" rows="6">{{ $product->description }}</textarea>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3 pb-2">
                <label class="form-label">Price</label>
                <div class="input-group">
                  <input class="form-control" type="number" value="{{ $product->variants && $product->variants->isNotEmpty() ? $product->variants->first()->price : 0 }}">
                  <span class="input-group-text">đ</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3 pb-2">
                <label class="form-label">SKU</label>
                <input class="form-control" type="text" value="MTKRY-001">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3 pb-2">
                <label class="form-label">Stock status</label>
                <select class="form-select">
                  <option>In stock</option>
                </select>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3 pb-2">
                <label class="form-label">Weight</label>
                <div class="input-group">
                  <input class="form-control" type="number" value="0.5">
                  <span class="input-group-text">kg</span>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="mb-3 pb-2">
                <label class="form-label">Tags</label>
                <input class="form-control" type="text" value="Electronics, Smartphones">
              </div>
            </div>
            <div class="col-12">
              <div class="mb-3 pb-2">
                <label class="form-label">Product status</label>
                <select class="form-select">
                  <option>{{ $product->status }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="d-flex flex-wrap justify-content-between pt-4">
        <button class="btn btn-secondary mt-2" type="button">
          <i class="ci-trash me-2"></i>
          Delete product
        </button>
        <div class="mt-2">
          <button class="btn btn-light me-2" type="button">Cancel</button>
          <button class="btn btn-primary" type="button">Save changes</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Product Tabs -->
<div class="row mt-5">
  <div class="col-12">
    <ul class="nav nav-tabs" id="productTabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="description-tab" data-bs-toggle="tab" href="#description" role="tab">
          Mô tả
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="specs-tab" data-bs-toggle="tab" href="#specs" role="tab">
          Thông số kỹ thuật
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="reviews-tab" data-bs-toggle="tab" href="#reviews" role="tab">
          Đánh giá
        </a>
      </li>
    </ul>
    <div class="tab-content p-4 border border-top-0 rounded-bottom">
      <!-- Description Tab -->
      <div class="tab-pane fade show active" id="description" role="tabpanel">
        {!! $product->description !!}
      </div>

      <!-- Specifications Tab -->
      <div class="tab-pane fade" id="specs" role="tabpanel">
        @if($product->variants && $product->variants->isNotEmpty() && $product->variants->first()->attributes)
        <table class="table table-striped">
          <tbody>
            @foreach($product->variants->first()->attributes as $attribute)
            <tr>
              <th>{{ $attribute->name }}</th>
              <td>{{ $attribute->value }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @else
        <p class="text-muted">Chưa có thông số kỹ thuật cho sản phẩm này.</p>
        @endif
      </div>

      <!-- Reviews Tab -->
      <div class="tab-pane fade show active" id="reviews" role="tabpanel">
        @if($product->reviews->isNotEmpty())
        @foreach($product->reviews as $review)
        <div class="review mb-4 border-bottom pb-3">
          <div class="d-flex align-items-center mb-2">
            {{-- Hiển thị số sao --}}
            <div class="rating text-warning me-2">
              @for($i = 1; $i <= 5; $i++)
                <i class="ci-star{{ $i <= $review->rating ? '-filled' : '' }}"></i>
                @endfor
            </div>
            <strong class="me-2">{{ $review->user->name }}</strong>
            <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
          </div>

          {{-- Tiêu đề nếu có --}}
          @if($review->title)
          <h6 class="fw-semibold mb-1">{{ $review->title }}</h6>
          @endif

          {{-- Nội dung đánh giá --}}
          <p class="mb-2">{{ $review->comment }}</p>

          {{-- Hình ảnh đính kèm nếu có --}}
          @if($review->images && count($review->images))
          <div class="d-flex gap-2 flex-wrap mt-2">
            @foreach($review->images as $image)
            <a href="{{ asset('storage/' . $image->path) }}" target="_blank">
              <img src="{{ asset('storage/' . $image->path) }}" width="80" class="rounded border">
            </a>
            @endforeach
          </div>
          @endif
        </div>
        @endforeach
        @else
        <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
        @endif
      </div>

    </div>
  </div>
</div>

<!-- Related Products -->
@if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
<div class="row mt-5">
  <div class="col-12">
    <h3 class="h4 mb-4">Sản phẩm liên quan</h3>
    <div class="row row-cols-2 row-cols-md-4 g-4">
      @foreach($relatedProducts as $relatedProduct)
      <div class="col">
        <div class="product-card">
          <a href="{{ route('users.products.show', $relatedProduct->slug) }}" class="product-thumb">
            <img src="{{ $relatedProduct->coverImageUrl }}" alt="{{ $relatedProduct->name }}">
          </a>
          <div class="product-info">
            <h4 class="product-title">
              <a href="{{ route('users.products.show', $relatedProduct->slug) }}">
                {{ $relatedProduct->name }}
              </a>
            </h4>
            <div class="product-price">
              @if($relatedProduct->variants->isNotEmpty())
              {{ number_format($relatedProduct->variants->first()->price) }}đ
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endif
@endsection

@push('styles')
<style>
  .product-gallery {
    position: relative;
  }

  .product-gallery-preview {
    position: relative;
    margin-bottom: 1rem;
  }

  .product-gallery-preview-item {
    display: none;
  }

  .product-gallery-preview-item.active {
    display: block;
  }

  .product-gallery-thumblist {
    display: flex;
    gap: 0.5rem;
  }

  .product-gallery-thumblist-item {
    width: 80px;
    height: 80px;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    overflow: hidden;
  }

  .product-gallery-thumblist-item.active {
    border-color: #0d6efd;
  }

  .product-gallery-thumblist-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
</style>
@endpush

@push('scripts')
<script>
  // Product gallery
  document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.product-gallery-thumblist-item');
    const previews = document.querySelectorAll('.product-gallery-preview-item');

    thumbnails.forEach(thumb => {
      thumb.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);

        // Update active states
        thumbnails.forEach(t => t.classList.remove('active'));
        previews.forEach(p => p.classList.remove('active'));

        this.classList.add('active');
        document.getElementById(targetId).classList.add('active');
      });
    });
  });
</script>
@endpush