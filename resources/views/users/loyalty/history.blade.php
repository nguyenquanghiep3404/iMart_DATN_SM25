@extends('users.layouts.profile')

@section('title', 'Lịch sử điểm thưởng')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 pb-2">
    <h1 class="h3 mb-0">Lịch sử điểm thưởng</h1>
    <div class="text-nowrap">
        <div class="text-end">Số điểm hiện tại</div>
        <div class="fs-2 text-primary fw-semibold text-end">{{ number_format($pointsBalance) }}</div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">Ngày giao dịch</th>
                        <th class="text-nowrap">Loại</th>
                        <th class="text-nowrap text-end">Điểm thay đổi</th>
                        <th>Mô tả</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-nowrap align-middle">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-nowrap align-middle">
                                @if($log->type == 'earn')
                                    <span class="badge bg-success bg-opacity-10 text-success">Tích điểm</span>
                                @elseif($log->type == 'spend')
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Sử dụng</span>
                                @elseif($log->type == 'manual_adjustment')
                                    <span class="badge bg-secondary">Điều chỉnh thủ công</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($log->type) }}</span>
                                @endif
                            </td>
                            <td class="text-nowrap text-end align-middle fs-base fw-medium {{ $log->points > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $log->points > 0 ? '+' : '' }}{{ number_format($log->points) }}
                            </td>
                            <td class="align-middle">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <p class="mb-0">Bạn chưa có giao dịch điểm thưởng nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($logs->hasPages())
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
