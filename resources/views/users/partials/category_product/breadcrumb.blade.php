<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2 text-truncate" style="font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Trang chủ</a></li>

            @if (!empty($searchQuery))
            <li class="breadcrumb-item active" aria-current="page">Tìm kiếm: "{{ $searchQuery }}"</li>
            @elseif (isset($currentCategory))
            <li class="breadcrumb-item">
                <a href="{{ route('users.products.all') }}">Danh mục sản phẩm</a>
            </li>

            @php
            $ancestors = collect([]);
            $cat = $currentCategory;
            while ($cat->parent_id) {
            $parent = $categories->firstWhere('id', $cat->parent_id);
            if ($parent) {
            $ancestors->prepend($parent);
            $cat = $parent;
            } else {
            break;
            }
            }
            @endphp

            @foreach ($ancestors as $ancestor)
            <li class="breadcrumb-item">
                <a href="{{ route('products.byCategory', ['id' => $ancestor->id, 'slug' => Str::slug($ancestor->name)]) }}"
                    title="{{ $ancestor->name }}">{{ $ancestor->name }}</a>
            </li>
            @endforeach

            <li class="breadcrumb-item active" aria-current="page" title="{{ $currentCategory->name }}">
                {{ $currentCategory->name }}
            </li>
            @else
            <li class="breadcrumb-item"><a href="{{ route('users.products.all') }}">Danh mục sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tất cả sản phẩm</li>
            @endif
        </ol>
    </nav>
</div>