<div class="category-sidebar">
    <h5 class="sidebar-title py-2">
        <i class="ci-menu me-2"></i> Tất Cả Danh Mục
    </h5>
    <ul class="category-list">
        @foreach ($parentCategories as $parent)
            @php
                $childCategories = $categories->where('parent_id', $parent->id);
                $hasChildren = $childCategories->isNotEmpty();
                $isParentActive = request()->route('id') == $parent->id;
                $isChildActive = $childCategories->pluck('id')->contains(request()->route('id'));
                $isExpanded = $isParentActive || $isChildActive;
            @endphp
            <li class="parent-category">
                <a href="{{ route('products.byCategory', ['id' => $parent->id, 'slug' => Str::slug($parent->name)]) }}"
                   class="{{ $isExpanded ? 'active' : '' }}">
                    {{ $parent->name }}
                </a>
            </li>

            @if ($hasChildren && $isExpanded)
                <ul class="child-categories">
                    @foreach ($childCategories as $child)
                        <li>
                            <a href="{{ route('products.byCategory', ['id' => $child->id, 'slug' => Str::slug($child->name)]) }}"
                               class="{{ request()->route('id') == $child->id ? 'active' : '' }}">
                                {{ $child->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endforeach
    </ul>

    <form action="{{ url()->current() }}" method="GET" id="price-filter-form" data-ajax-filter="true">
        <div class="filter-section">
            <h5>Khoảng Giá</h5>
            <div class="d-flex align-items-center">
                <input type="number" id="min_price" name="min_price" class="form-control" placeholder="TỪ"
                       value="{{ request('min_price') }}" min="0">
                <span class="mx-2">-</span>
                <input type="number" id="max_price" name="max_price" class="form-control" placeholder="ĐẾN"
                       value="{{ request('max_price') }}" min="0">
            </div>
            <div id="price-error" class="text-danger mt-2" style="display: none; font-size: .875em;"></div>
            <button type="submit" class="btn btn-danger w-100 mt-3">ÁP DỤNG</button>
        </div>

        <div class="filter-section">
            <h5>Đánh Giá</h5>
            <div class="rating-filter">
                <ul class="list-unstyled">
                    @for ($i = 5; $i >= 1; $i--)
                        <li class="d-flex align-items-center mb-2">
                            <a href="{{ request()->fullUrlWithQuery(['rating' => $i]) }}"
                               class="text-decoration-none w-100">
                                <div class="d-flex align-items-center">
                                    @for ($j = 1; $j <= 5; $j++)
                                        <i class="ci-star-filled fs-base {{ $j <= $i ? 'text-warning' : 'text-body-tertiary' }}"
                                           style="font-size: 1.2rem;"></i>
                                    @endfor
                                    <span class="ms-2 fs-sm text-body-secondary">{{ $i < 5 ? 'trở lên' : '' }}</span>
                                </div>
                            </a>
                        </li>
                    @endfor
                </ul>
            </div>
        </div>

        @php
            $isFiltered = request()->has('min_price') || request()->has('max_price') || request()->has('rating') ||
                          isset($currentCategory) || (request()->filled('sort') && request('sort') !== 'tat_ca');
        @endphp

        @if($isFiltered)
            <div class="filter-section">
                <a href="{{ route('users.products.all') }}" class="btn btn-outline-secondary w-100">Xóa bộ lọc</a>
            </div>
        @endif
    </form>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productsContainer = document.getElementById('ajax-products-list');

    function ajaxLoad(url, method = 'GET', data = null) {
        productsContainer.classList.add('opacity-50');
        fetch(url, {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: method === 'POST' ? data : null,
        })
        .then(response => response.text())
        .then(html => {
            productsContainer.innerHTML = html;
            productsContainer.classList.remove('opacity-50');
            window.scrollTo({ top: productsContainer.offsetTop - 80, behavior: 'smooth' });
        });
    }

    document.querySelectorAll('form[data-ajax-filter]').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();
            ajaxLoad(`${this.action}?${params}`);
        });
    });

    document.querySelectorAll('.rating-filter a').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            ajaxLoad(this.href);
        });
    });

    const clearBtn = document.querySelector('.btn-outline-secondary.w-100');
    if (clearBtn) {
        clearBtn.addEventListener('click', function (e) {
            e.preventDefault();
            ajaxLoad(this.href);
        });
    }
});
</script>
@endpush
