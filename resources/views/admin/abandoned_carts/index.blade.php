@extends('admin.comments.layouts.main')

@section('content')
    @include('admin.abandoned_carts.css.style')
    <div class="px-4 sm:px-6 md:px-8 py-8">
        <div class="container mx-auto max-w-full">

            <!-- PAGE HEADER -->
            @include('admin.abandoned_carts.layouts.header')

            <div class="card-custom">
                <div class="card-custom-header">
                    <h3 class="card-custom-title">
                        Danh sách giỏ hàng ({{ $totalAbandonedCarts }})
                    </h3>
                </div>

                <div class="card-custom-body">
                    <!-- FILTERS -->
                    @include('admin.abandoned_carts.layouts.filters')

                    <!-- BULK ACTIONS BAR -->
                    <div x-show="selectedCarts.length > 0" x-cloak x-transition
                        class="bg-gray-100 border border-gray-200 rounded-lg p-3 my-4 flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-700">
                            Đã chọn <strong x-text="selectedCarts.length"></strong> giỏ hàng
                        </p>
                        <div class="space-x-2">
                            <button class="btn btn-primary btn-sm"><i class="fas fa-paper-plane mr-1"></i>Gửi Email hàng
                                loạt</button>
                            <button class="btn btn-info btn-sm"><i class="fas fa-bell mr-1"></i>Gửi In-App hàng
                                loạt</button>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center px-4">
                                        <input type="checkbox" @change="toggleSelectAll" :checked="selectAll"
                                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    </th>
                                    <th>Khách Hàng</th>
                                    <th>Giá Trị</th>
                                    <th class="text-center">Số SP</th>
                                    <th>Lần cuối cập nhật</th>
                                    <th class="text-center">Trạng thái liên hệ</th>
                                    <th style="width: 150px;" class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($abandonedCarts as $cart)
                                    <tr>
                                        <td class="text-center px-4">
                                            <input type="checkbox" name="selected[]" value="{{ $cart->id }}"
                                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </td>
                                        <td>
                                            <div class="font-semibold text-gray-800">
                                                {{ $cart->user->name ?? 'Khách vãng lai' }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $cart->user->email ?? 'Chưa có thông tin' }}</div>
                                        </td>
                                        <td class="font-semibold">
                                            {{ number_format($cart->cart->items->sum('subtotal')) }}đ</td>
                                        <td class="text-center">{{ $cart->cart->items->count() }}</td>
                                        <td>{{ $cart->updated_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="flex justify-center items-center gap-2">
                                                <span
                                                    class="status-icon-badge {{ $cart->email_sent ? 'status-sent' : 'status-pending' }}"
                                                    title="{{ $cart->email_sent ? 'Đã gửi Email' : 'Chưa gửi Email' }}">
                                                    <i class="fas fa-paper-plane"></i>
                                                </span>
                                                <span
                                                    class="status-icon-badge {{ $cart->in_app_sent ? 'status-sent' : 'status-pending' }}"
                                                    title="{{ $cart->in_app_sent ? 'Đã gửi In-App' : 'Chưa gửi In-App' }}">
                                                    <i class="fas fa-bell"></i>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="inline-flex space-x-1">
                                                <a href="{{ route('admin.abandoned_carts.show', $cart->id) }}"
                                                    class="btn btn-secondary btn-sm" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($cart->user && $cart->user->email)
                                                    <button class="btn btn-primary btn-sm btn-send-email"
                                                        data-id="{{ $cart->id }}" title="Gửi mail khôi phục"
                                                        @if ($cart->email_sent) disabled @endif>
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                @endif
                                                {{-- @if ($cart->user)
                                                    <button class="btn btn-info btn-sm" title="Gửi thông báo in-app">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                @endif --}}
                                                @if ($cart->user)
                                                    <button class="btn btn-info btn-sm btn-send-inapp"
                                                        data-id="{{ $cart->id }}" title="Gửi thông báo in-app"
                                                        @if ($cart->in_app_notification_status === 'sent') disabled @endif>
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-10 text-gray-500">
                                            <p>Không tìm thấy giỏ hàng nào phù hợp.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>

                <!-- PAGINATION -->
                @include('admin.abandoned_carts.layouts.pagtnatton')

            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </div>
@endsection
@include('admin.abandoned_carts.script.script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).on('click', '.btn-send-email', function(e) {
        e.preventDefault();

        const btn = $(this);
        const cartId = btn.data('id');

        Swal.fire({
            title: 'Xác nhận',
            text: 'Bạn có chắc muốn gửi email khôi phục giỏ hàng?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Gửi',
            cancelButtonText: 'Hủy',
        }).then((result) => {
            if (!result.isConfirmed) return;

            btn.prop('disabled', true);

            Swal.fire({
                title: 'Đang gửi...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();

                    $.post("{{ route('admin.abandoned_carts.send_email', ['id' => '__id__']) }}"
                        .replace('__id__', cartId), {
                            _token: '{{ csrf_token() }}'
                        }).done(function(res) {
                        // Debug console xem dữ liệu server trả về
                        console.log(res);

                        // Nếu server có trường success (true/false)
                        if ('success' in res) {
                            Swal.fire({
                                icon: res.success ? 'success' : 'error',
                                title: res.success ? 'Thành công' : 'Lỗi',
                                text: res.message || (res.success ?
                                    'Đã gửi email!' : 'Gửi thất bại'),
                            }).then(() => {
                                if (res.success) {
                                    location.reload();
                                } else {
                                    btn.prop('disabled', false);
                                }
                            });
                        } else {
                            // Nếu server chỉ trả message (không có success)
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: res.message ||
                                    'Đã gửi email thành công!',
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }).fail(function(xhr) {
                        console.error(xhr);
                        Swal.fire('Lỗi', 'Đã xảy ra lỗi khi gửi email.', 'error');
                        btn.prop('disabled', false);
                    });
                }
            });
        });
    });
</script>
