<div class="category-sidebar shadow-lg">
    <h5 class="sidebar-title py-2 px-3 d-flex align-items-center" style="width: 100%; height: 72px;">
        <i class="ci-menu me-2 "></i> Tất Cả Danh Mục
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
                    @if ($hasChildren) data-category-id="{{ $parent->id }}" @endif>
                    <span class="flex-grow-1">{{ $parent->name }}</span>
                    @if ($hasChildren)
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
</div>

<div class="category-sidebar shadow-lg" style="margin-top: 20px;">
    <!-- Lọc giá -->
    <form action="{{ url()->current() }}" method="GET" id="price-filter-form" data-ajax-filter="true">
        <div class="filter-section price-filter">
            <h5 class="mb-3">Mức giá</h5>
            <h6 class="mb-3">Nhập giá phù hợp với bạn</h6>
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
    </form>
</div>

<style>
    /* --- General Sidebar Styling --- */
    .category-sidebar {
        background-color: #fcfcfc;
        /* Very light, almost white background */
        border-radius: 12px;
        /* More pronounced rounded corners */
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        /* Deeper, softer shadow */
        padding-bottom: 20px;
        /* Consistent bottom padding */
        border: 1px solid #e9ecef;
        /* Subtle border for definition */
    }

    /* --- Sidebar Title --- */
    .category-sidebar .sidebar-title {
        background-color: #eef2f6;
        /* Soft, cool grey background for title */
        color: #212529;
        /* Darker, more formal text color */
        padding: 18px 25px;
        /* Generous padding */
        font-size: 1.25rem;
        /* Larger and more prominent title */
        font-weight: 700;
        /* Extra bold */
        border-bottom: 1px solid #dee2e6;
        /* Clean separator */
        display: flex;
        align-items: center;
        gap: 10px;
        /* Space between icon and text */
        letter-spacing: 0.5px;
        /* Slightly spaced letters for title */
    }

    .category-sidebar .sidebar-title i {
        color: #007bff;
        /* Vibrant primary blue for the icon */
        font-size: 1.5rem;
        /* Larger icon for emphasis */
    }

    /* --- Category List --- */
    .category-list {
        list-style: none;
        padding: 0;
        margin: 15px 0;
        /* Adjusted vertical margin */
    }

    .category-list .parent-category {
        margin-bottom: 3px;
        /* Minimal spacing between parent items */
    }

    .category-list .parent-category:last-child {
        margin-bottom: 0;
    }

    .category-list .parent-category a {
        display: flex;
        align-items: center;
        padding: 12px 25px;
        /* More balanced padding */
        color: #495057;
        /* Consistent dark grey text */
        text-decoration: none;
        transition: background-color 0.25s ease-out, color 0.25s ease-out, box-shadow 0.25s ease-out;
        border-radius: 8px;
        /* Nicely rounded corners for items */
        margin: 0 15px;
        /* Indent items slightly from sidebar edge */
        font-size: 1.02rem;
        /* Slightly larger text */
        font-weight: 500;
        /* Medium weight for good readability */
    }

    .category-list .parent-category a:hover {
        background-color: #eaf3fd;
        /* Very light blue on hover */
        color: #0056b3;
        /* Darker blue on hover */
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        /* Subtle shadow on hover */
    }

    .category-list .parent-category a.active {
        background-color: #007bff;
        /* Primary blue for active parent */
        color: #ffffff !important;
        /* White text for active parent */
        font-weight: 600;
        /* Bolder active parent */
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        /* More prominent shadow for active */
        transform: translateY(-1px);
        /* Slight lift effect */
    }

    .category-list .parent-category a.active .ci-chevron-down,
    .category-list .parent-category a.active .ci-chevron-up {
        color: #ffffff !important;
        /* White arrow for active parent */
    }

    /* Toggle icon animation */
    .category-list .parent-category a .ci-chevron-down,
    .category-list .parent-category a .ci-chevron-up {
        transition: transform 0.25s ease-out;
    }

    .category-list .parent-category a.toggle-category[aria-expanded="true"] .ci-chevron-down {
        transform: rotate(180deg);
    }

    /* --- Child Categories --- */
    .child-categories {
        list-style: none;
        padding: 0;
        margin: 8px 0 15px 0;
        /* Adjusted top/bottom margin */
        padding-left: 45px;
        /* Deeper indent for child categories */
    }

    .child-categories li {
        margin-bottom: 2px;
        /* Minimal spacing */
    }

    .child-categories li:last-child {
        margin-bottom: 0;
    }

    .child-categories .category-link {
        padding: 10px 15px;
        /* Slightly less padding than parent links */
        color: #6c757d;
        /* Muted text for child links */
        text-decoration: none;
        transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
        border-radius: 6px;
        /* Smaller border-radius for child links */
        font-size: 0.95rem;
        /* Slightly smaller text for children */
    }

    .child-categories .category-link:hover {
        background-color: #f5f8fc;
        /* Very light grey-blue on hover */
        color: #343a40;
        /* Darker text on hover */
    }

    .child-categories .category-link.active {
        background-color: #e0f2ff;
        /* Lighter blue for active child */
        color: #0056b3 !important;
        /* Darker blue text for active child */
        font-weight: 600;
        /* Bolder active child */
        border: 1px solid #b3d7ff;
        /* Subtle border for active child */
        box-shadow: 0 1px 4px rgba(0, 123, 255, 0.1);
        /* Very subtle shadow */
    }

    /* --- Filter Sections --- */
    .filter-section {
        padding: 20px 25px;
        /* Consistent padding */
        border-top: 1px solid #eceff3;
        /* Lighter, cleaner separator */
    }

    .filter-section:first-of-type {
        border-top: none;
        /* No top border for the first filter section */
    }

    .filter-section h5 {
        font-size: 1.1rem;
        /* Clearer heading for filters */
        font-weight: 600;
        color: #343a40;
        margin-bottom: 15px;
        /* More space below heading */
        display: flex;
        align-items: center;
        gap: 8px;
        /* Space between icon and text */
    }

    .filter-section h5 i {
        font-size: 1.3rem;
        /* Slightly larger filter icons */
        color: #dc3545;
        /* Red for price, consistent with button */
    }

    /* Price Filter specific icon color */
    .filter-section.price-filter h5 i {
        color: #dc3545;
        /* Red for price icon */
    }

    /* Rating Filter specific icon color */
    .filter-section.rating-filter h5 i {
        color: #ffc107;
        /* Yellow for star icon */
    }


    /* --- Price Filter --- */
    .price-filter .form-control {
        border-color: #ced4da;
        border-radius: 6px;
        /* Slightly more rounded inputs */
        padding: 10px 12px;
        font-size: 0.95rem;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
        /* Subtle inner shadow */
        transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .price-filter .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25), inset 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .price-filter .btn-danger {
        background-color: #dc3545;
        /* Standard danger red */
        border-color: #dc3545;
        font-weight: 600;
        padding: 10px 15px;
        border-radius: 6px;
        /* Match input fields */
        transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        letter-spacing: 0.5px;
    }

    .price-filter .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.25);
    }

    .price-filter #price-error {
        font-size: 0.85rem;
        margin-top: 5px;
        /* Closer to inputs */
        padding-left: 2px;
    }

    /* --- Clear Filter Button --- */
    .filter-section .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
        font-weight: 600;
        padding: 10px 15px;
        border-radius: 6px;
        transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out, border-color 0.2s ease-in-out;
        letter-spacing: 0.5px;
    }

    .filter-section .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.25);
    }

    /* --- Responsive adjustments (optional, but good practice) --- */
    @media (max-width: 992px) {
        .category-sidebar {
            margin-bottom: 25px;
        }
    }
    .custom-checkbox-group {
        margin-bottom: 15px; /* Tăng khoảng cách dưới cùng */
    }
    .custom-checkbox {
        margin-bottom: 10px; /* Tăng khoảng cách giữa các checkbox */
        font-size: 1.1em; /* Tăng kích thước chữ */
    }
    .custom-checkbox .form-check-input {
        transform: scale(1.2); /* Tăng kích thước checkbox */
        margin-right: 10px; /* Tăng khoảng cách giữa checkbox và label */
    }
    .custom-checkbox .form-check-label {
        line-height: 1.5; /* Điều chỉnh dòng chữ để dễ đọc hơn */
    }

</style>
