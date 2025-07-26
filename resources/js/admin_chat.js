// resources/js/admin_chat.js

// Các biến Blade được truyền vào đối tượng window từ Blade view trong admin.chat.dashboard.blade.php
const AUTH_ID = window.ADMIN_CHAT_AUTH_ID;
const INITIAL_SUPPORT_CONVERSATIONS = window.ADMIN_CHAT_SUPPORT_CONVERSATIONS;
const INITIAL_INTERNAL_CONVERSATIONS = window.ADMIN_CHAT_INTERNAL_CONVERSATIONS;
const INITIAL_ADMINS_DATA = window.ADMIN_CHAT_ADMINS_DATA; // Dữ liệu admins (bao gồm cả vai trò)

console.log('admin_chat.js loaded.');
console.log('AUTH_ID:', AUTH_ID);
console.log('INITIAL_SUPPORT_CONVERSATIONS:', INITIAL_SUPPORT_CONVERSATIONS);
console.log('INITIAL_INTERNAL_CONVERSATIONS:', INITIAL_INTERNAL_CONVERSATIONS);
console.log('INITIAL_ADMINS_DATA:', INITIAL_ADMINS_DATA);


// Kiểm tra xem marked.js có tồn tại không. Nếu không, bạn cần tải nó qua CDN hoặc npm.
if (typeof marked === 'undefined') {
    console.warn("marked.js is not loaded. Markdown rendering will not work. Please ensure <script src=\"https://cdn.jsdelivr.net/npm/marked@latest/marked.min.js\"></script> is in your Blade file.");
    // Gán một hàm parse rỗng để tránh lỗi nếu marked.js chưa được tải
    window.marked = { parse: (text) => text };
}


// --- LẤY CÁC PHẦN TỬ DOM ---
// Thêm kiểm tra null cho các phần tử DOM để tránh lỗi nếu chúng không tồn tại trên trang
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
let activeConversationType = null; // 'support' hoặc 'internal' (Được cập nhật bởi loadConversation)
let currentChannel = null; // Để theo dõi kênh Echo hiện tại và thoát khi đổi cuộc hội thoại


// --- Hàm hiển thị tin nhắn vào khung chat ---
function displayMessage(messageData) {
    if (!messageContainer) {
        console.error("displayMessage: messageContainer is null. Cannot display message.");
        return;
    }

    const messageElement = document.createElement('div');
    messageElement.classList.add('message');

    // Xác định loại tin nhắn (gửi đi hay nhận về)
    messageElement.classList.add(messageData.sender_id == AUTH_ID ? 'sent' : 'received');

    // Tạo avatar (có thể tùy chỉnh thêm nếu có ảnh đại diện)
    const senderName = messageData.sender && messageData.sender.name ? messageData.sender.name : (messageData.sender_id == AUTH_ID ? 'Bạn' : 'Khách');
    const avatarContent = senderName.charAt(0).toUpperCase();
    const avatar = `<div class="avatar">${avatarContent}</div>`;

    // Sử dụng marked.parse để render Markdown
    const contentHtml = typeof marked !== 'undefined' ? marked.parse(messageData.content || '') : (messageData.content || '');

    messageElement.innerHTML = `
        ${avatar}
        <div class="content">${contentHtml}</div>
    `;
    messageContainer.appendChild(messageElement);
    messageContainer.scrollTop = messageContainer.scrollHeight; // Cuộn xuống dưới cùng
}

// Hàm để đăng ký kênh Reverb cho một conversation_id
function subscribeToConversation(convId, currentUserId) {
    if (typeof window.Echo === 'undefined') {
        console.error('subscribeToConversation: Echo is not initialized. Cannot subscribe to channel.');
        return;
    }
    if (!convId) {
        console.warn('subscribeToConversation: conversationId is null or undefined. Cannot subscribe.');
        return;
    }

    // Hủy đăng ký kênh cũ nếu có để tránh lắng nghe trùng lặp
    if (currentChannel) {
        window.Echo.leave(currentChannel.name);
        console.log(`Left channel ${currentChannel.name}`);
        currentChannel = null;
    }

    console.log(`Subscribing to private-chat.conversation.${convId} with currentUserId: ${currentUserId}`);
    // Đảm bảo tên event khớp với broadcastAs() trong Event class
    currentChannel = window.Echo.private(`chat.conversation.${convId}`)
        .listen('.message.sent', (e) => { // Tên event là .message.sent
            console.log('Received message event for current channel:', e);
            // Kiểm tra xem tin nhắn có đúng cho cuộc hội thoại đang mở không
            if (e.conversation && e.conversation.id == convId) {
                displayMessage(e.message);
            } else {
                console.warn('Received message for a different conversation than active one:', e.conversation ? e.conversation.id : 'N/A', 'Expected:', convId);
            }
        })
        .error((error) => {
            console.error('Echo channel error for conversation', convId, ':', error);
            if (error.status === 403) {
                alert('Bạn không có quyền truy cập kênh chat này.');
            }
        });
}

// --- Hàm tải dữ liệu cuộc hội thoại và hiển thị ---
async function loadConversation(conversationId, type) {
    console.log('loadConversation called with ID:', conversationId, 'Type:', type);

    // Thêm kiểm tra đầu vào
    if (!conversationId) {
        console.error('loadConversation: conversationId is null or invalid. Cannot load conversation.');
        return;
    }

    // Hủy đăng ký kênh cũ trước khi tải cuộc hội thoại mới
    if (activeConversationId && typeof window.Echo !== 'undefined') {
        window.Echo.leave(`chat.conversation.${activeConversationId}`);
        console.log(`Left previous channel chat.conversation.${activeConversationId}`);
    }

    activeConversationId = conversationId;
    activeConversationType = type; // Cập nhật loại cuộc hội thoại hiện tại

    // Xóa trạng thái active của tất cả các item và thêm vào item được chọn
    document.querySelectorAll('.conversation-item').forEach(item => item.classList.remove('active'));
    const selectedItem = document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
    if (selectedItem) {
        selectedItem.classList.add('active');
    } else {
        console.warn(`No .conversation-item found for ID: ${conversationId}`);
    }


    // Xóa tin nhắn cũ và thông tin cũ
    if (messageContainer) messageContainer.innerHTML = '<p style="text-align: center; color: var(--text-secondary); margin-top: 20px;">Đang tải tin nhắn...</p>'; // Hiển thị trạng thái tải
    if (customerInfoList) customerInfoList.innerHTML = '<p style="color: var(--text-secondary);">Đang tải thông tin...</p>';
    if (orderHistoryList) orderHistoryList.innerHTML = '';
    if (inputArea) inputArea.style.display = 'flex'; // Hiển thị khung nhập tin nhắn
    if (closeConversationBtn) closeConversationBtn.style.display = 'block'; // Luôn hiển thị nút đóng
    if (inviteAdminBtn) inviteAdminBtn.style.display = 'none'; // Mặc định ẩn, sẽ hiển thị lại nếu là support chat


    try {
        const fetchUrl = `/admin/chat/${conversationId}`;
        console.log('Attempting to fetch conversation from URL:', fetchUrl); // LOG URL THỰC SỰ ĐANG GỌI

        const response = await fetch(fetchUrl);
        if (!response.ok) {
            // Log chi tiết hơn về lỗi HTTP
            const errorText = await response.text();
            console.error(`HTTP error! Status: ${response.status}. Response text: ${errorText}`);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        const conversation = data.conversation;

        console.log('Conversation data received:', conversation);

        if (chatUserName) chatUserName.textContent = conversation.user?.name || conversation.subject || 'Trò chuyện nội bộ';


        // Hiển thị thông tin khách hàng/người tham gia
        if (conversation.type === 'support') {
            if (inviteAdminBtn) inviteAdminBtn.style.display = 'block'; // Chỉ hiển thị nút mời admin cho chat support
            if (customerInfoList) customerInfoList.innerHTML = `
                <div class="info-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    <span>${conversation.user?.name || 'Khách vãng lai'}</span>
                </div>
                <div class="info-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                    <span>${conversation.user?.email || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 6.75z" /></svg>
                    <span>${conversation.user?.phone_number || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    <span>Giao cho: ${conversation.assigned_to ? conversation.assigned_to.name : 'Chưa giao'}</span>
                </div>
            `;
            // Bạn sẽ cần một API hoặc mối quan hệ để lấy Order History của User
            // For now, it's static as in your HTML
            if (orderHistoryList) orderHistoryList.innerHTML = `
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
            if (inviteAdminBtn) inviteAdminBtn.style.display = 'block'; // Vẫn cho phép mời admin khác vào nhóm nội bộ
            if (customerInfoList) customerInfoList.innerHTML = `
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
                       <span>Tham gia: ${conversation.participants.map(p => p.user?.name).join(', ')}</span>
                   </div>
            `;
            if (orderHistoryList) orderHistoryList.innerHTML = '<p style="color: var(--text-secondary);">Không có lịch sử mua hàng cho cuộc trò chuyện nội bộ.</p>';
        }

        // Hiển thị tin nhắn
        if (messageContainer) messageContainer.innerHTML = ''; // Xóa thông báo "Đang tải tin nhắn..."
        if (conversation.messages && conversation.messages.length > 0) {
            conversation.messages.forEach(message => {
                displayMessage(message);
            });
        } else {
            if (messageContainer) messageContainer.innerHTML = '<p style="text-align: center; color: var(--text-secondary); margin-top: 20px;">Chưa có tin nhắn trong cuộc hội thoại này.</p>';
        }

        if (messageContainer) messageContainer.scrollTop = messageContainer.scrollHeight; // Cuộn xuống dưới cùng

        // Đăng ký kênh riêng tư cho cuộc hội thoại này (sau khi đã leave kênh cũ)
        subscribeToConversation(activeConversationId, AUTH_ID); // Dùng AUTH_ID của admin

    } catch (error) {
        console.error('Lỗi khi tải cuộc hội thoại:', error);
        alert('Không thể tải cuộc hội thoại. Vui lòng kiểm tra console để biết chi tiết.');
        if (inputArea) inputArea.style.display = 'none'; // Ẩn khung nhập nếu có lỗi
        if (messageContainer) messageContainer.innerHTML = '<p style="text-align: center; color: #dc3545; margin-top: 20px;">Đã xảy ra lỗi khi tải cuộc hội thoại.</p>';
        if (chatUserName) chatUserName.textContent = 'Lỗi tải cuộc hội thoại';
    }
}

// --- GÁN SỰ KIỆN ---
document.addEventListener('DOMContentLoaded', function() {
    console.log('admin_chat.js: DOMContentLoaded event fired.');

    // Gán sự kiện cho các item hội thoại
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', () => {
            const conversationId = item.dataset.conversationId;
            const type = item.dataset.type;
            console.log(`Conversation item clicked. ID: ${conversationId}, Type: ${type}`);
            loadConversation(conversationId, type);
        });
    });

    // Gán sự kiện cho nút Gửi tin nhắn
    if (sendMessageBtn && messageInput) {
        sendMessageBtn.addEventListener('click', async () => {
            const content = messageInput.value.trim();
            if (content === '' || !activeConversationId) return;

            try {
                const payload = { content: content };
                if (activeConversationId) {
                    payload.conversation_id = activeConversationId;
                }
                payload.sender_id = AUTH_ID; // Người gửi là admin hiện tại

                const response = await fetch(`/admin/chat/${activeConversationId}/send-message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
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
    }


    // --- Lắng nghe sự kiện Broadcast từ Laravel (cho Admin) ---
    if (typeof window.Echo !== 'undefined') {
        window.Echo.channel('admin.notifications')
            .listen('.conversation.created', (e) => {
                console.log('New conversation created event (admin.notifications):', e);
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
                            <p class="name">${e.conversation.user?.name || 'Khách vãng lai'}</p>
                            <p class="last-message">${e.conversation.messages && e.conversation.messages.length > 0 ? e.conversation.messages[e.conversation.messages.length - 1].content : 'Chưa có tin nhắn'}</p>
                        </div>
                    `;
                    newConversationItem.addEventListener('click', () => loadConversation(e.conversation.id, 'support'));
                    if (customerConversationsList) customerConversationsList.prepend(newConversationItem); // Thêm vào đầu danh sách
                    alert('Có cuộc trò chuyện hỗ trợ mới từ ' + (e.conversation.user?.name || 'khách vãng lai') + '!');
                }
            });

        // Lắng nghe kênh riêng tư của admin để nhận thông báo về chat nội bộ
        window.Echo.private(`users.${AUTH_ID}`)
            .listen('.conversation.created', (e) => {
                console.log('Internal conversation created event (private users channel):', e);
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
                    if (internalConversationsList) internalConversationsList.prepend(newConversationItem); // Thêm vào đầu danh sách
                    alert('Bạn có một cuộc trò chuyện nội bộ mới với chủ đề: ' + (e.conversation.subject || 'Trò chuyện nội bộ') + '!');
                }
            });
    }

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
            // Reset chat panel khi đổi tab
            activeConversationId = null;
            activeConversationType = null;
            if (chatUserName) chatUserName.textContent = '';
            if (messageContainer) messageContainer.innerHTML = '<p style="text-align: center; color: var(--text-secondary); margin-top: 20px;">Chọn một cuộc hội thoại để bắt đầu chat.</p>';
            if (customerInfoList) customerInfoList.innerHTML = '<p style="color: var(--text-secondary);">Chọn một cuộc hội thoại để xem thông tin chi tiết.</p>';
            if (orderHistoryList) orderHistoryList.innerHTML = '';
            if (inputArea) inputArea.style.display = 'none';
            if (closeConversationBtn) closeConversationBtn.style.display = 'none';
            if (inviteAdminBtn) inviteAdminBtn.style.display = 'none';
            // Hủy đăng ký kênh cũ khi đổi tab
            if (currentChannel) {
                window.Echo.leave(currentChannel.name);
                console.log(`Left channel ${currentChannel.name} on tab switch.`);
                currentChannel = null;
            }
        });
    });

    // --- Xử lý nút Đóng Hội Thoại ---
    if (closeConversationBtn) {
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
                        const convItemToRemove = document.querySelector(`.conversation-item[data-conversation-id="${activeConversationId}"]`);
                        if(convItemToRemove) convItemToRemove.remove();

                        // Reset giao diện chat
                        activeConversationId = null;
                        activeConversationType = null;
                        if (chatUserName) chatUserName.textContent = '';
                        if (messageContainer) messageContainer.innerHTML = '<p style="text-align: center; color: var(--text-secondary); margin-top: 20px;">Chọn một cuộc hội thoại để bắt đầu chat.</p>';
                        if (customerInfoList) customerInfoList.innerHTML = '<p style="color: var(--text-secondary);">Chọn một cuộc hội thoại để xem thông tin chi tiết.</p>';
                        if (orderHistoryList) orderHistoryList.innerHTML = '';
                        if (inputArea) inputArea.style.display = 'none';
                        if (closeConversationBtn) closeConversationBtn.style.display = 'none';
                        if (inviteAdminBtn) inviteAdminBtn.style.display = 'none';
                        // Hủy đăng ký kênh sau khi đóng cuộc hội thoại
                        if (currentChannel) {
                            window.Echo.leave(currentChannel.name);
                            console.log(`Left channel ${currentChannel.name} after close.`);
                            currentChannel = null;
                        }
                    } else {
                        alert(data.message || 'Lỗi khi đóng cuộc hội thoại.');
                    }
                } catch (error) {
                    console.error('Lỗi:', error);
                    alert('Đã xảy ra lỗi khi đóng cuộc hội thoại.');
                }
            }
        });
    }

    // --- Logic Modal Mời Admin ---
    if (inviteAdminBtn) {
        inviteAdminBtn.addEventListener('click', () => {
            // Cập nhật danh sách admins cho select box trong modal
            // Di chuyển logic hiển thị modal vào trong hàm để đảm bảo select được điền trước
            if (adminSelect && INITIAL_ADMINS_DATA) {
                adminSelect.innerHTML = ''; // Xóa các option cũ
                let optionsAdded = 0; // Biến đếm số option được thêm

                INITIAL_ADMINS_DATA.forEach(admin => {
                    // Bỏ qua chính người dùng đang đăng nhập
                    if (admin.id === AUTH_ID) {
                        console.log(`Skipping current user: ${admin.name} (ID: ${admin.id})`);
                        return; // Bỏ qua admin hiện tại và chuyển sang admin tiếp theo
                    }

                    let shouldAdd = false;

                    // Logic lọc dựa trên loại cuộc trò chuyện (activeConversationType)
                    if (activeConversationType === 'support') {
                        // Nếu là chat khách hàng (support chat), thêm tất cả admin (trừ người đang đăng nhập)
                        shouldAdd = true;
                        console.log(`Conversation Type: Support. Admin ${admin.name} is eligible.`);
                    } else if (activeConversationType === 'internal') {
                        // Nếu là chat nội bộ, chỉ thêm admin có vai trò 'order_manager'
                        // Đảm bảo admin.roles tồn tại và là một mảng
                        if (admin.roles && Array.isArray(admin.roles)) {
                            const roleNames = admin.roles.map(role => role.name || role); // Lấy tên vai trò
                            if (roleNames.includes('order_manager')) {
                                shouldAdd = true;
                                console.log(`Conversation Type: Internal. Admin ${admin.name} has 'order_manager' role and is eligible.`);
                            } else {
                                console.log(`Conversation Type: Internal. Admin ${admin.name} does NOT have 'order_manager' role. Skipping.`);
                            }
                        } else {
                            console.warn(`Admin ${admin.name} has no roles or roles is not an array. Skipping for internal chat.`);
                        }
                    } else {
                        // Trường hợp activeConversationType không xác định (chưa click vào cuộc hội thoại nào)
                        console.warn(`Undefined activeConversationType (${activeConversationType}). Admin ${admin.name} is eligible.`);
                        shouldAdd = true; // Mặc định hiển thị nếu không có loại cụ thể được chọn
                    }

                    if (shouldAdd) {
                        const option = document.createElement('option');
                        option.value = admin.id;
                        option.textContent = admin.name;
                        adminSelect.appendChild(option);
                        optionsAdded++;
                        console.log(`Added option for: ${admin.name} (ID: ${admin.id})`);
                    }
                });

                if (optionsAdded === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Không có người dùng nào để mời';
                    option.disabled = true;
                    adminSelect.appendChild(option);
                    console.log('No other eligible users available to invite based on conversation type and roles.');
                }
            } else {
                console.warn('adminSelect or INITIAL_ADMINS_DATA is missing/empty for Invite Admin modal. adminSelect:', adminSelect, 'INITIAL_ADMINS_DATA:', INITIAL_ADMINS_DATA);
            }
            if (inviteAdminModal) inviteAdminModal.style.display = 'block'; // Di chuyển vào cuối hàm
        });
    }

    if (confirmInviteBtn && adminSelect) {
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
                    if (inviteAdminModal) inviteAdminModal.style.display = 'none';
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
    }

    if (cancelInviteBtn) {
        cancelInviteBtn.addEventListener('click', () => {
            if (inviteAdminModal) inviteAdminModal.style.display = 'none';
        });
    }

    // --- Logic Modal Tạo Chat Nội Bộ Mới ---
    if (createInternalChatBtn) {
        createInternalChatBtn.addEventListener('click', () => {
            if (createInternalChatModal) createInternalChatModal.style.display = 'block';
            // Cập nhật danh sách admins cho select box trong modal
            if (internalChatRecipients && INITIAL_ADMINS_DATA) {
                internalChatRecipients.innerHTML = ''; // Xóa các option cũ
                let optionsAdded = 0;
                INITIAL_ADMINS_DATA.forEach(admin => {
                    // Loại bỏ admin hiện tại khỏi danh sách để mời
                    if (admin.id === AUTH_ID) {
                        return;
                    }
                   
                    // Cho phép tất cả admin được mời vào chat nội bộ (nếu không có yêu cầu lọc)
                    // Nếu bạn muốn lọc cả ở đây, bạn sẽ cần logic tương tự như inviteAdminBtn
                    const option = document.createElement('option');
                    option.value = admin.id;
                    option.textContent = admin.name;
                    internalChatRecipients.appendChild(option);
                    optionsAdded++;
                });
                if (optionsAdded === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Không có người dùng nào để mời';
                    option.disabled = true;
                    internalChatRecipients.appendChild(option);
                }
            }
        });
    }

    if (internalChatForm && internalChatRecipients && firstMessage) {
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
                    if (createInternalChatModal) createInternalChatModal.style.display = 'none';
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
    }

    if (cancelCreateInternalChatBtn) {
        cancelCreateInternalChatBtn.addEventListener('click', () => {
            if (createInternalChatModal) createInternalChatModal.style.display = 'none';
            internalChatForm.reset();
        });
    }

    // Tự động tải cuộc hội thoại đầu tiên nếu có khi trang tải
    if (INITIAL_SUPPORT_CONVERSATIONS && INITIAL_SUPPORT_CONVERSATIONS.length > 0) {
        console.log('Attempting to load first support conversation:', INITIAL_SUPPORT_CONVERSATIONS[0].id);
        loadConversation(INITIAL_SUPPORT_CONVERSATIONS[0].id, "support");
    } else if (INITIAL_INTERNAL_CONVERSATIONS && INITIAL_INTERNAL_CONVERSATIONS.length > 0) {
        console.log('Attempting to load first internal conversation:', INITIAL_INTERNAL_CONVERSATIONS[0].id);
        loadConversation(INITIAL_INTERNAL_CONVERSATIONS[0].id, "internal");
    } else {
        console.log('No initial conversations to load.');
    }
});
