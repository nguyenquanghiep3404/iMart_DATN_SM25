@extends('users.layouts.profile')

@section('content')
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f9fafb;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        transition: background-color 0.2s, color 0.2s;
        color: #374151;
    }

    .sidebar-link:hover {
        background-color: #f3f4f6;
    }

    .sidebar-link.active {
        background-color: #fee2e2;
        color: #dc2626;
        font-weight: 600;
    }

    .interactive-card {
        border: 2px solid #e5e7eb;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .interactive-card.selected {
        border-color: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1);
    }
</style>

<!-- Main Content -->
<main class="w-3/4 xl:w-4/5">
    <div class="mb-6">
        <a href="{{ route('orders.show', $order->id) }}" class="text-sm font-medium text-gray-600 hover:text-red-600 transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Quay lại chi tiết đơn hàng
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <header class="p-6 border-b">
            <h1 class="text-xl font-bold text-gray-900">Tạo Yêu Cầu Trả Hàng</h1>
            <p class="text-sm text-gray-500 mt-1">Đối với đơn hàng <a href="#" class="font-medium text-red-600 hover:underline">#DH987654321</a></p>
        </header>

        <form id="return-request-form" method="POST" action="{{ route('refunds.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="order-id" value="{{ $order->id }}">
            <div class="p-6 md:p-8 space-y-8">
                <!-- Step 1: Select Products -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">1. Chọn sản phẩm cần trả</h2>
                    <div class="space-y-4">
                        <!-- Product 1 -->
                        @foreach ($orderItems as $item)
                        <div class="interactive-card rounded-lg p-4">
                            <div class="flex items-start space-x-4">
                                <input type="checkbox" name="order_item_ids[]" value="{{ $item->id }}" class="h-5 w-5 mt-1 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <img src="{{ optional($item->variant->product->coverImage)->url ?? 'https://placehold.co/80x80' }}" alt="Ảnh sản phẩm" class="w-20 h-20 rounded-md object-cover flex-shrink-0">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800">{{ $item->variant->product->name }}</p>
                                    <p class="text-sm text-gray-500">Biến thể: {{ $item->variant->name }}</p>
                                    <p class="text-sm text-gray-500">Giá: {{ number_format($item->price) }} VNĐ</p>
                                </div>
                                <div class="w-24">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                    <input type="number" name="quantities[{{ $item->id }}]" value="{{ $item->quantity }}" min="1" max="{{ $item->quantity }}" class="w-full border-gray-300 rounded-md shadow-sm text-center" disabled>
                                </div>
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>

                <!-- Step 2 & 3: Details Section (hidden by default) -->
                <div id="return-details-section" class="hidden">
                    <hr>
                    <h2 class="text-xl font-semibold text-gray-800 my-6">2. Lý do trả hàng</h2>
                    <div class="space-y-6">
                        <div>
                            <label for="return-reason" class="block text-base font-medium text-gray-800 mb-2">Lý do trả hàng</label>
                            <select id="return-reason" name="reason" class="mt-1 block w-full text-base p-3 border-2 border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500" required>
                                <option value="">-- Chọn lý do --</option>
                                <option value="defective">Sản phẩm bị lỗi do nhà sản xuất</option>
                                <option value="wrong_item">Giao sai sản phẩm</option>
                                <option value="not_as_described">Không đúng như mô tả</option>
                                <option value="changed_mind">Thay đổi ý định (có thể áp dụng phí)</option>
                                <option value="other">Lý do khác</option>
                            </select>
                        </div>
                        <div>
                            <label for="reason_details" class="block text-sm font-medium text-gray-700 mb-1">Mô tả chi tiết (nếu cần)</label>
                            <textarea id="reason_details" name="reason_details" rows="3" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Vui lòng mô tả rõ hơn về tình trạng sản phẩm..."></textarea>
                        </div>
                        <div>
                            <label class="block text-base font-medium text-gray-800 mb-2">Tải lên hình ảnh/video</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none">
                                            <span>Tải lên một file</span>
                                            <input id="file-upload" name="media[]" type="file" class="sr-only" multiple>
                                        </label>
                                        <p class="pl-1">hoặc kéo và thả</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, MP4 tối đa 10MB</p>
                                </div>
                            </div>
                            <div id="file-list" class="mt-2 text-sm text-gray-600"></div>
                        </div>
                    </div>

                    <hr class="mt-8">
                    <h2 class="text-xl font-semibold text-gray-800 my-6">3. Chọn phương thức hoàn tiền</h2>
                    <div id="refund-options" class="space-y-3">
                        <!-- Lựa chọn 1: Điểm thưởng -->
                        <label for="refund-points" class="block border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition refund-option">
                            <div class="flex items-center">
                                <input type="radio" id="refund-points" name="refund_method" value="points" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-800">Hoàn tiền bằng Điểm thưởng</p>
                                    <p class="text-sm text-gray-500">Số điểm dự kiến được hoàn: <span id="expected-points" class="font-bold text-green-600">0 điểm</span>. Dùng để mua sắm cho lần sau.</p>
                                </div>
                            </div>
                        </label>


                        <label for="refund-bank" class="interactive-card block rounded-lg p-4 cursor-pointer">
                            <div class="flex items-center">
                                <input type="radio" id="refund-bank" name="refund_method" value="bank" class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-800">Hoàn tiền qua Chuyển khoản Ngân hàng</p>
                                    <p class="text-sm text-gray-500">Nhận tiền trực tiếp vào tài khoản của bạn sau 2-3 ngày làm việc.</p>
                                </div>
                            </div>
                        </label>
                        <div id="bank-details" class="hidden ml-8 mt-2 p-4 bg-gray-50 border border-dashed border-gray-300 rounded-lg space-y-3">
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700">Tên ngân hàng</label>
                                <input type="text" id="bank_name" name="bank_name" class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-red-500" placeholder="VD: Vietcombank">
                            </div>
                            <div>
                                <label for="bank_account_name" class="block text-sm font-medium text-gray-700">Tên chủ tài khoản</label>
                                <input type="text" id="bank_account_name" name="bank_account_name" class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-red-500" placeholder="NGUYEN VAN A">
                            </div>
                            <div>
                                <label for="bank_account_number" class="block text-sm font-medium text-gray-700">Số tài khoản</label>
                                <input type="text" id="bank_account_number" name="bank_account_number" class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-red-500">
                            </div>
                        </div>
                    </div>

                    <label for="refund-coupon" class="block border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition refund-option">
                        <div class="flex items-center">
                            <input type="radio" id="refund-coupon" name="refund_method" value="coupon" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <p class="font-semibold text-gray-800">Nhận Mã giảm giá</p>
                                <p class="text-sm text-gray-500">Bạn sẽ nhận được mã giảm giá trị giá <span id="refund-total-coupon" class="font-bold text-green-600">0 VNĐ</span>, chỉ áp dụng một lần cho tài khoản này.</p>
                            </div>
                        </div>
                    </label>

                    <hr class="mt-8">
                    <h2 class="text-xl font-semibold text-gray-800 my-6">4. Tóm tắt hoàn tiền</h2>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tổng giá trị sản phẩm trả:</span>
                                <span id="refund-subtotal" class="font-medium text-gray-800">0 VNĐ</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t mt-2">
                                <span class="text-base font-bold text-gray-900">Số tiền dự kiến hoàn lại:</span>
                                <span id="refund-total" class="text-xl font-bold text-green-600">0 VNĐ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-gray-50">
                <div class="flex items-start mb-4">
                    <div class="flex items-center h-5">
                        <input id="terms" name="terms" type="checkbox" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="font-medium text-gray-700">Tôi đã đọc và đồng ý với <a href="#" class="text-red-600 hover:underline">Chính sách Trả hàng & Hoàn tiền</a> của cửa hàng.</label>
                    </div>
                </div>
                <button type="submit" id="submit-return-btn" class="w-full bg-red-600 text-white font-bold text-lg py-3 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Gửi yêu cầu
                </button>
            </div>
        </form>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productCheckboxes = document.querySelectorAll('input[name="order_item_ids[]"]');
        const returnDetailsSection = document.getElementById('return-details-section');
        const submitBtn = document.getElementById('submit-return-btn');
        const refundSubtotalEl = document.getElementById('refund-subtotal');
        const refundTotalEl = document.getElementById('refund-total');
        const refundRadios = document.querySelectorAll('input[name="refund_method"]');
        const bankDetailsDiv = document.getElementById('bank-details');
        document.querySelectorAll('label.interactive-card[for^="refund-"]').forEach(div => {
            div.classList.remove('selected');
        });
        const refundOptionDivs = document.querySelectorAll('.interactive-card[for^="refund-"]');
        const fileUploadInput = document.getElementById('file-upload');
        const fileListDiv = document.getElementById('file-list');
        const termsCheckbox = document.getElementById('terms');

        const productData = @json(
        $orderItems->mapWithKeys(function($item) {
            return [$item->id => [
                'price' => $item->price,
                'maxQty' => $item->quantity
            ]];
        })
         );

        function updateFormState() {
            let anySelected = false;
            let totalRefund = 0;

            productCheckboxes.forEach(checkbox => {
                const card = checkbox.closest('.interactive-card');
                const quantityInput = card.querySelector(`input[name="quantities[${checkbox.value}]"]`);

                if (checkbox.checked) {
                    anySelected = true;
                    card.classList.add('selected');
                    quantityInput.disabled = false;

                    const productId = checkbox.value;
                    const quantity = parseInt(quantityInput.value, 10);
                    const price = productData[productId].price;
                    totalRefund += price * quantity;
                } else {
                    card.classList.remove('selected');
                    quantityInput.disabled = true;
                }
            });

            if (anySelected) {
                returnDetailsSection.classList.remove('hidden');
            } else {
                returnDetailsSection.classList.add('hidden');
            }

            const formattedTotal = new Intl.NumberFormat('vi-VN').format(totalRefund) + ' VNĐ';
            refundSubtotalEl.textContent = formattedTotal;
            refundTotalEl.textContent = formattedTotal;

            refundTotalEl.textContent = formattedTotal;

            // Nếu có phần tử hiển thị mã giảm giá, cập nhật luôn
            const refundTotalCouponEl = document.getElementById('refund-total-coupon');
            if (refundTotalCouponEl) {
                refundTotalCouponEl.textContent = formattedTotal;
            }


            // ✅ Tính và hiển thị số điểm
            const points = Math.floor(totalRefund);
            document.getElementById('expected-points').textContent = points.toLocaleString('vi-VN') + ' điểm';

            toggleSubmitButton();
        }


        function updateRefundSelection() {
            document.querySelectorAll('label.interactive-card[for^="refund-"]').forEach(div => {
                div.classList.remove('selected');
            });
            const selectedRadio = document.querySelector('input[name="refund_method"]:checked');
            if (selectedRadio) {
                selectedRadio.closest('label').classList.add('selected');
                bankDetailsDiv.classList.toggle('hidden', selectedRadio.value !== 'bank');
            }
        }

        function toggleSubmitButton() {
            const anyProductSelected = Array.from(productCheckboxes).some(cb => cb.checked);
            submitBtn.disabled = !termsCheckbox.checked || !anyProductSelected;
        }

        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateFormState);
            const quantityInput = checkbox.closest('.interactive-card').querySelector('input[type="number"]');
            quantityInput.addEventListener('input', updateFormState);
        });

        refundRadios.forEach(radio => {
            radio.addEventListener('change', updateRefundSelection);
        });

        termsCheckbox.addEventListener('change', toggleSubmitButton);

        fileUploadInput.addEventListener('change', (event) => {
            fileListDiv.innerHTML = '';
            const files = event.target.files;
            if (files.length > 0) {
                const list = document.createElement('ul');
                list.className = 'list-disc list-inside space-y-1';
                for (let i = 0; i < files.length; i++) {
                    const li = document.createElement('li');
                    li.textContent = files[i].name;
                    list.appendChild(li);
                }
                fileListDiv.appendChild(list);
            }
        });

        // Initial state
        updateFormState();
        updateRefundSelection();
    });
</script>
@endsection