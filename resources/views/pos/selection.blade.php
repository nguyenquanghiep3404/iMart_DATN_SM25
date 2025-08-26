@extends('pos.layouts.pos')

@section('title', 'Chọn Cửa Hàng & Máy POS')

@push('styles')
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f0f2f5;
    }
    .selection-card {
        background-color: white;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .selection-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.05);
        border-color: #3b82f6;
    }
    .selection-card .icon {
        font-size: 2.5rem;
        color: #3b82f6;
        margin-bottom: 1rem;
    }
    .back-button {
        display: inline-flex;
        align-items: center;
        color: #4b5563;
        font-weight: 500;
        transition: color 0.2s;
    }
    .back-button:hover {
        color: #1d4ed8;
    }
</style>
@endpush

@section('content')
<div class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-4xl p-4">
        {{-- THÊM MỚI: Phần header mà JavaScript cần để hoạt động --}}
        <div id="header-content" class="text-center mb-8">
            {{-- JavaScript sẽ điền nội dung vào đây --}}
        </div>

        <div id="select-store-screen">
            <div id="store-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($stores as $store)
                    {{-- Sửa lại: Dùng data- attributes thay cho onclick --}}
                    <div class="selection-card" data-store-id="{{ $store->id }}" data-store-name="{{ $store->name }}">
                        <div class="icon"><i class="fas fa-store-alt"></i></div>
                        <h2 class="text-lg font-bold text-gray-800">{{ $store->name }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $store->address }}</p>
                    </div>
                @empty
                    <p class="text-center col-span-full text-gray-600">Bạn chưa được phân quyền cho bất kỳ cửa hàng nào.</p>
                @endforelse
            </div>
        </div>

        <div id="select-register-screen" class="hidden">
            <div id="register-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {{-- JavaScript sẽ điền danh sách máy POS vào đây --}}
            </div>
            <div id="loading-registers" class="hidden text-center text-gray-500 py-10">
                <i class="fas fa-spinner fa-spin text-2xl"></i>
                <p class="mt-2">Đang tải danh sách máy POS...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- DOM Elements ---
    const headerContentEl = document.getElementById('header-content');
    const selectStoreScreen = document.getElementById('select-store-screen');
    const selectRegisterScreen = document.getElementById('select-register-screen');
    const storeListContainer = document.getElementById('store-list');
    const registerListContainer = document.getElementById('register-list');
    const loadingRegisters = document.getElementById('loading-registers');
    
    // --- Functions ---
    function showScreen(screen) {
        selectStoreScreen.classList.add('hidden');
        selectRegisterScreen.classList.add('hidden');
        
        if (screen === 'store') {
            selectStoreScreen.classList.remove('hidden');
            updateHeader('store');
        } else if (screen === 'register') {
            selectRegisterScreen.classList.remove('hidden');
        }
    }
    
    function updateHeader(screen, context = {}) {
        if (screen === 'store') {
            headerContentEl.innerHTML = `
                <h1 class="text-3xl font-extrabold text-gray-800">Chọn Cửa Hàng</h1>
                <p class="text-gray-500 mt-1">Vui lòng chọn cửa hàng bạn đang làm việc.</p>
            `;
        } else if (screen === 'register') {
            headerContentEl.innerHTML = `
                <div class="text-center">
                    <button id="back-to-store-btn" class="back-button mb-4">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại Chọn Cửa Hàng
                    </button>
                    <h1 class="text-3xl font-extrabold text-gray-800">Chọn Máy POS</h1>
                    <p class="text-gray-500 mt-1">Bạn đang chọn máy cho cửa hàng: <span class="font-bold">${context.storeName}</span></p>
                </div>
            `;
            // Gắn sự kiện cho nút quay lại vừa được tạo
            document.getElementById('back-to-store-btn').addEventListener('click', () => showScreen('store'));
        }
    }

    async function handleStoreSelection(storeId, storeName) {
        showScreen('register');
        updateHeader('register', { storeName });
        loadingRegisters.classList.remove('hidden');
        registerListContainer.innerHTML = '';
        
        const url = `{{ url('/pos/stores') }}/${storeId}/registers`;

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response failed');
            const registers = await response.json();

            loadingRegisters.classList.add('hidden');

            if (!registers || registers.length === 0) {
                registerListContainer.innerHTML = `<p class="text-center col-span-full text-gray-600">Cửa hàng này chưa có máy POS nào được thiết lập.</p>`;
                return;
            }

            registers.forEach(register => {
                const manageUrl = `{{ url('/pos/session/manage') }}?register_id=${register.id}`;
                const statusClass = register.is_active ? 'opacity-60 cursor-not-allowed' : 'hover:border-blue-500 hover:shadow-lg';
                const statusDotClass = register.is_active ? 'bg-red-500' : 'bg-green-500';
                const statusText = register.is_active ? `Đang dùng bởi ${register.active_user}` : 'Sẵn sàng';
                const statusTextColor = register.is_active ? 'text-red-600' : 'text-green-600';

                const cardHtml = `
                    <div class="selection-card p-4 ${statusClass}" data-url="${manageUrl}" ${register.is_active ? 'disabled' : ''}>
                        <div class="icon"><i class="fas fa-cash-register"></i></div>
                        <h2 class="text-lg font-bold text-gray-800">${register.name}</h2>
                        <div class="flex items-center justify-center mt-2 text-xs font-medium ${statusTextColor}">
                            <span class="h-2 w-2 rounded-full ${statusDotClass} mr-2"></span>
                            <span>${statusText}</span>
                        </div>
                    </div>
                `;
                registerListContainer.insertAdjacentHTML('beforeend', cardHtml);
            });

        } catch (error) {
            console.error('Error fetching registers:', error);
            loadingRegisters.classList.add('hidden');
            registerListContainer.innerHTML = `<p class="text-center col-span-full text-red-500">Đã xảy ra lỗi khi tải danh sách máy POS.</p>`;
        }
    }

    // --- Event Delegation ---
    storeListContainer.addEventListener('click', function(e) {
        const card = e.target.closest('.selection-card');
        if (card && card.dataset.storeId) {
            handleStoreSelection(card.dataset.storeId, card.dataset.storeName);
        }
    });

    registerListContainer.addEventListener('click', function(e) {
        const card = e.target.closest('.selection-card');
        if (card && card.dataset.url && !card.hasAttribute('disabled')) {
            window.location.href = card.dataset.url;
        }
    });
    
    // --- Initial Load ---
    showScreen('store');
});
</script>
@endpush