@php($disableMainCss = true)
@extends('admin.layouts.app')
@section('content')
    @include('admin.marketing_campaigns.layouts.css')

    <body class="antialiased text-slate-700">

        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-800">
                    {{ isset($campaign) ? 'Chi tiết / Sửa Chiến Dịch' : 'Tạo Chiến Dịch Mới' }}
                </h1>
                <p class="mt-1 text-slate-500">Tiếp cận đúng khách hàng với thông điệp phù hợp.</p>
            </div>

            <!-- Main Content Grid -->
            <form id="campaignForm">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Left Column: Content Editor -->
                    <div class="lg:col-span-2 space-y-8">
                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="space-y-2">
                                <label for="campaignName" class="text-base font-semibold text-slate-800">Tên chiến
                                    dịch</label>
                                <p class="text-sm text-slate-500">Tên nội bộ để bạn dễ dàng quản lý, khách hàng sẽ không
                                    thấy tên này.</p>
                                <input type="text" id="campaignName" disabled name="campaignName"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                                    placeholder="Ví dụ: Chiến dịch khuyến mãi tháng 7"
                                    value="{{ old('campaignName', $campaign->name ?? '') }}" />
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label for="emailSubject" class="text-base font-semibold text-slate-800">Tiêu đề
                                        Email</label>
                                    <input type="text" disabled id="emailSubject" name="emailSubject"
                                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                                        placeholder="Ví dụ: Ưu đãi độc quyền dành riêng cho bạn!"
                                        value="{{ old('emailSubject', $campaign->email_subject ?? '') }}" />
                                </div>
                                <div class="space-y-2">
                                    <label for="emailContent" class="text-base font-semibold text-slate-800">Nội dung
                                        Email</label>
                                    <textarea id="emailContent"disabled name="emailContent" rows="12"
                                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">{{ old('emailContent', $campaign->email_content ?? 'Chào Imart xin gửi tặng bạn ưu đãi đặc biệt...') }}</textarea>
                                    <p class="text-xs text-slate-500">Sử dụng các biến như để cá nhân hóa email.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Settings -->
                    <div class="lg:col-span-1 space-y-8">
                        <div class="bg-white p-6 rounded-xl shadow-lg space-y-6">
                            <h3 class="text-base font-semibold text-slate-800 border-b pb-4">Thiết Lập Chiến Dịch</h3>

                            <div class="space-y-2">
                                <label for="targetGroup" class="flex items-center text-sm font-medium text-slate-600">
                                    <i data-lucide="users" class="mr-2 w-5 h-5"></i>
                                    Đối tượng mục tiêu
                                </label>
                                @if ($customerGroup)
                                    <input type="text" value="{{ $customerGroup->name }}"
                                        class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700 cursor-not-allowed"
                                        readonly>
                                    <input type="hidden" name="customer_group_id" value="{{ $customerGroup->id }}">
                                @else
                                    <p class="text-red-500">Chưa có nhóm khách hàng</p>
                                @endif
                            </div>


                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-medium text-slate-600">
                                    <i data-lucide="send" class="mr-2 w-4 h-4"></i>
                                    Kênh gửi
                                </label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" name="campaignType" value="email"
                                            class="w-4 h-4 text-purple-600 focus:ring-purple-500"
                                            {{ old('campaignType', $campaign->type ?? 'email') == 'email' ? 'checked' : '' }} />
                                        <span class="text-sm">Email</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-not-allowed opacity-50">
                                        <input type="radio" name="campaignType" value="sms" disabled class="w-4 h-4" />
                                        <span class="text-sm">In-app</span>
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-3 p-4 bg-white rounded-md shadow-sm border border-gray-200">
                                <label for="selectedCoupon"
                                    class="flex items-center text-sm font-semibold text-slate-700 mb-1">
                                    <i data-lucide="ticket" class="mr-2 w-5 h-5 text-purple-600"></i>
                                    Đính kèm Coupon
                                </label>
                                <div class="text-gray-700 text-base">
                                    @if ($coupon)
                                        <span class="font-medium text-purple-700">{{ $coupon->code }}</span> -
                                        <span class="italic text-gray-600">{{ $coupon->description }}</span>
                                    @else
                                        <span class="text-gray-400">Không đính kèm coupon</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-8 flex justify-between items-center">
                    <a href="{{ route('admin.marketing_campaigns.index') }}"
                        class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg shadow-sm
                              hover:bg-indigo-600 hover:text-white transition-colors duration-300 font-semibold select-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 -ml-1" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Quay lại danh sách
                    </a>

                    <div class="flex space-x-4">
                        <button type="submit" id="sendCampaignBtn"
                            class="flex items-center justify-center px-6 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg shadow-md hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-300 transition-all duration-200">
                            <i data-lucide="send-horizontal" class="w-5 h-5 mr-2"></i>
                            Gửi Chiến Dịch
                        </button>
                    </div>
                </div>

            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const campaignId = "{{ $campaign->id ?? '' }}";
        </script>
        <script>
            // --- Render Lucide Icons ---
            lucide.createIcons();

            // --- JavaScript Logic ---
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('campaignForm');
                const saveDraftBtn = document.getElementById('saveDraftBtn');

                // Function to gather form data
                const getCampaignData = () => {
                    const formData = new FormData(form);
                    const data = {
                        name: formData.get('campaignName'),
                        subject: formData.get('emailSubject'),
                        content: formData.get('emailContent'),
                        customer_group_id: formData.get('customer_group_id'),
                        type: formData.get('campaignType'),
                        coupon_id: formData.get('coupon_id'),
                    };
                    return data;
                };

                // Xử lý submit form (Gửi chiến dịch)
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    if (!campaignId) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: 'Campaign ID không hợp lệ hoặc không tồn tại.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // Hiển thị hộp thoại xác nhận trước khi gửi
                    Swal.fire({
                        title: 'Bạn có chắc muốn gửi chiến dịch này?',
                        text: "Hành động này sẽ gửi email đến tất cả khách hàng trong nhóm!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Có, gửi đi!',
                        cancelButtonText: 'Hủy bỏ',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const campaignData = getCampaignData();
                            // Hiển thị loading
                            Swal.fire({
                                title: 'Đang gửi chiến dịch...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                }
                            });
                            fetch(`/admin/marketing_campaigns/${campaignId}/send`, {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "Accept": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                    },
                                    body: JSON.stringify({
                                        ...campaignData,
                                        status: "sent"
                                    })
                                })
                                .then(async res => {
                                    if (!res.ok) {
                                        const errorData = await res.json();
                                        if (res.status === 422 && errorData.errors) {
                                            const messages = Object.values(errorData.errors)
                                                .flat()
                                                .map(msg => `<p>${msg}</p>`)
                                                .join('');
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Lỗi xác thực',
                                                html: messages,
                                            });
                                        } else {
                                            throw new Error(errorData.message ||
                                                'Có lỗi xảy ra.');
                                        }
                                        return;
                                    }
                                    return res.json();
                                })
                                .then(data => {
                                    if (!data) return;
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Đã gửi chiến dịch',
                                        text: data.message ||
                                            'Chiến dịch đã được gửi thành công.',
                                    }).then(() => {
                                        window.location.href =
                                            "{{ route('admin.marketing_campaigns.index') }}";
                                    });
                                })
                                .catch(err => {
                                    console.error(err);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Lỗi hệ thống',
                                        text: err.message || 'Vui lòng thử lại.',
                                    });
                                });
                        }
                    });
                });
            });
        </script>
    @endsection
