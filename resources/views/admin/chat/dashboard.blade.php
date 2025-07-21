<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung Tâm Hỗ Trợ Live Chat</title>
    {{-- Laravel CSRF token và Echo/Pusher scripts --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.x.x/dist/echo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.x.x/dist/pusher.min.js"></script>
    <style>
        /* CSS gốc từ giaodienchatadmin.html */
        :root {
            --bg-color: #f4f6f9;
            --panel-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --accent-color: #6f42c1;
            --hover-bg: #f8f9fa;
            --sent-bg: #6f42c1;
            --received-bg: #e9ecef;
            --font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        }

        html, body {
            height: 100%;
            margin: 0;
            font-family: var(--font-family);
            background-color: var(--bg-color);
            overflow: hidden;
        }

        .app-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .main-header {
            height: 60px;
            background-color: var(--panel-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            padding: 0 24px;
            flex-shrink: 0;
        }

        .main-header .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .main-header .logo svg {
            width: 28px;
            height: 28px;
            color: var(--accent-color);
        }

        .chat-dashboard {
            display: flex;
            height: calc(100vh - 60px);
            width: 100vw;
        }

        /* --- Cột 1: Danh sách hội thoại --- */
        .conversation-list-panel {
            width: 320px;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            background-color: var(--panel-bg);
            flex-shrink: 0;
        }

        .conversation-list-panel .header {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .conversation-list-panel .search-bar {
            width: 100%;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 14px;
            box-sizing: border-box;
        }

        /* CẬP NHẬT: Thêm style cho thanh Tabs mới */
        .conversation-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            padding: 0 16px;
        }
        .conversation-tabs .tab-link {
            padding: 14px 8px;
            margin-right: 16px;
            border: none;
            background-color: transparent;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
        }
        .conversation-tabs .tab-link.active {
            color: var(--accent-color);
            border-bottom-color: var(--accent-color);
        }

        .conversation-list-container {
            overflow-y: auto;
            flex-grow: 1;
        }

        /* CẬP NHẬT: Style cho các Pane của Tab */
        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
        }

        .conversation-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .conversation-item {
            display: flex;
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }

        .conversation-item:hover {
            background-color: var(--hover-bg);
        }

        .conversation-item.active {
            background-color: #f1e9ff;
            border-right: 3px solid var(--accent-color);
        }

        .conversation-item .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #ced4da;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-secondary);
            flex-shrink: 0;
        }

        .conversation-item .avatar svg {
            width: 24px;
            height: 24px;
            color: var(--text-secondary);
        }

        .conversation-item .details {
            flex-grow: 1;
            overflow: hidden;
        }

        .conversation-item .name {
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .conversation-item .last-message {
            font-size: 14px;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 4px 0 0;
        }

        /* --- Cột 2: Cửa sổ Chat --- */
        .chat-panel {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #fdfbff;
            opacity: 0.8;
            background-image:  radial-gradient(var(--accent-color) 0.5px, transparent 0.5px), radial-gradient(var(--accent-color) 0.5px, #fdfbff 0.5px);
            background-size: 20px 20px;
            background-position: 0 0,10px 10px;
        }

        .chat-panel .header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--panel-bg);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-panel .header .user-name {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .chat-panel .message-container {
            flex-grow: 1;
            padding: 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .message {
            display: flex;
            gap: 12px;
            max-width: 70%;
        }

        .message .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #adb5bd;
            flex-shrink: 0;
        }

        .message .content {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 15px;
            line-height: 1.5;
        }

        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        .message.sent .content {
            background-color: var(--sent-bg);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.received {
            align-self: flex-start;
        }
        .message.received .content {
            background-color: var(--received-bg);
            color: var(--text-primary);
            border-bottom-left-radius: 4px;
        }

        .chat-panel .input-area {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            background-color: var(--panel-bg);
            display: flex;
            gap: 16px;
        }

        .chat-panel .input-area .text-input {
            flex-grow: 1;
            padding: 12px 18px;
            border: 1px solid #ced4da;
            border-radius: 24px;
            font-size: 15px;
            outline: none;
        }

        .chat-panel .input-area .send-button {
            padding: 0 24px;
            border: none;
            background-color: var(--accent-color);
            color: white;
            border-radius: 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        /* --- Cột 3: Thông tin khách hàng --- */
        .info-panel {
            width: 350px;
            border-left: 1px solid var(--border-color);
            background-color: var(--panel-bg);
            padding: 24px;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .info-panel .section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-top: 0;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }

        .info-panel .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .info-panel .info-item svg {
            width: 18px;
            height: 18px;
            color: var(--text-secondary);
        }

        .order-history-list .order-item {
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .order-history-list .order-id {
            font-weight: 600;
        }
        .order-history-list .order-details {
            font-size: 13px;
            color: var(--text-secondary);
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <div class="app-container">
        <header class="main-header">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                </svg>
                <span>iMart Support</span>
            </div>
        </header>

        <div class="chat-dashboard">

            <aside class="conversation-list-panel">
                <div class="header">
                    <input type="text" class="search-bar" placeholder="Tìm kiếm hội thoại...">
                    <button id="createInternalChatBtn" style="margin-top: 10px; padding: 8px 12px; background-color: var(--accent-color); color: white; border: none; border-radius: 5px; cursor: pointer;">Tạo Chat Nội Bộ Mới</button>
                </div>

                <div class="conversation-tabs">
                    <button class="tab-link active" data-target="#customer-chats">Khách hàng</button>
                    <button class="tab-link" data-target="#internal-chats">Nội bộ</button>
                </div>

                <div class="conversation-list-container">

                    <div id="customer-chats" class="tab-pane active">
                        <ul class="conversation-list" id="customerConversationsList">
                            @forelse($openSupportConversations as $conversation)
                                <li class="conversation-item" data-conversation-id="{{ $conversation->id }}" data-type="support">
                                    <div class="avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    </div>
                                    <div class="details">
                                        <p class="name">{{ $conversation->user->name ?? 'Khách vãng lai' }}</p>
                                        <p class="last-message">{{ $conversation->messages->last()->content ?? 'Chưa có tin nhắn' }}</p>
                                    </div>
                                </li>
                            @empty
                                <li style="padding: 12px 16px; color: var(--text-secondary);">Không có cuộc trò chuyện hỗ trợ đang mở.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div id="internal-chats" class="tab-pane">
                        <ul class="conversation-list" id="internalConversationsList">
                            @forelse($internalConversations as $conversation)
                                <li class="conversation-item" data-conversation-id="{{ $conversation->id }}" data-type="internal">
                                    <div class="avatar">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.963A3.75 3.75 0 1012 6v2.75m-3.75 0A3.75 3.75 0 016 10.5v.75a3.75 3.75 0 01-7.5 0v-.75A3.75 3.75 0 016 6.75v2.75m0 0v-.25m0 0c0-.966.784-1.75 1.75-1.75h.5c.966 0 1.75.784 1.75 1.75v.25m0 0c0 .966-.784 1.75-1.75 1.75h-.5A1.75 1.75 0 016 13.25v-.25m0 0c0-1.105.895-2 2-2h.5c1.105 0 2 .895 2 2v.25m-2.5 0c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m-4.5 0c.23.02.428.037.601.055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25m2.5 0c-.69-.02-.823-.037-1-.055a3.75 3.75 0 01-3.44-3.44c-.018-.177-.035-.31-.055-.478m-1.5 5.25c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25" /></svg>
                                    </div>
                                    <div class="details">
                                        <p class="name">{{ $conversation->subject ?? 'Trò chuyện nội bộ' }}</p>
                                        <p class="last-message">{{ $conversation->messages->last()->content ?? 'Chưa có tin nhắn' }}</p>
                                    </div>
                                </li>
                            @empty
                                <li style="padding: 12px 16px; color: var(--text-secondary);">Không có cuộc trò chuyện nội bộ nào.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </aside>

            <main class="chat-panel" id="chatPanel">
                <div class="header">
                    <h3 class="user-name" id="chatUserName"></h3>
                    <button id="closeConversationBtn" style="margin-left: auto; padding: 5px 10px; background-color: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; display: none;">Đóng Hội Thoại</button>
                    <button id="inviteAdminBtn" style="margin-left: 10px; padding: 5px 10px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; display: none;">Mời Admin Khác</button>
                </div>
                <div class="message-container" id="messageContainer">
                    <p style="text-align: center; color: var(--text-secondary); margin-top: 20px;">Chọn một cuộc hội thoại để bắt đầu chat.</p>
                </div>
                <div class="input-area" style="display: none;" id="inputArea">
                    <input type="text" class="text-input" id="messageInput" placeholder="Nhập tin nhắn...">
                    <button class="send-button" id="sendMessageBtn">Gửi</button>
                </div>
            </main>

            <aside class="info-panel" id="infoPanel">
                <h3 class="section-title">Thông tin</h3>
                <div class="info-list" id="customerInfoList">
                    {{-- Thông tin khách hàng sẽ được tải động tại đây --}}
                    <p style="color: var(--text-secondary);">Chọn một cuộc hội thoại để xem thông tin chi tiết.</p>
                </div>

                <h3 class="section-title" style="margin-top: 32px;">Lịch sử mua hàng</h3>
                <div class="order-history-list" id="orderHistoryList">
                    {{-- Lịch sử mua hàng sẽ được tải động tại đây --}}
                </div>
            </aside>
        </div>
    </div>

    {{-- Modal Mời Admin --}}
    <div id="inviteAdminModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 1001;">
        <h4>Mời Admin Khác Tham Gia</h4>
        <select id="adminSelect" style="width: 100%; padding: 8px; margin-bottom: 15px;">
            @foreach($admins as $admin)
                @if($admin->id !== Auth::id())
                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                @endif
            @endforeach
        </select>
        <button id="confirmInviteBtn" style="padding: 8px 15px; background-color: #6f42c1; color: white; border: none; border-radius: 5px; cursor: pointer;">Mời</button>
        <button id="cancelInviteBtn" style="padding: 8px 15px; background-color: #ccc; color: black; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">Hủy</button>
    </div>

    {{-- Modal Tạo Chat Nội Bộ Mới --}}
    <div id="createInternalChatModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 1001;">
        <h4>Tạo Chat Nội Bộ Mới</h4>
        <form id="internalChatForm">
            <div style="margin-bottom: 15px;">
                <label for="internalChatSubject">Chủ đề (Tùy chọn):</label><br>
                <input type="text" id="internalChatSubject" name="subject" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="internalChatRecipients">Người nhận (Chọn nhiều):</label><br>
                <select id="internalChatRecipients" multiple style="width: 100%; height: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    @foreach($admins as $admin)
                        @if($admin->id !== Auth::id())
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom: 15px;">
                <label for="firstMessage">Tin nhắn đầu tiên:</label><br>
                <textarea id="firstMessage" name="first_message" required style="width: 100%; height: 80px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
            </div>
            <button type="submit" style="padding: 8px 15px; background-color: #6f42c1; color: white; border: none; border-radius: 5px; cursor: pointer;">Tạo Chat</button>
            <button type="button" id="cancelCreateInternalChatBtn" style="padding: 8px 15px; background-color: #ccc; color: black; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">Hủy</button>
        </form>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Biến Laravel Blade cho ID người dùng hiện tại
            const AUTH_ID = {{ Auth::id() }};

            // Các phần tử DOM chính
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabPanes = document.querySelectorAll('.tab-pane');
            const customerConversationsList = document.getElementById('customerConversationsList');
            const internalConversationsList = document.getElementById('internalConversationsList');
            const chatPanel = document.getElementById('chatPanel');
            const chatUserName = document.getElementById('chatUserName');
            const messageContainer = document.getElementById('messageContainer');
            const inputArea = document.getElementById('inputArea');
            const messageInput = document.getElementById('messageInput');
            const sendMessageBtn = document.getElementById('sendMessageBtn');
            const infoPanel = document.getElementById('infoPanel');
            const customerInfoList = document.getElementById('customerInfoList');
            const orderHistoryList = document.getElementById('orderHistoryList');
            const closeConversationBtn = document.getElementById('closeConversationBtn');
            const inviteAdminBtn = document.getElementById('inviteAdminBtn');
            const createInternalChatBtn = document.getElementById('createInternalChatBtn');

            // Modal Mời Admin
            const inviteAdminModal = document.getElementById('inviteAdminModal');
            const adminSelect = document.getElementById('adminSelect');
            const confirmInviteBtn = document.getElementById('confirmInviteBtn');
            const cancelInviteBtn = document.getElementById('cancelInviteBtn');

            // Modal Tạo Chat Nội Bộ
            const createInternalChatModal = document.getElementById('createInternalChatModal');
            const internalChatForm = document.getElementById('internalChatForm');
            const internalChatSubject = document.getElementById('internalChatSubject');
            const internalChatRecipients = document.getElementById('internalChatRecipients');
            const firstMessage = document.getElementById('firstMessage');
            const cancelCreateInternalChatBtn = document.getElementById('cancelCreateInternalChatBtn');


            let activeConversationId = null;
            let activeConversationType = null; // 'support' hoặc 'internal'

            // --- Cấu hình Laravel Echo ---
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: '{{ config('reverb.apps.0.key') }}',
                wsHost: window.location.hostname,
                wsPort: {{ config('reverb.port') }},
                wssPort: {{ config('reverb.port') }},
                forceTLS: false,
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                },
            });

            // --- Hàm hiển thị tin nhắn vào khung chat ---
            function displayMessage(messageData) {
                const messageElement = document.createElement('div');
                messageElement.classList.add('message');

                // Xác định loại tin nhắn (gửi đi hay nhận về)
                messageElement.classList.add(messageData.sender_id == AUTH_ID ? 'sent' : 'received');

                // Tạo avatar (có thể tùy chỉnh thêm nếu có ảnh đại diện)
                const avatarContent = messageData.sender && messageData.sender.name ? messageData.sender.name.charAt(0).toUpperCase() : (messageData.sender_id == AUTH_ID ? 'Bạn' : 'KH');
                const avatar = `<div class="avatar">${avatarContent}</div>`;

                messageElement.innerHTML = `
                    ${avatar}
                    <div class="content">
                        ${messageData.content}
                    </div>
                `;
                messageContainer.appendChild(messageElement);
                messageContainer.scrollTop = messageContainer.scrollHeight; // Cuộn xuống dưới cùng
            }

            // --- Hàm tải dữ liệu cuộc hội thoại và hiển thị ---
            async function loadConversation(conversationId, type) {
                activeConversationId = conversationId;
                activeConversationType = type;

                // Xóa trạng thái active của tất cả các item và thêm vào item được chọn
                document.querySelectorAll('.conversation-item').forEach(item => item.classList.remove('active'));
                document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`).classList.add('active');

                // Xóa tin nhắn cũ và thông tin cũ
                messageContainer.innerHTML = '';
                customerInfoList.innerHTML = '';
                orderHistoryList.innerHTML = '';
                inputArea.style.display = 'flex'; // Hiển thị khung nhập tin nhắn

                try {
                    const response = await fetch(`/admin/chat/${conversationId}`);
                    const data = await response.json();
                    const conversation = data.conversation;

                    chatUserName.textContent = conversation.user ? conversation.user.name : (conversation.subject || 'Trò chuyện nội bộ');
                    closeConversationBtn.style.display = 'block'; // Luôn hiển thị nút đóng

                    // Hiển thị thông tin khách hàng/người tham gia
                    if (conversation.type === 'support') {
                        inviteAdminBtn.style.display = 'block'; // Chỉ hiển thị nút mời admin cho chat support
                        customerInfoList.innerHTML = `
                            <div class="info-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                <span>${conversation.user.name || 'Khách vãng lai'}</span>
                            </div>
                            <div class="info-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                                <span>${conversation.user.email || 'N/A'}</span>
                            </div>
                            <div class="info-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 6.75z" /></svg>
                                <span>${conversation.user.phone_number || 'N/A'}</span>
                            </div>
                            <div class="info-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                <span>Giao cho: ${conversation.assigned_to ? conversation.assigned_to.name : 'Chưa giao'}</span>
                            </div>
                        `;
                        // Bạn sẽ cần một API hoặc mối quan hệ để lấy Order History của User
                        // For now, it's static as in your HTML
                        orderHistoryList.innerHTML = `
                            <div class="order-item">
                                <div class="order-id">Đơn hàng #12345</div>
                                <div class="order-details">
                                    <span>15/07/2025</span>
                                    <span>25.990.000₫</span>
                                </div>
                            </div>
                            <div class="order-item">
                                <div class="order-id">Đơn hàng #11987</div>
                                <div class="order-details">
                                    <span>02/06/2025</span>
                                    <span>1.550.000₫</span>
                                </div>
                            </div>
                        `;
                    } else { // internal chat
                        inviteAdminBtn.style.display = 'block'; // Vẫn cho phép mời admin khác vào nhóm nội bộ
                        customerInfoList.innerHTML = `
                             <div class="info-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.963A3.75 3.75 0 1012 6v2.75m-3.75 0A3.75 3.75 0 016 10.5v.75a3.75 3.75 0 01-7.5 0v-.75A3.75 3.75 0 016 6.75v2.75m0 0v-.25m0 0c0-.966.784-1.75 1.75-1.75h.5c.966 0 1.75.784 1.75 1.75v.25m0 0c0 .966-.784 1.75-1.75 1.75h-.5A1.75 1.75 0 016 13.25v-.25m0 0c0-1.105.895-2 2-2h.5c1.105 0 2 .895 2 2v.25m-2.5 0c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m-4.5 0c.23.02.428.037.601.055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25m2.5 0c-.69-.02-.823-.037-1-.055a3.75 3.75 0 01-3.44-3.44c-.018-.177-.035-.31-.055-.478m-1.5 5.25c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25" /></svg>
                                <span>Chủ đề: ${conversation.subject || 'Trò chuyện nội bộ'}</span>
                            </div>
                            <div class="info-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.963A3.75 3.75 0 1012 6v2.75m-3.75 0A3.75 3.75 0 016 10.5v.75a3.75 3.75 0 01-7.5 0v-.75A3.75 3.75 0 016 6.75v2.75m0 0v-.25m0 0c0-.966.784-1.75 1.75-1.75h.5c.966 0 1.75.784 1.75 1.75v.25m0 0c0 .966-.784 1.75-1.75 1.75h-.5A1.75 1.75 0 016 13.25v-.25m0 0c0-1.105.895-2 2-2h.5c1.105 0 2 .895 2 2v.25m-2.5 0c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m-4.5 0c.23.02.428.037.601.055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25m2.5 0c-.69-.02-.823-.037-1-.055a3.75 3.75 0 01-3.44-3.44c-.018-.177-.035-.31-.055-.478m-1.5 5.25c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25" /></svg>
                                <span>Người tạo: ${conversation.assigned_to ? conversation.assigned_to.name : 'N/A'}</span>
                            </div>
                            <div class="info-item">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l-3-3m0 0l-3 3m3-3v7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Tham gia: ${conversation.participants.map(p => p.user.name).join(', ')}</span>
                            </div>
                        `;
                        orderHistoryList.innerHTML = '<p style="color: var(--text-secondary);">Không có lịch sử mua hàng cho cuộc trò chuyện nội bộ.</p>';
                    }

                    // Hiển thị tin nhắn
                    conversation.messages.forEach(message => {
                        displayMessage(message);
                    });

                    messageContainer.scrollTop = messageContainer.scrollHeight; // Cuộn xuống dưới cùng

                    // Lắng nghe kênh riêng tư cho cuộc hội thoại này
                    window.Echo.private(`chat.conversation.${activeConversationId}`)
                        .listen('.message.sent', (e) => {
                            // Chỉ hiển thị tin nhắn nếu nó thuộc cuộc hội thoại đang mở
                            if (e.conversation.id == activeConversationId) {
                                displayMessage(e.message);
                            }
                        });

                } catch (error) {
                    console.error('Lỗi khi tải cuộc hội thoại:', error);
                    alert('Không thể tải cuộc hội thoại.');
                    inputArea.style.display = 'none'; // Ẩn khung nhập nếu có lỗi
                }
            }

            // --- Gắn sự kiện cho các item hội thoại ---
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.addEventListener('click', () => {
                    const conversationId = item.dataset.conversationId;
                    const type = item.dataset.type;
                    loadConversation(conversationId, type);
                });
            });

            // --- Gắn sự kiện cho nút Gửi tin nhắn ---
            sendMessageBtn.addEventListener('click', async () => {
                const content = messageInput.value.trim();
                if (content === '' || !activeConversationId) return;

                try {
                    const response = await fetch(`/admin/chat/${activeConversationId}/send-message`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ content: content })
                    });
                    const data = await response.json();
                    if (response.ok) {
                        messageInput.value = ''; // Xóa nội dung input
                        // Tin nhắn sẽ được hiển thị qua sự kiện broadcast, không cần gọi displayMessage trực tiếp
                    } else {
                        alert(data.message || 'Lỗi khi gửi tin nhắn.');
                    }
                } catch (error) {
                    console.error('Lỗi:', error);
                    alert('Đã xảy ra lỗi khi gửi tin nhắn.');
                }
            });

            // Gửi tin nhắn khi nhấn Enter trong input
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    sendMessageBtn.click();
                }
            });

            // --- Lắng nghe sự kiện Broadcast từ Laravel ---
            // Lắng nghe kênh chung cho các thông báo quản trị viên (ví dụ: có chat hỗ trợ mới)
            window.Echo.channel('admin.notifications')
                .listen('.conversation.created', (e) => {
                    // Nếu là cuộc hội thoại hỗ trợ khách hàng mới
                    if (e.conversation.type === 'support') {
                        const newConversationItem = document.createElement('li');
                        newConversationItem.classList.add('conversation-item');
                        newConversationItem.dataset.conversationId = e.conversation.id;
                        newConversationItem.dataset.type = 'support';
                        newConversationItem.innerHTML = `
                            <div class="avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            </div>
                            <div class="details">
                                <p class="name">${e.conversation.user.name || 'Khách vãng lai'}</p>
                                <p class="last-message">${e.conversation.messages && e.conversation.messages.length > 0 ? e.conversation.messages[e.conversation.messages.length - 1].content : 'Chưa có tin nhắn'}</p>
                            </div>
                        `;
                        newConversationItem.addEventListener('click', () => loadConversation(e.conversation.id, 'support'));
                        customerConversationsList.prepend(newConversationItem); // Thêm vào đầu danh sách
                        alert('Có cuộc trò chuyện hỗ trợ mới từ ' + (e.conversation.user.name || 'khách vãng lai') + '!');
                    }
                });

            // Lắng nghe kênh riêng tư của admin để nhận thông báo về chat nội bộ
            window.Echo.private(`users.${AUTH_ID}`)
                .listen('.conversation.created', (e) => {
                    // Nếu là cuộc hội thoại nội bộ và admin hiện tại là người nhận
                    if (e.conversation.type === 'internal') {
                         const newConversationItem = document.createElement('li');
                        newConversationItem.classList.add('conversation-item');
                        newConversationItem.dataset.conversationId = e.conversation.id;
                        newConversationItem.dataset.type = 'internal';
                        newConversationItem.innerHTML = `
                            <div class="avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.963A3.75 3.75 0 1012 6v2.75m-3.75 0A3.75 3.75 0 016 10.5v.75a3.75 3.75 0 01-7.5 0v-.75A3.75 3.75 0 016 6.75v2.75m0 0v-.25m0 0c0-.966.784-1.75 1.75-1.75h.5c.966 0 1.75.784 1.75 1.75v.25m0 0c0 .966-.784 1.75-1.75 1.75h-.5A1.75 1.75 0 016 13.25v-.25m0 0c0-1.105.895-2 2-2h.5c1.105 0 2 .895 2 2v.25m-2.5 0c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m-4.5 0c.23.02.428.037.601.055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25m2.5 0c-.69-.02-.823-.037-1-.055a3.75 3.75 0 01-3.44-3.44c-.018-.177-.035-.31-.055-.478m-1.5 5.25c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25" /></svg>
                            </div>
                            <div class="details">
                                <p class="name">${e.conversation.subject || 'Trò chuyện nội bộ'}</p>
                                <p class="last-message">${e.conversation.messages && e.conversation.messages.length > 0 ? e.conversation.messages[e.conversation.messages.length - 1].content : 'Chưa có tin nhắn'}</p>
                            </div>
                        `;
                        newConversationItem.addEventListener('click', () => loadConversation(e.conversation.id, 'internal'));
                        internalConversationsList.prepend(newConversationItem); // Thêm vào đầu danh sách
                        alert('Bạn có một cuộc trò chuyện nội bộ mới với chủ đề: ' + (e.conversation.subject || 'Trò chuyện nội bộ') + '!');
                    }
                });

            // --- Logic cho các Tabs (Khách hàng / Nội bộ) ---
            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    tabLinks.forEach(l => l.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));

                    this.classList.add('active');
                    const targetPane = document.querySelector(this.dataset.target);
                    if (targetPane) {
                        targetPane.classList.add('active');
                    }
                });
            });

            // --- Xử lý nút Đóng Hội Thoại ---
            closeConversationBtn.addEventListener('click', async () => {
                if (!activeConversationId) return;

                if (confirm('Bạn có chắc chắn muốn đóng cuộc hội thoại này không?')) {
                    try {
                        const response = await fetch(`/admin/chat/${activeConversationId}/close`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const data = await response.json();
                        if (response.ok) {
                            alert(data.message);
                            // Xóa cuộc hội thoại khỏi danh sách
                            document.querySelector(`.conversation-item[data-conversation-id="${activeConversationId}"]`).remove();
                            // Reset giao diện chat
                            activeConversationId = null;
                            activeConversationType = null;
                            chatUserName.textContent = '';
                            messageContainer.innerHTML = '<p style="text-align: center; color: var(--text-secondary); margin-top: 20px;">Chọn một cuộc hội thoại để bắt đầu chat.</p>';
                            customerInfoList.innerHTML = '<p style="color: var(--text-secondary);">Chọn một cuộc hội thoại để xem thông tin chi tiết.</p>';
                            orderHistoryList.innerHTML = '';
                            inputArea.style.display = 'none';
                            closeConversationBtn.style.display = 'none';
                            inviteAdminBtn.style.display = 'none';
                        } else {
                            alert(data.message || 'Lỗi khi đóng cuộc hội thoại.');
                        }
                    } catch (error) {
                        console.error('Lỗi:', error);
                        alert('Đã xảy ra lỗi khi đóng cuộc hội thoại.');
                    }
                }
            });

            // --- Logic Modal Mời Admin ---
            inviteAdminBtn.addEventListener('click', () => {
                if (activeConversationType === 'internal') {
                    alert('Bạn có thể mời admin khác vào nhóm chat nội bộ qua tính năng tạo chat nội bộ mới.');
                    return;
                }
                inviteAdminModal.style.display = 'block';
            });

            confirmInviteBtn.addEventListener('click', async () => {
                const adminId = adminSelect.value;
                if (!activeConversationId || !adminId) return;

                try {
                    const response = await fetch(`/admin/chat/${activeConversationId}/invite-admin`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ admin_id: adminId })
                    });
                    const data = await response.json();
                    if (response.ok) {
                        alert(data.message);
                        inviteAdminModal.style.display = 'none';
                        // Có thể cần tải lại thông tin cuộc hội thoại để cập nhật danh sách người tham gia
                        // loadConversation(activeConversationId, activeConversationType);
                    } else {
                        alert(data.message || 'Lỗi khi mời admin.');
                    }
                } catch (error) {
                    console.error('Lỗi:', error);
                    alert('Đã xảy ra lỗi khi mời admin.');
                }
            });

            cancelInviteBtn.addEventListener('click', () => {
                inviteAdminModal.style.display = 'none';
            });

            // --- Logic Modal Tạo Chat Nội Bộ Mới ---
            createInternalChatBtn.addEventListener('click', () => {
                createInternalChatModal.style.display = 'block';
            });

            internalChatForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const subject = internalChatSubject.value.trim();
                const recipients = Array.from(internalChatRecipients.selectedOptions).map(option => option.value);
                const firstMsg = firstMessage.value.trim();

                if (recipients.length === 0 || firstMsg === '') {
                    alert('Vui lòng chọn người nhận và nhập tin nhắn.');
                    return;
                }

                try {
                    const response = await fetch('/admin/chat/create-internal', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            subject: subject,
                            recipient_ids: recipients,
                            first_message: firstMsg
                        })
                    });
                    const data = await response.json();
                    if (response.ok) {
                        alert(data.message);
                        createInternalChatModal.style.display = 'none';
                        internalChatForm.reset(); // Reset form
                        // Tải lại toàn bộ danh sách hội thoại để hiển thị chat mới
                        location.reload();
                    } else {
                        alert(data.message || 'Lỗi khi tạo chat nội bộ.');
                    }
                } catch (error) {
                    console.error('Lỗi:', error);
                    alert('Đã xảy ra lỗi khi tạo chat nội bộ.');
                }
            });

            cancelCreateInternalChatBtn.addEventListener('click', () => {
                createInternalChatModal.style.display = 'none';
                internalChatForm.reset();
            });

            // Tự động tải cuộc hội thoại đầu tiên nếu có khi trang tải
            @if(count($openSupportConversations) > 0)
                loadConversation("{{ $openSupportConversations->first()->id }}", "support");
            @elseif(count($internalConversations) > 0)
                loadConversation("{{ $internalConversations->first()->id }}", "internal");
            @endif

        });
    </script>
</body>
</html>
