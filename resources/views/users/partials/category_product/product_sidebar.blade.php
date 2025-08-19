<div class="category-sidebar shadow-lg">
    <h5 class="sidebar-title py-2 px-3 d-flex align-items-center" style="width: 100%; height: 72px;">
        <i class="ci-menu me-2 "></i> Tất Cả Danh Mục
    </h5>
    <ul class="category-list">
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

            <li class="parent-category">
                <a class="d-flex align-items-center px-2 py-1 {{ $hasChildren ? 'toggle-category' : 'category-link' }}"
                   @if ($hasChildren) data-category-id="{{ $parent->id }}" @else href="{{ route('products.byCategory', ['id' => $parent->id, 'slug' => Str::slug($parent->name)]) }}" @endif>
                    <span class="flex-grow-1">{{ $parent->name }}</span>
                    @if ($hasChildren)
                        <i class="ci-chevron-down ms-2 small"></i>
                    @endif
                </a>

                @if ($hasChildren)
                    <ul class="child-categories {{ $isExpanded ? 'open' : '' }}">
                        <li>
                            <a href="{{ route('products.byCategory', ['id' => $parent->id, 'slug' => Str::slug($parent->name)]) }}"
                               class="category-link d-flex align-items-center px-2 py-1 rounded-2">
                                <span class="flex-grow-1">Tất cả {{ $parent->name }}</span>
                            </a>
                        </li>
                        
                        @foreach ($childCategories as $child)
                            <li>
                                <a href="{{ route('products.byCategory', ['id' => $child->id, 'slug' => Str::slug($child->name)]) }}"
                                   class="category-link d-flex align-items-center px-2 py-1 rounded-2">
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


<div class="category-sidebar shadow-lg" style="margin-top: 15px; padding-bottom: 20px;">
    <div class="filter-section price-filter">
        <h5 class="mb-0 d-flex justify-content-between align-items-center price-toggle" style="cursor: pointer;">
            <span>Mức giá</span>
            <i class="ci-chevron-down"></i>
        </h5>

        <div class="price-filter-content">
            <h6 class="mb-3 mt-3">Chọn mức giá nhanh</h6>
            <div class="d-flex flex-column gap-2 mb-4">
                <label><input type="checkbox" name="muc-gia[]" value="all"> Tất cả</label>
                <label><input type="checkbox" name="muc-gia[]" value="duoi-2-trieu"> Dưới 2 triệu</label>
                <label><input type="checkbox" name="muc-gia[]" value="tu-2-4-trieu"> Từ 2 - 4 triệu</label>
                <label><input type="checkbox" name="muc-gia[]" value="tu-4-7-trieu"> Từ 4 - 7 triệu</label>
                <label><input type="checkbox" name="muc-gia[]" value="tu-7-13-trieu"> Từ 7 - 13 triệu</label>
                <label><input type="checkbox" name="muc-gia[]" value="tu-13-20-trieu"> Từ 13 - 20 triệu</label>
                <label><input type="checkbox" name="muc-gia[]" value="tren-20-trieu"> Trên 20 triệu</label>
            </div>

            <h6 class="mb-3 mt-3">Hoặc chọn khoảng giá phù hợp với bạn</h6>
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <input type="text" id="min-price-display" class="form-control text-center" readonly>
                <span style="font-size: 1.5rem;">~</span>
                <input type="text" id="max-price-display" class="form-control text-center" readonly>
            </div>
            <div class="price-slider-wrapper" style="padding-bottom: 12px; padding-top: 12px;">
                <div id="price-range-slider"></div>
            </div>
            <input type="hidden" id="min_price" name="min_price">
            <input type="hidden" id="max_price" name="max_price">
        </div>
    </div>
</div>

<div class="category-sidebar shadow-lg" style="margin-top: 15px; padding-bottom: 20px;">
    <form>
        <div class="filter-section storage-filter">
            <h5 class="mb-0 d-flex justify-content-between align-items-center filter-toggle" style="cursor: pointer;">
                <span>Dung lượng</span>
                <i class="ci-chevron-down"></i>
            </h5>

            <div class="filter-content">
                <h6 class="mb-3 mt-3">Chọn dung lượng bạn thích</h6>
                <div class="d-flex flex-wrap gap-2">
                    <div class="storage-item" data-value="64GB">64GB</div>
                    <div class="storage-item" data-value="128GB">128GB</div>
                    <div class="storage-item" data-value="256GB">256GB</div>
                    <div class="storage-item" data-value="512GB">512GB</div>
                    <div class="storage-item" data-value="1TB">1TB</div>
                </div>
                <input type="hidden" name="storage" id="storage-input">
            </div>
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
        /* Consistent bottom padding */
        border: 1px solid #e9ecef;
        /* Subtle border for definition */
    }

    /* --- Sidebar Title --- */
    .category-sidebar .sidebar-title {
        background-color: #ffffff;
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

    .category-list .parent-category:last-child {
        margin-bottom: 0;
    }

    .category-list .parent-category a {
        display: flex;
        align-items: center;
        padding: 8px 15px;
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
        margin: 8px 0 0px 0;
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

    /* Animation mở/đóng cho danh mục con */
    .child-categories {
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.4s ease;
    }

    /* Khi mở */
    .child-categories.open {
        max-height: 200px;
        /* đặt lớn hơn chiều cao tối đa của menu con */
    }

    .toggle-category .ci-chevron-down {
        font-size: 1.3rem;
        /* Đồng bộ với filter-toggle */
        color: #dc3545;
        /* Màu đỏ giống price-toggle và filter-toggle */
        transition: transform 0.3s ease;
        /* Hiệu ứng xoay mượt mà */
    }

    .toggle-category.active .ci-chevron-down {
        transform: rotate(180deg);
        /* Xoay mũi tên khi mở */
    }

    .toggle-category[aria-expanded="true"] .ci-chevron-down {
        transform: rotate(180deg);
    }


    /* --- Filter Sections --- */
    .filter-section {
        padding: 20px 25px 0px 25px;
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
        margin-bottom: 15px;
        /* Tăng khoảng cách dưới cùng */
    }

    .custom-checkbox {
        margin-bottom: 10px;
        /* Tăng khoảng cách giữa các checkbox */
        font-size: 1.1em;
        /* Tăng kích thước chữ */
    }

    .custom-checkbox .form-check-input {
        transform: scale(1.2);
        /* Tăng kích thước checkbox */
        margin-right: 10px;
        /* Tăng khoảng cách giữa checkbox và label */
    }

    .custom-checkbox .form-check-label {
        line-height: 1.5;
        /* Điều chỉnh dòng chữ để dễ đọc hơn */
    }

    .price-filter-content,
    .storage-filter .filter-content {
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.35s ease;
    }

    .price-toggle,
    .storage-filter .filter-toggle {
        user-select: none;
    }

    .price-toggle i,
    .storage-filter .filter-toggle i {
        transition: transform 0.3s ease;
    }

    .price-toggle.active i,
    .storage-filter .filter-toggle.active i {
        transform: rotate(180deg);
    }

    .storage-item {
        display: flex;
        /* Sử dụng Flexbox */
        align-items: center;
        /* Căn giữa theo chiều dọc */
        justify-content: center;
        /* Căn giữa theo chiều ngang */
        padding: 6px 14px;
        border: 2px solid #d1d5db;
        /* Màu xám nhạt */
        border-radius: 10px;
        cursor: pointer;
        user-select: none;
        font-weight: 500;
        transition: all 0.2s ease;
        color: #111;
        min-width: 80px;
        /* Đảm bảo kích thước tối thiểu đồng đều */
        height: 36px;
        /* Chiều cao cố định để căn giữa tốt hơn */
        text-align: center;
        /* Dự phòng cho căn giữa văn bản */
    }

    .storage-item.active {
        border-color: #2563eb;
        /* xanh */
        background-color: #eff6ff;
        /* nền xanh nhạt */
        color: #2563eb;
    }

    .price-filter-content input[type="checkbox"] {
        width: 22px;
        height: 22px;
        cursor: pointer;
        vertical-align: middle;
        appearance: none;
        /* Ẩn checkbox mặc định */
        border: 2px solid #ccc;
        border-radius: 4px;
        position: relative;
        transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    /* Viền đỏ khi hover */
    .price-filter-content input[type="checkbox"]:hover {
        border-color: #dc3545;
        box-shadow: 0 0 5px rgba(220, 53, 69, 0.5);
    }

    /* Nền đỏ khi được tích */
    .price-filter-content input[type="checkbox"]:checked {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    /* Dấu tích trắng */
    .price-filter-content input[type="checkbox"]:checked::after {
        content: "";
        position: absolute;
        left: 6px;
        top: 2px;
        width: 6px;
        height: 12px;
        border: solid white;
        border-width: 0 2.5px 2.5px 0;
        transform: rotate(45deg);
    }

    /* Label để con trỏ chuột khi hover */
    .price-filter-content label {
        cursor: pointer;
        font-size: 1rem;
        user-select: none;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #212529;
        transition: color 0.3s ease;
    }

    /* Khi hover label đổi màu chữ */
    .price-filter-content label:hover {
        color: #dc3545;
    }

    .filter-tag {
        background-color: #d1d5db;
        border-radius: 20px;
        padding: 5px 12px;
        font-size: 14px;
        color: #000000;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }

    .filter-tag .remove-tag {
        color: #000000;
        cursor: pointer;
        font-size: 20px;
    }

    /* Tùy chỉnh thanh trượt chính */
    #price-range-slider {
        /* Loại bỏ các giá trị mặc định của thư viện để đảm bảo tùy chỉnh của bạn có hiệu lực */
        background-color: #e9ecef !important;
        height: 6px !important;
        border: none !important;
        box-shadow: none !important;
    }

    /* Tùy chỉnh phần thanh được kéo */
    #price-range-slider .noUi-connect {
        background: #0d6efd !important;
    }

    /* Tùy chỉnh các núm kéo (handles) */
    #price-range-slider .noUi-handle {
        width: 20px !important;
        height: 20px !important;
        top: -7px !important;
        /* Điều chỉnh vị trí núm kéo theo chiều dọc */
        background: #ffffff !important;
        border: 2px solid #0d6efd !important;
        border-radius: 50% !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15) !important;
        cursor: grab !important;
    }

    /* Bỏ các viền mặc định của núm kéo */
    #price-range-slider .noUi-handle:before,
    #price-range-slider .noUi-handle:after {
        display: none !important;
    }

    /* Tùy chỉnh input giá */
    #min-price-display,
    #max-price-display {
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        border-radius: 8px !important;
        padding: 8px !important;
        text-align: center !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        color: #495057 !important;
        width: 120px !important;
    }

    /* Điều chỉnh CSS của wrapper hoặc container cha */
    /* Đảm bảo không có padding ngang quá mức hoặc overflow: hidden trên container cha */
    .price-filter-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease-out;

    }

    .price-slider-wrapper {
        /* Tùy chỉnh khoảng cách bằng margin thay vì padding để tránh clipping */
        margin: 0 25px 0 10px !important;
    }
</style>
