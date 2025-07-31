@extends('users.layouts.profile')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Chi tiết đơn hàng #{{ $order->order_code }}</h3>
                <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-file-invoice"></i> Xem hóa đơn
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Trạng thái:</strong>
                                @if($order->status == 'pending_confirmation')
                                <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                                @elseif($order->status == 'processing')
                                <span class="badge bg-info text-dark">Đang xử lý</span>
                                @elseif($order->status == 'awaiting_shipment')
                                <span class="badge bg-info text-dark">Chờ lấy hàng</span>
                                @elseif($order->status == 'shipped')
                                <span class="badge bg-primary">Đang giao</span>
                                @elseif($order->status == 'out_for_delivery')
                                <span class="badge bg-primary">Đang giao</span>
                                @elseif($order->status == 'delivered')
                                <span class="badge bg-success">Đã giao</span>
                                @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">Đã hủy</span>
                                @elseif($order->status == 'returned')
                                <span class="badge bg-secondary">Trả hàng</span>
                                @elseif($order->status == 'failed_delivery')
                                <span class="badge bg-danger">Giao hàng thất bại</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                @endif
                            </p>
                            <p><strong>Phương thức thanh toán:</strong> {{ $order->payment_method }}</p>
                            <p><strong>Trạng thái thanh toán:</strong>
                                @if($order->payment_status == 'paid')
                                <span class="badge bg-success">Đã thanh toán</span>
                                @elseif($order->payment_status == 'pending')
                                <span class="badge bg-warning">Chờ thanh toán</span>
                                @elseif($order->payment_status == 'failed')
                                <span class="badge bg-danger">Thanh toán thất bại</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($order->payment_status) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phương thức vận chuyển:</strong> {{ $order->shipping_method ?? 'Chưa xác định' }}</p>
                            <p><strong>Phí vận chuyển:</strong> {{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</p>
                            <p><strong>Giảm giá:</strong> {{ number_format($order->discount_amount, 0, ',', '.') }} ₫</p>
                            <p><strong>Tổng tiền:</strong> <span class="text-danger fw-bold">{{ number_format($order->grand_total, 0, ',', '.') }} ₫</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Địa chỉ nhận hàng</h6>
                            <p><strong>Tên người nhận:</strong> {{ $order->customer_name }}</p>
                            <p><strong>SĐT của người nhận:</strong> {{ $order->customer_phone }}</p>
                            <p><strong>Địa chỉ chi tiết:</strong> {{ $order->shipping_address_line1 }}</p>
                            @if($order->shipping_address_line2)
                                <p>{{ $order->shipping_address_line2 }}</p>
                            @endif

                            <p>
                                <strong>Phường/Xã:</strong>
                                @if($order->shippingWard)
                                    {{ $order->shippingWard->name }}
                                @else
                                    {{ $order->shipping_ward_code }}
                                @endif
                            </p>

                            <p>
                                <strong>Tỉnh/Thành:</strong>
                                @if($order->shippingProvince)
                                    {{ $order->shippingProvince->name }}
                                @else
                                    {{ $order->shipping_province_code }}
                                @endif
                            </p>

                        </div>

                        @if($order->billing_address_line1)
                        <div class="col-md-6">
                            <h6>Địa chỉ thanh toán</h6>
                            <p><strong>Tên người thanh toán:</strong> {{ $order->customer_name }}</p>
                            <p><strong>Địa chỉ chi tiết:</strong> {{ $order->billing_address_line1 }}</p>
                            @if($order->billing_address_line2)
                            <p><strong>Địa chỉ:</strong> {{ $order->billing_address_line2 }}</p>
                        @endif

                        <p>
                            <strong>Phường/Xã:</strong>
                            @if($order->billingWard)
                                {{ $order->billingWard->name }}
                            @else
                                {{ $order->billing_ward_code }}
                            @endif
                        </p>

                        <p>
                            <strong>Tỉnh/Thành:</strong>
                            @if($order->billingProvince)
                                {{ $order->billingProvince->name }}
                            @else
                                {{ $order->billing_province_code }}
                            @endif
                        </p>

                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Sản phẩm đã đặt</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                    @if ($order->status === 'delivered')
                                    <td>Đánh giá</td>
                                    <td>Hoàn tiền</td>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->image_url)
                                            <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="img-thumbnail me-3" style="width: 60px;">
                                            @else
                                            
                                            <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                                <i class="fas fa-box-open text-muted"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $item->product_name }}</h6>
                                                <small class="text-muted">SKU: {{ $item->sku }}</small>
                                                @if(!empty($item->variant_attributes))
                                                <div class="mt-1">
                                                    @foreach($item->variant_attributes as $key => $value)
                                                    <small class="text-muted">{{ $key }}: {{ $value }}</small><br>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($item->price, 0, ',', '.') }} ₫</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->total_price, 0, ',', '.') }} ₫</td>
                                    <td>
                                        @if(
                                        $order->status === 'delivered' &&
                                        !$item->has_reviewed &&
                                        $item->product_variant_id &&
                                        $item->product_name
                                        )
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary write-review-btn"
                                            data-order-item-id="{{ $item->id }}"
                                            data-product-variant-id="{{ $item->product_variant_id }}"
                                            data-product-name="{{ $item->product_name }}">
                                            <i class="fas fa-star me-1"></i> Viết đánh giá
                                        </button>
                                        @elseif($item->has_reviewed)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle me-1"></i> Đã đánh giá
                                        </span>
                                        @else
                                        @endif

                                    </td>
                                    <td>
                                        @if ($order->status === 'delivered')
                                        @if ($item->returnItem)
                                        {{-- Nếu đã có phiếu trả hàng --}}
                                        <a href="{{ route('refunds.show', $item->returnItem->id) }}"
                                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                                            Xem chi tiết
                                        </a>
                                        @else
                                        {{-- Nếu chưa có phiếu trả hàng --}}
                                        <button class="open-return-modal bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition"
                                            data-name="{{ $item->product_name }}"
                                            data-sku="{{ 'SKU: '. $item->sku }}"
                                            data-image="{{ $item->image_url }}"
                                            data-price="{{ $item->price }}" {{-- Dạng số để JS tính toán --}}
                                            data-price-formatted="{{ number_format($item->price, 0, ',', '.') }} ₫" {{-- Dùng để hiển thị --}}
                                            data-order-item-id="{{ $item->id }}">
                                            Trả hàng
                                        </button>
                                        @endif
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tạm tính:</strong></td>
                                    <td>{{ number_format($order->sub_total, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Phí vận chuyển:</strong></td>
                                    <td>{{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Giảm giá:</strong></td>
                                    <td>-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                    <td class="text-danger fw-bold">{{ number_format($order->grand_total, 0, ',', '.') }} ₫</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($order->notes_from_customer)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ghi chú từ khách hàng</h5>
                </div>
                <div class="card-body">
                    <p>{{ $order->notes_from_customer }}</p>
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-between">
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>

                @if(in_array($order->status, ['pending_confirmation', 'processing', 'awaiting_shipment']))
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                    <i class="fas fa-times"></i> Hủy đơn hàng
                </button>
                @endif
            </div>

            <!-- Modal hủy đơn hàng -->
            <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelOrderModalLabel">Hủy đơn hàng</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p>Bạn có chắc chắn muốn hủy đơn hàng <strong>{{ $order->order_code }}</strong>?</p>
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Lý do hủy đơn</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="review-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 transition-opacity duration-300">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-transform duration-300 scale-95">
                    <div class="flex justify-between items-center p-4 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900" id="product-review-title">Viết đánh giá</h3>
                        <button id="close-review-modal-btn" class="text-gray-500 hover:text-gray-700 text-3xl leading-none">&times;</button>
                    </div>
                    <div class="p-6 space-y-4">
                        <input type="hidden" id="order_item_id">
                        <input type="hidden" id="product_variant_id">

                        <div>
                            <label class="font-semibold text-gray-700">Đánh giá của bạn</label>
                            <div id="review-stars-container" class="flex items-center gap-1 text-4xl mt-1">
                                <!-- stars render JS -->
                            </div>
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700">Bình luận</label>
                            <textarea id="review-text" class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" rows="4"></textarea>
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700">Thêm hình ảnh/video</label>
                            <input id="file-upload" name="media[]" type="file" accept="image/*,video/*" multiple class="form-control">
                            <div id="preview" class="mt-4 grid grid-cols-3 gap-4"></div>
                        </div>
                        <div class="text-right">
                            <button id="submit-review-btn" class="bg-blue-600 text-white font-semibold py-2 px-5 rounded-lg hover:bg-blue-700 transition-colors">Gửi đánh giá</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Modal Trả hàng -->
<div id="return-request-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex justify-center items-start overflow-auto">
    <div class="relative bg-white max-w-4xl w-full mt-10 mx-4 p-6 rounded-lg shadow-xl">
        <button id="close-return-modal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">×</button>
        <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">
            <!-- Tiêu đề -->
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Yêu cầu Trả hàng / Hoàn tiền</h1>
                <p class="text-gray-500 mt-2">Hoàn thành biểu mẫu dưới đây để gửi yêu cầu của bạn.</p>
            </div>

            <hr class="border-gray-200">

            <!-- Phần 1: Thông tin sản phẩm -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-700">1. Sản phẩm cần trả</h2>
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-6 border border-gray-200 rounded-lg p-4">
                    <img class="product-image w-24 h-24 object-cover rounded-md flex-shrink-0 border" src="..." ...>
                    <div class="flex-grow">
                        <p class="product-name font-bold text-lg text-gray-800"></p>
                        <p class="product-sku text-sm text-gray-500"></p>
                        <p class="product-price text-xl font-semibold text-red-600 mt-2"></p>
                    </div>
                </div>
            </div>

            <!-- Phần 2: Chi tiết yêu cầu -->
            <div class="space-y-6">
                <h2 class="text-xl font-semibold text-gray-700">2. Chi tiết yêu cầu</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Số lượng trả</label>
                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            value="1"
                            min="1"
                            max=""
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Nhập số lượng muốn trả">
                        <small id="quantity-note" class="text-xs text-gray-500 mt-1">Số lượng tối đa: <span id="max-qty-text">-</span></small>
                    </div>
                    <div>
                        <label for="return_reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do trả hàng</label>
                        <select id="return_reason" name="reason" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white">
                            <option value="">-- Chọn lý do --</option>
                            <option value="Sản phẩm bị lỗi do nhà sản xuất">Sản phẩm bị lỗi do nhà sản xuất</option>
                            <option value="Sản phẩm không đúng như mô tả">Sản phẩm không đúng như mô tả</option>
                            <option value="Giao sai sản phẩm">Giao sai sản phẩm</option>
                            <option value="Sản phẩm bị hư hỏng khi vận chuyển">Sản phẩm bị hư hỏng khi vận chuyển</option>
                            <option value="Thay đổi ý định">Thay đổi ý định (có thể áp dụng phí)</option>
                            <option value="Khác">Khác...</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="reason_details" class="block text-sm font-medium text-gray-700 mb-1">Mô tả chi tiết (nếu cần)</label>
                    <textarea id="reason_details" name="reason_details" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Vui lòng mô tả rõ hơn về tình trạng sản phẩm..."></textarea>
                </div>

                <!-- Chức năng tải lên hình ảnh/video -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hình ảnh/Video đính kèm (Tùy chọn)</label>
                    <div id="dropzone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md transition-colors duration-300">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="return-file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Tải lên tệp</span>
                                    <input id="return-file-upload" name="file-upload" type="file" class="sr-only" multiple>
                                </label>
                                <p class="pl-1">hoặc kéo và thả</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG tới 10MB; MP4 tới 50MB</p>
                        </div>
                    </div>
                    <div id="file-list-preview" class="mt-3 grid grid-cols-3 gap-4"></div>
                </div>
            </div>

            <!-- Phần 3: Phương thức hoàn tiền -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-700">3. Chọn phương thức hoàn tiền</h2>
                <div id="refund-options" class="space-y-3">

                    <!-- Lựa chọn 1: Điểm thưởng -->
                    <label for="refund-points" class="block border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition refund-option">
                        <div class="flex items-center">
                            <input type="radio" id="refund-points" name="refund_method" value="points" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <p class="font-semibold text-gray-800">Hoàn tiền bằng Điểm thưởng</p>
                                <p class="text-sm text-gray-500">Số điểm dự kiến được hoàn: <span id="expected-refund-points" class="font-bold text-green-600"></span>. Dùng để mua sắm cho lần sau.</p>
                            </div>
                        </div>
                    </label>

                    <!-- Lựa chọn 2: Chuyển khoản -->
                    <label for="refund-bank" class="block border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition refund-option">
                        <div class="flex items-center">
                            <input type="radio" id="refund-bank" name="refund_method" value="bank" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <p class="font-semibold text-gray-800">Hoàn tiền qua Chuyển khoản Ngân hàng</p>
                                <p class="text-sm text-gray-500">Nhận tiền trực tiếp vào tài khoản của bạn sau 2-3 ngày làm việc.</p>
                            </div>
                        </div>
                    </label>
                    <div id="bank-details" class="hidden ml-4 md:ml-8 mt-2 p-4 bg-gray-50 border border-dashed border-gray-300 rounded-lg space-y-3">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700">Tên ngân hàng</label>
                            <input type="text" id="bank_name" name="bank_name" class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500" placeholder="VD: Vietcombank">
                        </div>
                        <div>
                            <label for="bank_account_name" class="block text-sm font-medium text-gray-700">Tên chủ tài khoản</label>
                            <input type="text" id="bank_account_name" name="bank_account_name" class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500" placeholder="NGUYEN VAN A">
                        </div>
                        <div>
                            <label for="bank_account_number" class="block text-sm font-medium text-gray-700">Số tài khoản</label>
                            <input type="text" id="bank_account_number" name="bank_account_number" class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Lựa chọn 3: Mã giảm giá -->
                    <label for="refund-coupon" class="block border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition refund-option">
                        <div class="flex items-center">
                            <input type="radio" id="refund-coupon" name="refund_method" value="coupon" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <p class="font-semibold text-gray-800">Nhận Mã giảm giá</p>
                                <p class="text-sm text-gray-500">Bạn sẽ nhận được mã giảm giá trị giá <span class="product-price font-bold text-green-600"></span>, chỉ áp dụng một lần cho tài khoản này.</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Phần 4: Tóm tắt và Gửi -->
            <div class="border-t border-gray-200 pt-8 space-y-6">
                <div class="flex justify-between items-center">
                    <p class="text-lg font-semibold text-gray-700">Tổng tiền dự kiến hoàn:</p>
                    <p class="product-price text-2xl font-bold text-red-600"></p>
                </div>

                <!-- Điều khoản & Chính sách -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" name="terms" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="font-medium text-gray-700">Tôi đã đọc và đồng ý với <a href="#" class="text-blue-600 hover:underline">Chính sách Trả hàng & Hoàn tiền</a> của iMart.</label>
                    </div>
                </div>

                <!-- Ghi chú hướng dẫn -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">Sau khi yêu cầu được phê duyệt, chúng tôi sẽ gửi hướng dẫn chi tiết về địa chỉ nhận hàng qua email của bạn.</p>
                        </div>
                    </div>
                </div>

                <button id="submit-button" type="submit" class="w-full bg-red-600 text-white font-bold text-lg py-3 px-6 rounded-lg hover:bg-red-700 transition-all duration-300 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-red-300 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:shadow-none">
                    Gửi Yêu Cầu
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.selectedOrderItemId = null;
    document.addEventListener('DOMContentLoaded', () => {
        initReviewModal();
    });


    function initReviewModal() {
        const modal = document.getElementById('review-modal');
        const closeBtn = document.getElementById('close-review-modal-btn');
        const starsContainer = document.getElementById('review-stars-container');
        const submitBtn = document.getElementById('submit-review-btn');
        const reviewText = document.getElementById('review-text');
        const fileInput = document.getElementById('file-upload');
        let selectedRating = 0;

        // Gán sự kiện mở modal cho từng nút
        document.querySelectorAll('.write-review-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedRating = 0;
                reviewText.value = '';
                fileInput.value = '';

                const productName = btn.dataset.productName;
                const orderItemId = btn.dataset.orderItemId;
                const productVariantId = btn.dataset.productVariantId;

                document.getElementById('product-review-title').textContent = `Đánh giá: ${productName}`;
                document.getElementById('order_item_id').value = orderItemId;
                document.getElementById('product_variant_id').value = productVariantId;

                renderStars();
                showModal(modal);
            });
        });

        // Đóng modal
        closeBtn.addEventListener('click', () => hideModal(modal));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) hideModal(modal);
        });

        // Gửi đánh giá
        submitBtn.addEventListener('click', () => {
            const orderItemId = document.getElementById('order_item_id').value;
            const productVariantId = document.getElementById('product_variant_id').value;
            const comment = reviewText.value.trim();
            const files = fileInput.files;

            if (!selectedRating) {
                return toastr.warning('Vui lòng chọn số sao');
            }

            const formData = new FormData();
            formData.append('rating', selectedRating);
            formData.append('comment', comment);
            formData.append('product_variant_id', productVariantId);
            formData.append('order_item_id', orderItemId);

            for (let i = 0; i < files.length && i < 3; i++) {
                formData.append('media[]', files[i]);
            }

            fetch("{{ route('reviews.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(async res => {
                    const contentType = res.headers.get("content-type");
                    if (res.ok && contentType?.includes("application/json")) {
                        return res.json();
                    }
                    const text = await res.text();
                    throw new Error('Phản hồi không hợp lệ: ' + text);
                })
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message || 'Cảm ơn bạn đã đánh giá sản phẩm!');
                        hideModal(modal);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(data.message || 'Gửi đánh giá thất bại.');
                    }
                })
                .catch(err => {
                    toastr.error(err.message || 'Lỗi kết nối máy chủ.');
                });
        });

        function renderStars() {
            starsContainer.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                const star = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                star.setAttribute('class', 'review-star w-8 h-8 text-gray-300 cursor-pointer transition-colors');
                star.setAttribute('fill', 'currentColor');
                star.setAttribute('viewBox', '0 0 20 20');
                star.dataset.rating = i;
                star.innerHTML = `<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>`;
                starsContainer.appendChild(star);

                star.addEventListener('mouseover', () => {
                    document.querySelectorAll('.review-star').forEach(s => {
                        s.classList.toggle('text-yellow-400', s.dataset.rating <= star.dataset.rating);
                    });
                });
                star.addEventListener('mouseout', () => {
                    document.querySelectorAll('.review-star').forEach(s => {
                        s.classList.remove('text-yellow-400');
                        s.classList.add(s.dataset.rating <= selectedRating ? 'text-yellow-400' : 'text-gray-300');
                    });
                });
                star.addEventListener('click', () => {
                    selectedRating = parseInt(star.dataset.rating);
                    document.querySelectorAll('.review-star').forEach(s => {
                        s.classList.remove('text-yellow-400', 'text-gray-300');
                        s.classList.add(s.dataset.rating <= selectedRating ? 'text-yellow-400' : 'text-gray-300');
                    });
                });
            }
        }

        function showModal(modal) {
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('opacity-100', 'scale-100'), 10);
        }

        function hideModal(modal) {
            modal.classList.remove('opacity-100', 'scale-100');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    }
    let unitPrice = 0;
    let quantityInput = null;

    function updateRefundDisplay() {
        const qty = parseInt(quantityInput.value || 1);
        const total = unitPrice * qty;
        const expectedPoints = Math.floor(total / 1000);

        // Format giá VNĐ
        const formattedTotal = total.toLocaleString('vi-VN') + ' ₫';

        document.querySelectorAll('.product-price').forEach(el => {
            el.textContent = formattedTotal;
        });

        document.getElementById('expected-refund-points').textContent = expectedPoints.toLocaleString('vi-VN') + ' điểm';
    }


    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('return-request-modal');
        const closeBtn = document.getElementById('close-return-modal');
        const openBtns = document.querySelectorAll('.open-return-modal');

        // Các phần cần thay đổi
        const nameEl = modal.querySelector('.product-name'); // thêm class này vào thẻ tên
        const skuEl = modal.querySelector('.product-sku'); // thêm class này vào thẻ sku
        const priceEl = modal.querySelector('.product-price'); // thêm class này vào thẻ giá
        const imageEl = modal.querySelector('.product-image'); // thêm class này vào thẻ <img>
        openBtns.forEach(button => {
            button.addEventListener('click', () => {
                // Lấy dữ liệu từ data attribute
                const name = button.dataset.name;
                const sku = button.dataset.sku;
                const price = button.dataset.price;
                const priceFormatted = button.dataset.priceFormatted;
                const image = button.dataset.image;
                const maxQty = parseInt(button.dataset.max || '1');
                unitPrice = parseInt(price);
                quantityInput = document.getElementById('quantity')

                quantityInput.value = 1;
                quantityInput.max = maxQty;
                quantityInput.min = 1;
                updateRefundDisplay(); // Gọi tính toán lần đầu
                quantityInput.addEventListener('input', updateRefundDisplay);

                selectedOrderItemId = button.dataset.orderItemId
                // Gán vào modal
                nameEl.textContent = name;
                skuEl.textContent = sku;
                document.querySelectorAll('.product-price').forEach(el => {
                    el.textContent = priceFormatted;
                });
                imageEl.src = image;

                const refundAmount = parseInt(price.replace(/[^\d]/g, '') || '0');
                const expectedPoints = Math.floor(refundAmount / 1000);
                document.getElementById('expected-refund-points').textContent = expectedPoints.toLocaleString('vi-VN') + ' điểm';
                const input = document.getElementById('return-file-upload');
                const preview = document.getElementById('file-list-preview');

                if (input) {
                    input.addEventListener('change', function(e) {
                        console.log('File selected:', e.target.files);
                        preview.innerHTML = '';
                        const files = e.target.files;

                        Array.from(files).forEach(file => {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                const src = event.target.result;
                                let element;

                                if (file.type.startsWith('image/')) {
                                    element = document.createElement('img');
                                    element.src = src;
                                    element.className = "w-full h-32 object-cover rounded border";
                                } else if (file.type.startsWith('video/')) {
                                    element = document.createElement('video');
                                    element.src = src;
                                    element.controls = true;
                                    element.className = "w-full h-32 object-cover rounded border";
                                }

                                preview.appendChild(element);
                            };
                            reader.readAsDataURL(file);
                        });
                    });
                }

                // Hiện modal
                modal.classList.remove('hidden');
            });
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        const refundOptions = document.querySelectorAll('input[name="refund_method"]');
        const bankDetails = document.getElementById('bank-details');
        const submitButton = document.getElementById('submit-button');
        const fileUploadInput = document.getElementById('return-file-upload');
        const termsCheckbox = document.getElementById('terms');

        // Toggle hiển thị thông tin ngân hàng
        refundOptions.forEach(option => {
            option.addEventListener('change', function() {
                if (this.value === 'bank') {
                    bankDetails.classList.remove('hidden');
                } else {
                    bankDetails.classList.add('hidden');
                }
            });
        });

        // Submit form
        submitButton.addEventListener('click', () => {
            const refundMethod = document.querySelector('input[name="refund_method"]:checked')?.value;
            const quantity = document.getElementById('quantity').value;
            const reason = document.getElementById('return_reason').value;
            const reasonDetails = document.getElementById('reason_details').value;
            const bankName = document.getElementById('bank_name')?.value;
            const bankAccountName = document.getElementById('bank_account_name')?.value;
            const bankAccountNumber = document.getElementById('bank_account_number')?.value;
            const files = fileUploadInput.files;

            if (!refundMethod) {
                return toastr.warning('Vui lòng chọn phương thức hoàn tiền');
            }

            if (!termsCheckbox.checked) {
                return toastr.warning('Vui lòng đồng ý với chính sách hoàn tiền');
            }

            const formData = new FormData();
            formData.append('refund_method', refundMethod);
            formData.append('quantity', quantity);
            formData.append('reason', reason);
            formData.append('reason_details', reasonDetails);
            formData.append('order_item_id', selectedOrderItemId);


            if (refundMethod === 'bank') {
                if (!bankName || !bankAccountName || !bankAccountNumber) {
                    return toastr.warning('Vui lòng nhập đầy đủ thông tin ngân hàng');
                }
                formData.append('bank_name', bankName);
                formData.append('bank_account_name', bankAccountName);
                formData.append('bank_account_number', bankAccountNumber);
            }

            for (let i = 0; i < files.length && i < 5; i++) {
                formData.append('media[]', files[i]);
            }
            console.log([...formData.entries()]);

            fetch('/orders/refund-request', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json', // ✅ BẮT BUỘC
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(async res => {
                    if (!res.ok) {
                        const error = await res.text();
                        console.error('❌ Lỗi phản hồi:', error);
                        throw new Error('Phản hồi không hợp lệ');
                    }
                    return res.json();
                })
                .then(data => {
                    setTimeout(() => {
                        location.reload(); // 👉 Reload lại trang sau khi toastr hiển thị
                    }, 50);
                    console.log('✅ Thành công:', data);
                    // toastr.success(data.message);
                })
                .catch(error => {
                    console.error('❌ Lỗi:', error);
                    // toastr.error(error.message);
                });


        });
    });
</script>

@endsection
