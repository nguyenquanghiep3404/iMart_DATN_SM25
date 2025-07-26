@extends('admin.layouts.app')
@section('title', 'Nhà cung cấp đã xoá')
@section('content')

<div class="px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-full">
        <div class="mb-4">
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                ← Quay lại danh sách
            </a>
        </div>
        <div class="card-custom">
            <div class="card-custom-header">
                <h3 class="card-custom-title">Nhà cung cấp đã xoá ({{ count($suppliers) }})</h3>
            </div>
            <div class="card-custom-body">
                <div class="overflow-x-auto">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Địa chỉ</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $index => $supplier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->email }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ $supplier->address_line }}
                                    @if ($supplier->ward || $supplier->district || $supplier->province)
                                    <br>
                                    <small class="text-gray-500">
                                        {{ implode(', ', array_filter([
                $supplier->ward->name_with_type ?? null,
                $supplier->district->name_with_type ?? null,
                $supplier->province->name_with_type ?? null
            ])) }}
                                    </small>
                                </td>
                                @endif
                                <td class="text-center space-x-1">
                                    <form method="POST" action="{{ route('admin.suppliers.restore', $supplier->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Khôi phục nhà cung cấp này?')">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.suppliers.forceDelete', $supplier->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa vĩnh viễn nhà cung cấp này?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-gray-500">Không có nhà cung cấp đã xoá.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f3f4f6;
    }

    .card-custom {
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        background-color: #fff;
    }

    .card-custom-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background-color: #f9fafb;
    }

    .card-custom-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }

    .card-custom-body {
        padding: 1.5rem;
    }

    .card-custom-footer {
        background-color: #f9fafb;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        font-size: 0.875rem;
        background-color: white;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        border-color: #4f46e5;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, .25);
    }

    .table-custom {
        width: 100%;
        min-width: 700px;
        color: #374151;
    }

    .table-custom th,
    .table-custom td {
        padding: 0.75rem 1rem;
        vertical-align: middle !important;
        border-bottom-width: 1px;
        border-color: #e5e7eb;
    }

    .table-custom td {
        white-space: normal;
    }

    .table-custom thead th {
        font-weight: 600;
        color: #4b5563;
        background-color: #f9fafb;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        text-align: left;
        white-space: nowrap;
    }

    .btn {
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1.25rem;
        border: 1px solid transparent;
        height: 35px;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .btn-primary {
        background-color: #4f46e5;
        color: white;
    }

    .btn-primary:hover {
        background-color: #4338ca;
    }

    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        border-color: #d1d5db;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
    }

    .btn-danger {
        background-color: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background-color: #dc2626;
    }
</style>
@endsection