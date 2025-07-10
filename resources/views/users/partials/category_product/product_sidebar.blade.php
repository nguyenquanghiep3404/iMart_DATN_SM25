<div class="category-sidebar shadow-lg">
    <h5 class="sidebar-title py-2">
        <i class="ci-menu me-2"></i> Tất Cả Danh Mục
    </h5>

    <ul class="category-list mb-3">
        @foreach ($parentCategories as $parent)
            @php
                $childCategories = $categories->where('parent_id', $parent->id);
                $hasChildren = $childCategories->isNotEmpty();

                $currentId = $currentCategory->id ?? null;
                $currentParentId = $currentCategory->parent_id ?? null;

                $isParentActive = $currentId === $parent->id;
                $isChildActive = $currentParentId === $parent->id;
                $isExpanded = $isParentActive || $isChildActive;
            @endphp

            <li class="parent-category mb-1">
                <a href="{{ $hasChildren ? '#' : route('products.byCategory', ['id' => $parent->id, 'slug' => Str::slug($parent->name)]) }}"
                   class="d-flex align-items-center px-2 py-2 {{ $hasChildren ? 'toggle-category' : 'category-link' }} {{ $isParentActive ? 'active fw-bold text-primary' : '' }}"
                   @if($hasChildren) data-category-id="{{ $parent->id }}" @endif>
                    <span class="flex-grow-1">{{ $parent->name }}</span>
                    @if($hasChildren)
                        <i class="ci-chevron-down ms-2 small text-muted"></i>
                    @endif
                </a>

                @if ($hasChildren)
                    <ul class="child-categories mb-2" style="display: {{ $isExpanded ? 'block' : 'none' }};">
                        @foreach ($childCategories as $child)
                            <li>
                                <a href="{{ route('products.byCategory', ['id' => $child->id, 'slug' => Str::slug($child->name)]) }}"
                                   class="category-link d-flex align-items-center px-2 py-2 rounded-2 {{ $currentId === $child->id ? 'active fw-bold text-dark bg-light border' : '' }}">
                                    <span class="flex-grow-1">{{ $child->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>

    {{-- Lọc giá --}}
    <form action="{{ url()->current() }}" method="GET" id="price-filter-form" data-ajax-filter="true">
        <div class="filter-section price-filter">
            <h5 class="mb-3"><i class="ci-wallet me-2 text-danger"></i>Khoảng Giá</h5>
            <div class="d-flex align-items-center gap-2 mb-2">
                <input type="number" id="min_price" name="min_price" class="form-control shadow-sm" placeholder="Từ"
                       value="{{ request('min_price') }}" min="0">
                <span class="mx-2">-</span>
                <input type="number" id="max_price" name="max_price" class="form-control shadow-sm" placeholder="Đến"
                       value="{{ request('max_price') }}" min="0">
            </div>
            <div id="price-error" class="text-danger mt-2" style="display: none; font-size: .875em;"></div>
            <button type="submit" class="btn btn-danger w-100 mt-3 shadow-sm">ÁP DỤNG</button>
        </div>

        {{-- Lọc đánh giá --}}
        <div class="filter-section rating-filter">
            <h5 class="mb-3"><i class="ci-star-filled me-2 text-warning"></i>Đánh Giá</h5>
            <ul class="list-unstyled mb-0">
                @for ($i = 5; $i >= 1; $i--)
                    <li class="d-flex align-items-center mb-2">
                        <a href="{{ request()->fullUrlWithQuery(['rating' => $i]) }}"
                           class="text-decoration-none w-100 d-flex align-items-center px-2 py-2 rounded-2">
                            <div class="d-flex align-items-center">
                                @for ($j = 1; $j <= 5; $j++)
                                    <i class="ci-star-filled fs-base {{ $j <= $i ? 'text-warning' : 'text-body-tertiary' }}" style="font-size: 1.2rem;"></i>
                                @endfor
                                <span class="ms-2 fs-sm text-body-secondary">{{ $i < 5 ? 'trở lên' : '' }}</span>
                            </div>
                        </a>
                    </li>
                @endfor
            </ul>
        </div>

        {{-- Nút xoá bộ lọc --}}
        @php
            $isFiltered = request()->has('min_price') || request()->has('max_price') || request()->has('rating') ||
                          isset($currentCategory) || (request()->filled('sort') && request('sort') !== 'tat_ca');
        @endphp
        @if($isFiltered)
            <div class="filter-section">
                <a href="{{ route('users.products.all') }}" class="btn btn-outline-secondary w-100 shadow-sm">Xóa bộ lọc</a>
            </div>
        @endif
    </form>
</div>


