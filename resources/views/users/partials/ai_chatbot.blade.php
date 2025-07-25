<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hỗ trợ trực tuyến</title>

    {{-- Laravel CSRF token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Fonts và Tailwind CSS CDN (tạm thời, nên dùng Tailwind qua Vite trong production) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <style>
        /* --- Cài đặt chung và Biến màu (Theme Glassmorphism) --- */
        :root {
            --primary-color: #000000;
            --background-color: #ffffff;
            --text-dark: #000000;
            --text-light: #ffffff;
            --admin-message-bg: rgba(241, 241, 241, 0.8);
            --user-message-bg: #222222;
            --border-color: rgba(255, 255, 255, 0.2);
            --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            font-family: var(--font-family);
            margin: 0;
            background-color: var(--background-color);
        }

        * {
            box-sizing: border-box;
        }

        /* --- Icon Chat nổi --- */
        .chat-bubble {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 999;
            transition: transform 0.2s ease-in-out, opacity 0.3s ease-out, visibility 0.3s ease-out;
        }

        .chat-bubble:hover {
            transform: scale(1.1);
        }

        .chat-bubble svg {
            width: 32px;
            height: 32px;
            fill: white;
        }

        /* --- Khung Modal Chat (Hiệu ứng gương) --- */
        .chat-modal {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 370px;
            max-width: 90vw;
            height: 600px;
            max-height: 85vh;
            background: rgba(255, 255, 255, 0.5); /* Tăng độ mờ nền */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1000;
            transform: translateY(20px) scale(0.95);
            transform-origin: bottom right;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .chat-modal.show {
            transform: translateY(0) scale(1);
            opacity: 1;
            visibility: visible;
        }

        /* --- Header của Modal --- */
        .modal-header {
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .header-title svg {
            width: 28px;
            height: 28px;
            fill: var(--text-light);
        }

        .modal-header .close-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0 5px; line-height: 1; opacity: 0.8; }
        .modal-header .close-btn:hover { opacity: 1; }

        /* --- Màn hình chào mừng --- */
        .welcome-screen {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 30px;
            height: 100%;
            text-align: center;
        }

        .welcome-screen h4 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .welcome-screen p {
            margin-top: 0;
            margin-bottom: 25px;
            color: #333;
            font-size: 0.95rem;
        }

        .welcome-form .form-group {
            margin-bottom: 15px;
            width: 100%;
        }

        .welcome-form input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            background-color: rgba(255, 255, 255, 0.7);
        }

        .welcome-form .start-chat-btn {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: none;
            background-color: var(--primary-color);
            color: var(--text-light);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .welcome-form .start-chat-btn:hover {
            background-color: #333;
        }

        /* Giao diện chat chính */
        .main-chat-interface {
            display: none;
            flex-direction: column;
            flex-grow: 1;
            overflow: hidden;
        }

        /* --- Thanh Tabs --- */
        .modal-tabs {
            display: flex;
            background-color: rgba(255, 255, 255, 0.2);
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .tab-button {
            flex: 1;
            padding: 12px 15px;
            border: none;
            background-color: transparent;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            color: rgba(0, 0, 0, 0.6);
            transition: all 0.2s ease;
            border-bottom: 3px solid transparent;
        }

        .tab-button.active {
            color: var(--text-dark);
            border-bottom-color: var(--primary-color);
        }

        .tab-button:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* --- Nội dung các Tab --- */
        .tab-content {
            display: none;
            flex-grow: 1;
            flex-direction: column;
            overflow: hidden;
        }

        .tab-content.active {
            display: flex;
        }

        .modal-body {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
            background-color: transparent;
        }

        .message {
            display: flex;
            flex-direction: column;
            max-width: 80%;
        }

        .message .content {
            padding: 10px 15px;
            border-radius: 18px;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .message .timestamp {
            font-size: 0.75rem;
            color: #555;
            margin-top: 5px;
        }

        .message.received { align-self: flex-start; }
        .message.received .content { background-color: var(--admin-message-bg); color: var(--text-dark); border-top-left-radius: 4px; }
        .message.received .timestamp { margin-left: 5px; text-align: left; }

        .message.sent { align-self: flex-end; }
        .message.sent .content { background-color: var(--user-message-bg); color: var(--text-light); border-top-right-radius: 4px; }
        .message.sent .timestamp { margin-right: 5px; text-align: right; }

        .message.ai .content { background-color: var(--admin-message-bg); color: var(--text-dark); }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .modal-footer input {
            flex-grow: 1;
            border: 1px solid #ccc;
            border-radius: 20px;
            padding: 10px 15px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
            background-color: rgba(255, 255, 255, 0.5);
        }

        .modal-footer input:focus { border-color: var(--primary-color); }
        .modal-footer input::placeholder { color: #555; }

        .modal-footer .send-btn {
            background-color: var(--primary-color);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .modal-footer .send-btn:hover { background-color: #333; }
        .modal-footer .send-btn svg { width: 20px; height: 20px; fill: white; transform: translateX(1px); }

    </style>

    {{-- Các biến Blade được truyền vào đối tượng window để JavaScript có thể truy cập --}}
    {{-- Đây là cách an toàn để truyền dữ liệu từ PHP sang JS khi dùng Vite --}}
    <script>
        window.APP_AUTH_ID = {{ Auth::check() ? Auth::id() : 'null' }};
        window.APP_GUEST_USER_ID = {{ Illuminate\Support\Js::from($guestUserId ?? null) }};
        window.APP_CONVERSATIONS_DATA = {{ Illuminate\Support\Js::from($conversations ?? collect()) }};
        // Log để debug trong console (có thể xóa sau khi mọi thứ hoạt động)
        console.log('APP_AUTH_ID:', window.APP_AUTH_ID);
        console.log('APP_GUEST_USER_ID:', window.APP_GUEST_USER_ID);
        console.log('APP_CONVERSATIONS_DATA:', window.APP_CONVERSATIONS_DATA);
    </script>

    {{-- Bao gồm các tài nguyên được Vite xử lý (CSS và JavaScript chính của bạn) --}}
    {{-- Đảm bảo bạn đã chạy "npm install" và "npm run dev" --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <div class="chat-bubble" id="chatBubble">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
    </div>

    <div class="chat-modal" id="chatModal">
        <div class="modal-header">
            <div class="header-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM256 416c-20.5 0-37.1-16.6-37.1-37.1c0-20.5 16.6-37.1 37.1-37.1s37.1 16.6 37.1 37.1c0 20.5-16.6 37.1-37.1 37.1zM309.1 128.4C301.9 119.2 291.1 114 279.1 114h-47.1c-11.9 0-22.8 5.2-30.1 14.4c-7.3 9.2-11.2 20.7-11.2 32.5c0 10.9 3.5 21.4 10.1 29.8l34.4 43.1c4.8 6 7.6 13.4 7.6 21.2v11.1h-69.4c-11.1 0-20.1 9-20.1 20.1s9 20.1 20.1 20.1h89.6c11.1 0 20.1-9 20.1-20.1v-23.4c0-12.8-5.1-24.9-14.1-33.9l-34.4-43.1c-4.8-6-7.6-13.4-7.6-21.2c0-4.4 1.8-8.6 4.9-11.7c3.1-3.1 7.4-4.9 11.7-4.9h47.1c4.3 0 8.5 1.8 11.7 4.9c3.1 3.1 4.9 7.4 4.9 11.7c0 11.1-9 20.1-20.1 20.1h-11.1c-11.1 0-20.1 9-20.1 20.1s9 20.1 20.1 20.1h11.1c33.2 0 60.2-27 60.2-60.2c0-13.8-4.7-27-13.2-37.6z"/></svg>
                <span>Hỗ trợ trực tuyến</span>
            </div>
            <button class="close-btn" id="closeModal">&times;</button>
        </div>

        <div class="welcome-screen" id="welcomeScreen">
             <h4>Chào mừng bạn!</h4>
             <p>Vui lòng để lại thông tin để chúng tôi có thể hỗ trợ bạn tốt nhất.</p>
             <form class="welcome-form" id="welcomeForm">
                <div class="form-group">
                    <input type="text" id="guestName" placeholder="Tên của bạn" required>
                </div>
                <div class="form-group">
                    <input type="tel" id="guestPhone" placeholder="Số điện thoại" required>
                </div>
                <button type="submit" class="start-chat-btn">Bắt đầu chat</button>
             </form>
        </div>

        <div class="main-chat-interface" id="mainChatInterface">
            <div class="modal-tabs">
                <button class="tab-button active" data-target="#humanChat">
                    <span style="font-size: 1.2em; vertical-align: middle;">👤</span> Chat với nhân viên
                </button>
                <button class="tab-button" data-target="#aiChat">
                    <span style="font-size: 1.2em; vertical-align: middle;">🤖</span> Chat với AI
                </button>
            </div>

            <div class="tab-content active" id="humanChat">
                <div class="modal-body" id="humanChatBody">
                    {{-- Các tin nhắn sẽ được tải động tại đây --}}
                    @if(isset($conversations) && $conversations->isNotEmpty())
                        @foreach($conversations->first()->messages as $message)
                            <div class="message {{ $message->sender_id == (Auth::id() ?? ($guestUserId ?? null)) ? 'sent' : 'received' }}">
                                <div class="content">{{ $message->content }}</div>
                                <div class="timestamp">{{ \Carbon\Carbon::parse($message->created_at)->format('H:i A') }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="message received">
                            <div class="content">Xin chào! Tôi có thể giúp gì cho bạn?</div>
                            <div class="timestamp">{{ \Carbon\Carbon::now()->format('H:i A') }}</div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <input type="text" class="chat-input" id="humanMessageInput" placeholder="Nhập tin nhắn...">
                    <button class="send-btn" id="humanSendButton">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </div>
            </div>

            <div class="tab-content" id="aiChat">
                <div class="modal-body" id="aiChatBody">
                    <div class="message received ai">
                        <div class="content">Xin chào, tôi là trợ lý ảo. Bạn cần thông tin về vấn đề gì?</div>
                        <div class="timestamp">10:35 SA</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="text" class="chat-input" id="aiMessageInput" placeholder="Hỏi trợ lý ảo...">
                    <button class="send-btn" id="aiSendButton">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
