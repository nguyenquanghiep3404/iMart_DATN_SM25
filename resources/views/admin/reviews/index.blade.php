@extends('admin.layouts.app')

@section('content')
<div class="body-content px-8 py-8 bg-slate-100">
    <div class="flex justify-between mb-10">
        <div class="page-title">
            <h3 class="mb-0 text-[28px]">Reviews</h3>
            <ul class="text-tiny font-medium flex items-center space-x-3 text-text3">
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="text-hover-primary">Home</a>
                </li>
                <li class="breadcrumb-item flex items-center">
                    <span class="inline-block bg-text3/60 w-[4px] h-[4px] rounded-full"></span>
                </li>
                <li class="breadcrumb-item text-muted">Reviews List</li>
            </ul>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-md shadow py-4">
        <div class="tp-search-box flex items-center justify-between px-8 py-6 flex-wrap">
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="relative mb-4 md:mb-0 w-full md:w-auto">
                <input
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="input h-[44px] w-full pl-14 pr-4 rounded border border-gray-300 focus:ring focus:ring-blue-100 focus:outline-none"
                    type="text"
                    placeholder="Tìm theo tên sản phẩm...">
                <button type="submit" class="absolute top-1/2 left-5 transform -translate-y-1/2 text-gray-500 hover:text-blue-500">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                        <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" />
                        <path d="M18.9999 19L14.6499 14.65" stroke="currentColor" stroke-width="2" />
                    </svg>
                </button>
            </form>

            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span class="text-sm">Rating:</span>
                    <select class="border border-gray-300 rounded px-2 py-1 text-sm">
                        <option>All</option>
                        <option>5 Star</option>
                        <option>4 Star</option>
                        <option>3 Star</option>
                        <option>2 Star</option>
                        <option>1 Star</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto px-8">
            <table class="min-w-full text-sm text-gray-600">
                <thead class="bg-gray-100 text-gray-700 uppercase">
                    <tr>
                        <th class="px-4 py-3">Sản phẩm</th>
                        <th class="px-4 py-3 text-right">Người dùng</th>
                        <th class="px-4 py-3 text-right">Trạng thái</th>
                        <th class="px-4 py-3 text-right">Số sao</th>
                        <th class="px-4 py-3 text-right">Ngày</th>
                        <th class="px-4 py-3 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr class="border-b">
                        <td class="px-4 py-4 flex items-center space-x-3">
                            <img src="{{ $review->variant->product->image_url ?? 'default.jpg' }}" class="w-12 h-12 object-cover rounded" alt="">
                            <span class="font-medium">{{ $review->variant->product->name }}</span>
                        </td>
                        <td class="px-4 py-4 text-right">{{ $review->user->name }}</td>
                        <td class="px-4 py-4 text-right">
                            <span class="inline-block px-3 py-1 text-sm font-medium rounded-full transition duration-300
                                @if($review->status == 'approved')
                                    bg-green-100 text-green-800
                                @elseif($review->status == 'pending')
                                    bg-yellow-100 text-yellow-800
                                @elseif($review->status == 'rejected')
                                    bg-red-100 text-red-800
                                @else
                                    bg-gray-100 text-gray-700
                                @endif
                            ">
                                {{ ucfirst($review->status) }}
                            </span>
                        </td>

                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="{{ $i <= $review->rating ? '#facc15' : '#e5e7eb' }}" viewBox="0 0 24 24">
                                    <path d="M12 .587l3.668 7.568L24 9.75l-6 5.939L19.336 24 12 20.02 4.664 24 6 15.689 0 9.75l8.332-1.595z" />
                                    </svg>
                                    @endfor
                            </div>
                        </td>
                        <td class="px-4 py-4 text-right">{{ $review->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('admin.reviews.show', $review->id) }}" class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600">Xem</a>
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xoá?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Không có đánh giá nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-8 py-4">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection