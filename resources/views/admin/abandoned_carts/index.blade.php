@extends('admin.layouts.app')

@section('content')
    @include('admin.abandoned_carts.css.style')

    <div class="px-4 sm:px-6 md:px-8 py-8">
        <div class="container mx-auto max-w-full">

            <!-- PAGE HEADER -->
            @include('admin.abandoned_carts.layouts.header')

            <!-- ALPINE COMPONENT WRAPPER -->
            <div class="card-custom" x-data="cartSelection()" x-init="initSelected()">
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
                            <button class="btn btn-primary btn-sm" @click="bulkSendEmail"
                                :disabled="hasSentEmail || selectedCarts.length === 0">
                                <i class="fas fa-paper-plane mr-1"></i>Gửi Email hàng loạt
                            </button>
                            <button class="btn btn-info btn-sm" @click="bulkSendInApp"
                                :disabled="hasSentInApp || selectedCarts.length === 0">
                                <i class="fas fa-bell mr-1"></i>Gửi In-App hàng loạt
                            </button>

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
                                            <input type="checkbox" :value="{{ $cart->id }}" x-model="selectedCarts"
                                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </td>
                                        <td>
                                            <div class="font-semibold text-gray-800">
                                                {{ $cart->user->name ?? 'Khách vãng lai' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $cart->user->email ?? 'Chưa có thông tin' }}
                                            </div>
                                        </td>
                                        <td class="font-semibold">
                                            {{ number_format($cart->cart->items->sum('subtotal')) }}đ
                                        </td>
                                        <td class="text-center">{{ $cart->cart->items->count() }}</td>
                                        <td>{{ $cart->updated_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="flex justify-center items-center gap-2">

                                                {{-- Email status --}}
                                                <div class="relative group">
                                                    <span
                                                        class="status-icon-badge {{ $cart->email_status === 'sent' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700' }}">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </span>
                                                    <div
                                                        class="absolute bottom-full mb-1 hidden group-hover:block text-xs bg-black text-white px-2 py-1 rounded shadow">
                                                        {{ $cart->email_status === 'sent' ? 'Đã gửi Email' : 'Chưa gửi Email' }}
                                                    </div>
                                                </div>

                                                {{-- In-app status --}}
                                                <div class="relative group">
                                                    <span
                                                        class="status-icon-badge {{ $cart->in_app_notification_status === 'sent' ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-700' }}">
                                                        <i class="fas fa-bell"></i>
                                                    </span>
                                                    <div
                                                        class="absolute bottom-full mb-1 hidden group-hover:block text-xs bg-black text-white px-2 py-1 rounded shadow">
                                                        {{ $cart->in_app_notification_status === 'sent' ? 'Đã gửi In-App' : 'Chưa gửi In-App' }}
                                                    </div>
                                                </div>
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
                                                        @if ($cart->email_status === 'sent') disabled @endif>
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                @endif
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
    </div>
@endsection

@include('admin.abandoned_carts.script.script')
<script>
    function cartSelection() {
        return {
            selectedCarts: [],
            allCartIds: @json($abandonedCarts->pluck('id')),

            // Map trạng thái email (true nếu đã gửi)
            emailStatusMap: @json($abandonedCarts->mapWithKeys(fn($cart) => [$cart->id => $cart->email_status === 'sent'])),

            // Map trạng thái in-app (true nếu đã gửi)
            inAppStatusMap: @json($abandonedCarts->mapWithKeys(fn($cart) => [$cart->id => $cart->in_app_notification_status === 'sent'])),

            initSelected() {
                this.selectedCarts = [];
            },

            get selectAll() {
                return this.selectedCarts.length === this.allCartIds.length;
            },

            toggleSelectAll(event) {
                this.selectedCarts = event.target.checked ? [...this.allCartIds] : [];
            },

            // Kiểm tra có giỏ hàng nào đã gửi email chưa
            get hasSentEmail() {
                return this.selectedCarts.some(id => this.emailStatusMap[id] === true);
            },

            // Kiểm tra có giỏ hàng nào đã gửi in-app chưa
            get hasSentInApp() {
                return this.selectedCarts.some(id => this.inAppStatusMap[id] === true);
            },

            async bulkSendEmail() {
                if (this.selectedCarts.length === 0 || this.hasSentEmail) return;

                const confirm = await Swal.fire({
                    title: 'Xác nhận',
                    text: `Bạn có chắc muốn gửi email đến ${this.selectedCarts.length} giỏ hàng?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Gửi',
                    cancelButtonText: 'Hủy',
                });

                if (!confirm.isConfirmed) return;

                Swal.fire({
                    title: 'Đang gửi email...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch("{{ route('admin.abandoned_carts.bulk_send_email') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            cart_ids: this.selectedCarts
                        })
                    });

                    const result = await response.json();

                    Swal.fire({
                        icon: result.success ? 'success' : 'error',
                        title: result.success ? 'Thành công' : 'Lỗi',
                        text: result.message || '',
                    }).then(() => {
                        if (result.success) {
                            location.reload();
                        }
                    });

                } catch (error) {
                    Swal.fire('Lỗi', 'Đã xảy ra lỗi khi gửi email.', 'error');
                }
            },

            async bulkSendInApp() {
                if (this.selectedCarts.length === 0 || this.hasSentInApp) return;

                const confirm = await Swal.fire({
                    title: 'Xác nhận',
                    text: `Bạn có chắc muốn gửi thông báo in-app đến ${this.selectedCarts.length} giỏ hàng?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Gửi',
                    cancelButtonText: 'Hủy',
                });

                if (!confirm.isConfirmed) return;

                Swal.fire({
                    title: 'Đang gửi thông báo...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch("{{ route('admin.abandoned_carts.bulk_send_inapp') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            cart_ids: this.selectedCarts
                        })
                    });

                    const result = await response.json();

                    Swal.fire({
                        icon: result.success ? 'success' : 'error',
                        title: result.success ? 'Thành công' : 'Lỗi',
                        text: result.message || '',
                    }).then(() => {
                        if (result.success) {
                            location.reload();
                        }
                    });

                } catch (error) {
                    Swal.fire('Lỗi', 'Đã xảy ra lỗi khi gửi thông báo.', 'error');
                }
            }
        };
    }
</script>

{{-- <script>
    function cartSelection() {
        return {
            selectedCarts: [],
            allCartIds: @json($abandonedCarts->pluck('id')),

            initSelected() {
                this.selectedCarts = [];
            },

            get selectAll() {
                return this.selectedCarts.length === this.allCartIds.length;
            },

            toggleSelectAll(event) {
                this.selectedCarts = event.target.checked ? [...this.allCartIds] : [];
            },

            async bulkSendEmail() {
                if (this.selectedCarts.length === 0) return;

                const confirm = await Swal.fire({
                    title: 'Xác nhận',
                    text: `Bạn có chắc muốn gửi email đến ${this.selectedCarts.length} giỏ hàng?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Gửi',
                    cancelButtonText: 'Hủy',
                });

                if (!confirm.isConfirmed) return;

                Swal.fire({
                    title: 'Đang gửi email...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch("{{ route('admin.abandoned_carts.bulk_send_email') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            cart_ids: this.selectedCarts
                        })
                    });

                    const result = await response.json();

                    Swal.fire({
                        icon: result.success ? 'success' : 'error',
                        title: result.success ? 'Thành công' : 'Lỗi',
                        text: result.message || '',
                    }).then(() => {
                        if (result.success) {
                            location.reload();
                        }
                    });

                } catch (error) {
                    Swal.fire('Lỗi', 'Đã xảy ra lỗi khi gửi email.', 'error');
                }
            },

            async bulkSendInApp() {
                if (this.selectedCarts.length === 0) return;

                const confirm = await Swal.fire({
                    title: 'Xác nhận',
                    text: `Bạn có chắc muốn gửi thông báo in-app đến ${this.selectedCarts.length} giỏ hàng?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Gửi',
                    cancelButtonText: 'Hủy',
                });

                if (!confirm.isConfirmed) return;

                Swal.fire({
                    title: 'Đang gửi thông báo...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch("{{ route('admin.abandoned_carts.bulk_send_inapp') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            cart_ids: this.selectedCarts
                        })
                    });

                    const result = await response.json();

                    Swal.fire({
                        icon: result.success ? 'success' : 'error',
                        title: result.success ? 'Thành công' : 'Lỗi',
                        text: result.message || '',
                    }).then(() => {
                        if (result.success) {
                            location.reload();
                        }
                    });

                } catch (error) {
                    Swal.fire('Lỗi', 'Đã xảy ra lỗi khi gửi thông báo.', 'error');
                }
            }
        };
    }
</script> --}}
