@extends('admin.layouts.app')

@section('title', 'Chi tiết Người dùng: ' . $user->name)

@push('styles')
{{-- Các style đã có từ thiết kế trước --}}
<style>
    .body-content { background-color: #f8f9fa; }
    .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.07); background-color: #fff; margin-bottom: 1.5rem; }
    .card-custom-header { color: white; padding: 1rem 1.5rem; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; display: flex; justify-content: space-between; align-items: center; }
    .card-custom-header-primary { background-color: #4f46e5; border-bottom: 1px solid #4338ca; }
    .card-custom-header-info { background-color: #3b82f6; border-bottom: 1px solid #2563eb; }
    .card-custom-header-warning { background-color: #f59e0b; border-bottom: 1px solid #d97706; }
    .card-custom-title { font-size: 1.125rem; font-weight: 600; }
    .card-custom-tools a, .card-custom-tools button { color: rgba(255, 255, 255, 0.8); padding: 0.25rem 0.5rem; border-radius: 0.25rem; }
    .card-custom-tools a:hover, .card-custom-tools button:hover { color: white; background-color: rgba(255,255,255,0.1); }
    .card-custom-body { padding: 1.5rem; }
    .card-custom-footer { background-color: #f9fafb; padding: 1rem 1.5rem; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; border-top: 1px solid #e5e7eb; }

    .profile-avatar-container { width: 120px; height: 120px; border-radius: 50%; overflow: hidden; margin: 0 auto 1rem auto; border: 4px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .profile-avatar { width: 100%; height: 100%; object-fit: cover; }
    .profile-name { font-size: 1.5rem; font-weight: 700; color: #1f2937; text-align: center; }
    .profile-email { font-size: 0.875rem; color: #6b7280; text-align: center; margin-bottom: 0.5rem; }
    .profile-status { text-align: center; margin-bottom: 1.5rem; }

    .dl-horizontal dt { font-weight: 600; color: #374151; padding-bottom: 0.5rem; }
    .dl-horizontal dd { color: #4b5563; margin-bottom: 0.75rem; padding-left: 0.5rem; }
    @media (min-width: 640px) { /* sm */
        .dl-horizontal { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap-x: 1rem; }
        .dl-horizontal dt { grid-column: span 1 / span 1; }
        .dl-horizontal dd { grid-column: span 2 / span 2; margin-bottom: 0.5rem; padding-left:0; }
    }

    .badge-status { font-weight: 500; padding: 0.25em 0.75em; display: inline-block; border-radius: 9999px; font-size: 0.875rem; text-transform: capitalize; }
    .badge-status-active { background-color: #d1fae5; color: #065f46; }
    .badge-status-inactive { background-color: #fef3c7; color: #92400e; }
    .badge-status-banned { background-color: #fee2e2; color: #991b1b; }

    .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; }
    .btn-primary { background-color: #4f46e5; color: white; }
    .btn-primary:hover { background-color: #4338ca; }
    .btn-outline-secondary { color: #4b5563; border: 1px solid #d1d5db; background-color: white; }
    .btn-outline-secondary:hover { background-color: #f3f4f6; color: #2d3748; } /* Thêm màu chữ đậm hơn khi hover */

    .list-group-item { padding: 0.75rem 1.25rem; border: 1px solid rgba(0,0,0,.125); border-top-width: 0; }
    .list-group-item:first-child { border-top-width: 1px; border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; }
    .list-group-item:last-child { border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }

    .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 1100; display: flex; flex-direction: column; gap: 0.75rem; }
    .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; }
    .toast.hide { opacity: 0; transform: translateX(100%); }
</style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">

    {{-- Toast Container --}}
    <div id="toast-container" class="toast-container">
        {{-- Flash messages sẽ được hiển thị ở đây --}}
    </div>

    <div class="container mx-auto max-w-7xl">

        {{-- Page Header & Back Button --}}
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Chi tiết: {{ $user->name }}</h1>
                <nav aria-label="breadcrumb" class="mt-1">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800">Người dùng</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Chi tiết</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại Danh sách
                </a>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="flex flex-col lg:flex-row lg:space-x-6">

            {{-- Left Column: User Profile Summary --}}
            <div class="w-full lg:w-4/12 space-y-6">
                <div class="card-custom">
                    <div class="card-custom-body">
                        <div class="profile-avatar-container">
                            <img src="{{ $user->avatar_url ?? asset('adminlte/dist/img/avatar_placeholder.png') }}" alt="{{ $user->name }}" class="profile-avatar">
                        </div>
                        <h2 class="profile-name">{{ $user->name }}</h2>
                        <p class="profile-email">
                            {{ $user->email }}
                            @if($user->email_verified_at)
                                <i class="fas fa-check-circle text-green-500 ml-1" title="Đã xác thực email lúc {{ $user->email_verified_at->format('d/m/Y H:i') }}"></i>
                            @else
                                <i class="fas fa-exclamation-triangle text-yellow-500 ml-1" title="Chưa xác thực email"></i>
                            @endif
                        </p>
                        @if($user->phone_number)
                        <p class="profile-email"><i class="fas fa-phone-alt fa-xs mr-1"></i>{{ $user->phone_number }}</p>
                        @endif
                        <div class="profile-status">
                            @php
                                $statusClass = '';
                                switch ($user->status) {
                                    case 'active': $statusClass = 'badge-status-active'; break;
                                    case 'inactive': $statusClass = 'badge-status-inactive'; break;
                                    case 'banned': $statusClass = 'badge-status-banned'; break;
                                    default: $statusClass = 'bg-gray-200 text-gray-800'; break;
                                }
                            @endphp
                            <span class="badge-status {{ $statusClass }}">{{ $user->status }}</span>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary w-full">
                                <i class="fas fa-edit mr-2"></i> Chỉnh sửa Hồ sơ
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Detailed Information & Related Data --}}
            <div class="w-full lg:w-8/12 space-y-6 mt-6 lg:mt-0">
                {{-- Account Details Card --}}
                <div class="card-custom">
                    <div class="card-custom-header card-custom-header-primary">
                        <h3 class="card-custom-title">Thông tin Tài khoản</h3>
                    </div>
                    <div class="card-custom-body">
                        <dl class="dl-horizontal">
                            <dt>ID Người dùng</dt>
                            <dd>{{ $user->id }}</dd>

                            <dt>Ngày đăng ký</dt>
                            <dd>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i:s') : 'N/A' }}</dd>

                            <dt>Cập nhật lần cuối</dt>
                            <dd>{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</dd>

                            <dt>Email đã xác thực</dt>
                            <dd>
                                @if($user->email_verified_at)
                                    Có (lúc {{ $user->email_verified_at->format('d/m/Y H:i:s') }})
                                @else
                                    Chưa
                                @endif
                            </dd>

                            <dt>Đăng nhập lần cuối</dt>
                            <dd>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i:s') : 'Chưa đăng nhập lần nào' }}</dd>
                        </dl>
                    </div>
                </div>

                {{-- Roles Card --}}
                @if($user->roles && $user->roles->count() > 0)
                <div class="card-custom">
                    <div class="card-custom-header card-custom-header-warning">
                        <h3 class="card-custom-title">Vai trò Người dùng</h3>
                    </div>
                    <div class="card-custom-body p-0">
                        <ul class="list-group">
                            @foreach($user->roles as $role)
                                <li class="list-group-item">{{ $role->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- JavaScript cho Toast (giữ lại hoặc điều chỉnh từ file trước) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast');
    const hideToast = (toastElement) => {
        if (toastElement) {
            toastElement.classList.add('hide');
            setTimeout(() => {
                if(toastElement.parentNode) {
                    toastElement.remove();
                }
            }, 350);
        }
    };
    toasts.forEach(toast => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
        const autoHideTimeout = setTimeout(() => { hideToast(toast); }, 5000);
        const closeButton = toast.querySelector('[data-dismiss-target]');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                clearTimeout(autoHideTimeout);
                const targetSelector = this.getAttribute('data-dismiss-target');
                const toastToHide = document.querySelector(targetSelector);
                hideToast(toastToHide);
            });
        } else {
             toast.addEventListener('click', () => hideToast(toast));
        }
    });
});
</script>
@endpush
