@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Chi tiết đánh giá</h3>

    <div class="card p-4 shadow-sm">
        <div class="mb-3"><strong>Người đánh giá:</strong> {{ $review->user->name }}</div>
        <div class="mb-3"><strong>Sản phẩm:</strong> {{ $review->variant->product->name }}</div>
        <div class="mb-3"><strong>Phiên bản:</strong> SKU: {{ $review->variant->sku }}</div>
        <div class="mb-3">
            <strong>Đánh giá:</strong>
            <span class="text-warning">{!! str_repeat('★', $review->rating) !!}</span>
        </div>
        <div class="mb-3"><strong>Tiêu đề:</strong> {{ $review->title }}</div>
        <div class="mb-3"><strong>Nội dung:</strong> {{ $review->comment }}</div>
        <div class="mb-3">
            <strong>Trạng thái:</strong>
            <span class="badge bg-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'secondary') }}">
                {{ ucfirst($review->status) }}
            </span>
        </div>
        <div class="mb-3"><strong>Ngày tạo:</strong> {{ $review->created_at->format('d/m/Y') }}</div>

        <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST" class="mt-4 d-flex align-items-end gap-2">
            @csrf
            @method('PUT')
            <div>
                <label for="status" class="form-label mb-1">Cập nhật trạng thái</label>
                <select name="status" class="form-select">
                    <option value="pending" {{ $review->status == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ $review->status == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ $review->status == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
        </form>
    </div>
</div>
@endsection
