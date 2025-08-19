<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung Tâm Hỗ Trợ Live Chat</title>
    {{-- Laravel CSRF token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tải CSS và JS chính của Vite (bao gồm bootstrap.js và admin_chat.js) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- marked.js để render Markdown --}}
    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.0/marked.min.js"></script>

    {{-- 
        Định nghĩa các biến toàn cục cho JavaScript.
        Lưu ý: Đảm bảo Controller của bạn luôn truyền vào một Collection (kể cả rỗng) thay vì null.
    --}}
    <script>
        window.ADMIN_CHAT_AUTH_ID = {{ Auth::id() ?? 'null' }};

        // ✅ SỬA LỖI: Cú pháp @json($variable ?? []) an toàn hơn.
        window.ADMIN_CHAT_SUPPORT_CONVERSATIONS = @json($openSupportConversations ?? []);
        window.ADMIN_CHAT_INTERNAL_CONVERSATIONS = @json($internalConversations ?? []);
        window.ADMIN_CHAT_ADMINS_DATA = @json($admins ?? []);

        console.log('Blade variables initialized for admin_chat.js');
    </script>

    {{-- CSS của bạn không thay đổi, giữ nguyên --}}
    <style>
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
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
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
        .conversation-item:hover { background-color: var(--hover-bg); }
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
        .chat-panel {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #fdfbff;
            opacity: 0.8;
            background-image: radial-gradient(var(--accent-color) 0.5px, transparent 0.5px), radial-gradient(var(--accent-color) 0.5px, #fdfbff 0.5px);
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
        .message.received { align-self: flex-start; }
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
        .order-history-list .order-id { font-weight: 600; }
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
                                        {{-- ✅ SỬA LỖI N+1: Sử dụng "latestMessage" thay vì "messages->last()" --}}
                                        {{-- Lưu ý: Bạn cần định nghĩa quan hệ latestMessage trong Model ChatConversation --}}
                                        <p class="last-message">{{ $conversation->latestMessage->content ?? 'Chưa có tin nhắn' }}</p>
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
                                        {{-- ✅ SỬA LỖI N+1: Sử dụng "latestMessage" thay vì "messages->last()" --}}
                                        <p class="last-message">{{ $conversation->latestMessage->content ?? 'Chưa có tin nhắn' }}</p>
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
            {{-- Options sẽ được điền bằng JavaScript từ window.ADMIN_CHAT_ADMINS_DATA --}}
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
                    {{-- Options sẽ được điền bằng JavaScript từ window.ADMIN_CHAT_ADMINS_DATA --}}
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
</body>
</html>