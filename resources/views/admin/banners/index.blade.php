<!-- Trang index danh sách banner kết hợp giao diện Tailwind với dữ liệu Laravel thực + modal thêm/sửa -->
@extends('admin.layouts.app')

@section('title', 'Danh sách Banner')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Quản lý Banner</h1>
                <p class="text-gray-500 mt-1">Thêm mới và quản lý các banner quảng cáo trên trang web.</p>
            </div>
            <button onclick="openModal()" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Thêm banner mới</span>
            </button>
        </div>

        @if(session('success'))
        <div class="mb-4 text-green-600 font-semibold">
            {{ session('success') }}
        </div>
        @endif

        <div class="overflow-x-auto bg-white rounded-xl shadow-sm">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-4">Ảnh desktop</th>
                        <th class="px-6 py-4">Ảnh mobile</th>
                        <th class="px-6 py-4">Tiêu đề</th>
                        <th class="px-6 py-4">Thời gian hiệu lực</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="p-4 text-center">
                            @if($banner->desktopImage)
                            <img src="{{ Storage::url($banner->desktopImage->path) }}" alt="{{ $banner->title }}" class="h-16 w-auto rounded-md object-cover">
                            @else
                            <span class="text-gray-400 italic">Không có ảnh</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($banner->mobileImage)
                            <img src="{{ Storage::url($banner->mobileImage->path) }}" alt="{{ $banner->title }}" class="h-16 w-auto rounded-md object-cover">
                            @else
                            <span class="text-gray-400 italic">Không có ảnh</span>
                            @endif
                        </td>
                        <td class="p-6">
                            <div class="font-semibold text-gray-800">{{ $banner->title }}</div>
                            @if($banner->link_url)
                            <a href="{{ $banner->link_url }}" target="_blank" class="text-indigo-600 text-xs hover:underline">{{ $banner->link_url }}</a>
                            @endif
                        </td>
                        <td class="p-6">
                            <div>Từ: <strong>{{ $banner->start_date ? date('d/m/Y', strtotime($banner->start_date)) : '---' }}</strong></div>
                            <div>Đến: <strong>{{ $banner->end_date ? date('d/m/Y', strtotime($banner->end_date)) : '---' }}</strong></div>
                        </td>
                        <td class="p-6">
                            <span class="status-badge {{ $banner->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                {{ $banner->status === 'active' ? 'Hoạt động' : 'Tạm ẩn' }}
                            </span>
                        </td>
                        <td class="p-6 text-center">
                            <button onclick="editBanner({{ $banner->id }})" class="text-indigo-600 hover:text-indigo-900 text-lg" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn xoá banner này?')" class="text-red-600 hover:text-red-900 text-lg ml-4" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-500 p-8">Không có banner nào được tìm thấy.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if(method_exists($banners, 'links'))
            <div class="mt-6 px-4 pb-6">
                {{ $banners->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal thêm/sửa banner với preview ảnh và thời gian hiệu lực -->
<div id="banner-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-3xl">
        @csrf
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">Thêm banner mới</h2>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>
        <div class="p-8 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Liên kết (URL)</label>
                <input type="url" name="link_url" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active">Hiển thị</option>
                        <option value="inactive">Ẩn</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
                    <input type="date" name="start_date" id="start_date" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                    <input type="date" name="end_date" id="end_date" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh desktop</label>
                <img id="preview-desktop" class="w-48 mb-2 rounded border hidden">
                <input type="file" name="image_desktop" accept="image/*" class="w-full py-2 px-3 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh mobile</label>
                <img id="preview-mobile" class="w-32 mb-2 rounded border hidden">
                <input type="file" name="image_mobile" accept="image/*" class="w-full py-2 px-3 border border-gray-300 rounded-lg">
            </div>
        </div>
        <div class="p-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-2xl">
            <button type="button" onclick="closeModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Hủy</button>
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">Lưu banner</button>
        </div>
    </form>
</div>
<script>
    let banners = @json($bannerJson);
    let editingBanner = null;

    function openModal() {
        editingBanner = null;
        clearForm();
        document.querySelector('#banner-modal form').action = "{{ route('admin.banners.store') }}";
        document.querySelector('#banner-modal h2').innerText = 'Thêm banner mới';
        document.getElementById('banner-modal').classList.remove('hidden');
    }

    function editBanner(id) {
        const banner = banners.find(b => b.id === id);
        if (!banner) return;
        editingBanner = banner;
        clearForm();
        const form = document.querySelector('#banner-modal form');
        document.querySelector('#banner-modal h2').innerText = 'Chỉnh sửa banner';
        form.action = `/admin/banners/${id}`;

        if (!form.querySelector('input[name="_method"]')) {
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.prepend(methodInput);
        } else {
            form.querySelector('input[name="_method"]').value = 'PUT';
        }
        form.start_date.value = banner.start_date || '';
        form.end_date.value = banner.end_date || '';
        form.title.value = banner.title || '';
        form.link_url.value = banner.link_url || '';
        form.status.value = banner.status || '';
        form.start_date.value = banner.start_date || '';
        form.end_date.value = banner.end_date || '';

        const desktopImg = document.getElementById('preview-desktop');
        const mobileImg = document.getElementById('preview-mobile');
        desktopImg.classList.add('hidden');
        mobileImg.classList.add('hidden');

        if (banner.desktop_image?.path) {
            desktopImg.src = `/storage/${banner.desktop_image.path}`;
            desktopImg.classList.remove('hidden');
        }
        if (banner.mobile_image?.path) {
            mobileImg.src = `/storage/${banner.mobile_image.path}`;
            mobileImg.classList.remove('hidden');
        }

        document.getElementById('banner-modal').classList.remove('hidden');
    }

    function clearForm() {
        const form = document.querySelector('#banner-modal form');
        form.reset();
        form.removeAttribute('action');
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();
        document.getElementById('preview-desktop').classList.add('hidden');
        document.getElementById('preview-mobile').classList.add('hidden');
        document.getElementById('preview-desktop').src = '';
        document.getElementById('preview-mobile').src = '';
    }

    function closeModal() {
        document.getElementById('banner-modal').classList.add('hidden');
    }
</script>
@endsection