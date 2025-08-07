// resources/js/admin_chat.js

// Các biến Blade được truyền vào từ view
const AUTH_ID = window.ADMIN_CHAT_AUTH_ID;
const INITIAL_SUPPORT_CONVERSATIONS = window.ADMIN_CHAT_SUPPORT_CONVERSATIONS;
const INITIAL_INTERNAL_CONVERSATIONS = window.ADMIN_CHAT_INTERNAL_CONVERSATIONS;
const INITIAL_ADMINS_DATA = window.ADMIN_CHAT_ADMINS_DATA;

// --- KIỂM TRA THƯ VIỆN & CÀI ĐẶT BAN ĐẦU ---
if (typeof marked === 'undefined') {
    console.warn("marked.js is not loaded. Markdown rendering will fall back to plain text.");
    window.marked = { parse: (text) => text }; // Fallback để tránh lỗi
}

// --- LẤY CÁC PHẦN TỬ DOM ---
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
const inviteAdminModal = document.getElementById('inviteAdminModal');
const adminSelect = document.getElementById('adminSelect');
const confirmInviteBtn = document.getElementById('confirmInviteBtn');
const cancelInviteBtn = document.getElementById('cancelInviteBtn');
const createInternalChatModal = document.getElementById('createInternalChatModal');
const internalChatForm = document.getElementById('internalChatForm');
const internalChatSubject = document.getElementById('internalChatSubject');
const internalChatRecipients = document.getElementById('internalChatRecipients');
const firstMessage = document.getElementById('firstMessage');
const cancelCreateInternalChatBtn = document.getElementById('cancelCreateInternalChatBtn');

// --- QUẢN LÝ TRẠNG THÁI ---
let activeConversationId = null;
let activeConversationType = null;
let currentChannel = null;

// --- CÁC HÀM HELPER ---

/**
 * ✅ CẢI TIẾN: Hàm hiển thị thông báo toast thay vì alert()
 * Gợi ý: Tích hợp thư viện như Toastify.js hoặc Notyf vào đây.
 * @param {string} message - Nội dung thông báo.
 * @param {string} type - Loại thông báo ('success', 'error', 'info').
 */
function showToastNotification(message, type = 'info') {
    console.log(`[${type.toUpperCase()} NOTIFICATION]: ${message}`);
    // Ví dụ với Toastify.js:
    // Toastify({ text: message, className: type, gravity: "top", position: "right" }).showToast();
}

/**
 * Hiển thị một tin nhắn trong khung chat.
 * @param {object} messageData - Dữ liệu tin nhắn.
 */
function displayMessage(messageData) {
    if (!messageContainer) return;

    const messageElement = document.createElement('div');
    messageElement.classList.add('message', messageData.sender_id == AUTH_ID ? 'sent' : 'received');

    const senderName = messageData.sender?.name || (messageData.sender_id == AUTH_ID ? 'Bạn' : 'Khách');
    const avatarContent = senderName.charAt(0).toUpperCase();
    const avatar = `<div class="avatar" title="${senderName}">${avatarContent}</div>`;
    const contentHtml = marked.parse(messageData.content || '');

    messageElement.innerHTML = `${avatar}<div class="content">${contentHtml}</div>`;
    messageContainer.appendChild(messageElement);
    messageContainer.scrollTop = messageContainer.scrollHeight;
}

/**
 * ✅ CẢI TIẾN: Hàm tái sử dụng để tạo và thêm một item hội thoại vào danh sách.
 * @param {object} conversation - Dữ liệu cuộc hội thoại.
 */
function createConversationElement(conversation) {
    const list = conversation.type === 'support' ? customerConversationsList : internalConversationsList;
    if (!list) return;

    // Xóa thông báo "Không có cuộc trò chuyện" nếu có
    const emptyItem = list.querySelector('.empty-message');
    if (emptyItem) emptyItem.remove();

    const item = document.createElement('li');
    item.className = 'conversation-item';
    item.dataset.conversationId = conversation.id;
    item.dataset.type = conversation.type;

    const lastMessage = conversation.latest_message?.content || 'Bắt đầu cuộc trò chuyện...';
    const name = conversation.type === 'support' ? (conversation.user?.name || 'Khách vãng lai') : (conversation.subject || 'Trò chuyện nội bộ');
    const avatarIcon = conversation.type === 'support'
        ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.963A3.75 3.75 0 1012 6v2.75m-3.75 0A3.75 3.75 0 016 10.5v.75a3.75 3.75 0 01-7.5 0v-.75A3.75 3.75 0 016 6.75v2.75m0 0v-.25m0 0c0-.966.784-1.75 1.75-1.75h.5c.966 0 1.75.784 1.75 1.75v.25m0 0c0 .966-.784 1.75-1.75 1.75h-.5A1.75 1.75 0 016 13.25v-.25m0 0c0-1.105.895-2 2-2h.5c1.105 0 2 .895 2 2v.25m-2.5 0c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m-4.5 0c.23.02.428.037.601.055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25m2.5 0c-.69-.02-.823-.037-1-.055a3.75 3.75 0 01-3.44-3.44c-.018-.177-.035-.31-.055-.478m-1.5 5.25c.69.02.823.037 1 .055a3.75 3.75 0 013.44 3.44c.018.177.035.31.055.478m0 0c0 1.105-.895 2-2 2h-.5c-1.105 0-2-.895-2-2v-.25" /></svg>`;

    item.innerHTML = `
        <div class="avatar">${avatarIcon}</div>
        <div class="details">
            <p class="name">${name}</p>
            <p class="last-message">${lastMessage}</p>
        </div>
    `;
    item.addEventListener('click', () => loadConversation(conversation.id, conversation.type));
    list.prepend(item);
}


// --- HÀM LOGIC CHÍNH ---

/**
 * Đăng ký kênh Echo cho một cuộc hội thoại.
 * @param {number} convId - ID cuộc hội thoại.
 */
function subscribeToConversation(convId) {
    if (typeof window.Echo === 'undefined' || !convId) return;

    if (currentChannel) {
        window.Echo.leave(currentChannel.name);
    }

    currentChannel = window.Echo.private(`chat.conversation.${convId}`)
        .listen('.message.sent', (e) => {
            if (e.conversation?.id == activeConversationId && e.message.sender_id != AUTH_ID) {
                displayMessage(e.message);
            }
        })
        .error((error) => {
            console.error('Echo channel error:', error);
            if (error.status === 403) {
                showToastNotification('Bạn không có quyền truy cập kênh chat này.', 'error');
            }
        });
}

/**
 * Tải và hiển thị chi tiết một cuộc hội thoại.
 * @param {number} conversationId - ID cuộc hội thoại.
 * @param {string} type - Loại ('support' hoặc 'internal').
 */
async function loadConversation(conversationId, type) {
    if (!conversationId) return;

    activeConversationId = conversationId;
    activeConversationType = type;

    document.querySelectorAll('.conversation-item').forEach(item => item.classList.remove('active'));
    document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`)?.classList.add('active');

    messageContainer.innerHTML = `<p>Đang tải tin nhắn...</p>`;
    customerInfoList.innerHTML = `<p>Đang tải thông tin...</p>`;
    orderHistoryList.innerHTML = '';
    inputArea.style.display = 'flex';
    closeConversationBtn.style.display = 'block';
    inviteAdminBtn.style.display = 'block';

    try {
        const response = await fetch(`/admin/chat/${conversationId}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const data = await response.json();
        const conversation = data.conversation;

        chatUserName.textContent = conversation.user?.name || conversation.subject || 'Trò chuyện';

        // Hiển thị thông tin
        if (conversation.type === 'support') {
            customerInfoList.innerHTML = `
                <div class="info-item">...<span>${conversation.user?.name || 'Khách vãng lai'}</span></div>
                <div class="info-item">...<span>${conversation.user?.email || 'N/A'}</span></div>
                <div class="info-item">...<span>${conversation.user?.phone_number || 'N/A'}</span></div>
                <div class="info-item">...<span>Giao cho: ${conversation.assigned_to?.name || 'Chưa giao'}</span></div>
            `;
            orderHistoryList.innerHTML = ``;
        } else {
            customerInfoList.innerHTML = `
                <div class="info-item">...<span>Chủ đề: ${conversation.subject || 'Trò chuyện nội bộ'}</span></div>
                <div class="info-item">...<span>Người tạo: ${conversation.assigned_to?.name || 'N/A'}</span></div>
                <div class="info-item">...<span>Tham gia: ${conversation.participants.map(p => p.user?.name).join(', ')}</span></div>
            `;
            orderHistoryList.innerHTML = `<p>Không áp dụng cho chat nội bộ.</p>`;
        }

        // Hiển thị tin nhắn
        messageContainer.innerHTML = '';
        if (conversation.messages?.length > 0) {
            conversation.messages.forEach(displayMessage);
        } else {
            messageContainer.innerHTML = `<p>Chưa có tin nhắn.</p>`;
        }

        subscribeToConversation(conversationId);

    } catch (error) {
        console.error('Lỗi khi tải cuộc hội thoại:', error);
        showToastNotification('Không thể tải cuộc hội thoại.', 'error');
        chatUserName.textContent = 'Lỗi';
        messageContainer.innerHTML = `<p>Đã xảy ra lỗi khi tải cuộc hội thoại.</p>`;
    }
}

/**
 * Xử lý việc gửi tin nhắn.
 */
async function handleSendMessage() {
    const content = messageInput.value.trim();
    if (content === '' || !activeConversationId) return;

    const optimisticMessage = {
        content: content,
        sender_id: AUTH_ID,
        sender: { name: 'Bạn' }
    };
    displayMessage(optimisticMessage);
    messageInput.value = '';
    messageInput.focus();

    try {
        const response = await fetch(`/admin/chat/${activeConversationId}/send-message`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Socket-ID': window.Echo.socketId()
            },
            body: JSON.stringify({ content })
        });

        if (!response.ok) {
            const data = await response.json();
            showToastNotification(data.message || 'Lỗi khi gửi tin nhắn.', 'error');
            // Cân nhắc xóa tin nhắn tạm thời hoặc đánh dấu là gửi lỗi
        }
    } catch (error) {
        console.error('Lỗi mạng khi gửi tin nhắn:', error);
        showToastNotification('Lỗi mạng, không thể gửi tin nhắn.', 'error');
    }
}


// --- GÁN SỰ KIỆN KHI DOM ĐÃ TẢI ---
document.addEventListener('DOMContentLoaded', function() {

    // Sự kiện click cho các item hội thoại ban đầu
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', () => {
            loadConversation(item.dataset.conversationId, item.dataset.type);
        });
    });
    
    // Sự kiện gửi tin nhắn
    if (sendMessageBtn && messageInput) {
        sendMessageBtn.addEventListener('click', handleSendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSendMessage();
            }
        });
    }

    // Sự kiện cho các Tabs
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            tabLinks.forEach(l => l.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.querySelector(this.dataset.target)?.classList.add('active');
        });
    });

    // Sự kiện nút Đóng Hội Thoại
    closeConversationBtn?.addEventListener('click', async () => {
        if (!activeConversationId || !confirm('Bạn có chắc muốn đóng cuộc hội thoại này?')) return;

        try {
            const response = await fetch(`/admin/chat/${activeConversationId}/close`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            });
            const data = await response.json();
            if (response.ok) {
                showToastNotification(data.message, 'success');
                document.querySelector(`.conversation-item[data-conversation-id="${activeConversationId}"]`)?.remove();
                // Reset giao diện
                chatPanel.style.display = 'none'; // Hoặc reset về trạng thái ban đầu
            } else {
                showToastNotification(data.message || 'Lỗi khi đóng cuộc hội thoại.', 'error');
            }
        } catch (error) {
            console.error('Lỗi khi đóng cuộc hội thoại:', error);
            showToastNotification('Đã xảy ra lỗi mạng.', 'error');
        }
    });

    // Sự kiện nút Mời Admin
    inviteAdminBtn?.addEventListener('click', () => {
        if (adminSelect) {
            adminSelect.innerHTML = '';
            INITIAL_ADMINS_DATA.filter(admin => admin.id !== AUTH_ID).forEach(admin => {
                const option = document.createElement('option');
                option.value = admin.id;
                option.textContent = admin.name;
                adminSelect.appendChild(option);
            });
        }
        inviteAdminModal.style.display = 'block';
    });

    confirmInviteBtn?.addEventListener('click', async () => {
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
            showToastNotification(data.message, response.ok ? 'success' : 'error');
            if (response.ok) {
                inviteAdminModal.style.display = 'none';
            }
        } catch(error) {
            console.error('Lỗi mời admin:', error);
            showToastNotification('Lỗi mạng khi mời admin.', 'error');
        }
    });

    cancelInviteBtn?.addEventListener('click', () => inviteAdminModal.style.display = 'none');
    
    // Sự kiện modal Tạo Chat Nội Bộ
    createInternalChatBtn?.addEventListener('click', () => {
        if (internalChatRecipients) {
             internalChatRecipients.innerHTML = '';
             INITIAL_ADMINS_DATA.filter(admin => admin.id !== AUTH_ID).forEach(admin => {
                const option = document.createElement('option');
                option.value = admin.id;
                option.textContent = admin.name;
                internalChatRecipients.appendChild(option);
            });
        }
        createInternalChatModal.style.display = 'block';
    });
    
    internalChatForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const recipient_ids = Array.from(internalChatRecipients.selectedOptions).map(opt => opt.value);
        const first_message = firstMessage.value.trim();

        if (recipient_ids.length === 0 || first_message === '') {
            return showToastNotification('Vui lòng chọn người nhận và nhập tin nhắn.', 'error');
        }

        try {
            const response = await fetch('/admin/chat/create-internal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    subject: internalChatSubject.value.trim(),
                    recipient_ids,
                    first_message
                })
            });
            const data = await response.json();
            showToastNotification(data.message, response.ok ? 'success' : 'error');
            if (response.ok) {
                createInternalChatModal.style.display = 'none';
                internalChatForm.reset();
                // ✅ CẢI TIẾN: Không cần reload, Echo sẽ tự cập nhật
            }
        } catch (error) {
            console.error('Lỗi tạo chat nội bộ:', error);
            showToastNotification('Lỗi mạng khi tạo chat nội bộ.', 'error');
        }
    });

    cancelCreateInternalChatBtn?.addEventListener('click', () => createInternalChatModal.style.display = 'none');


    // --- LẮNG NGHE SỰ KIỆN BROADCAST TOÀN CỤC ---
    if (typeof window.Echo !== 'undefined') {
        // Kênh public cho các thông báo chung
        window.Echo.channel('admin.notifications')
            .listen('.conversation.created', (e) => {
                if (e.conversation.type === 'support') {
                    showToastNotification(`Có chat hỗ trợ mới từ ${e.conversation.user?.name || 'khách'}.`, 'info');
                    createConversationElement(e.conversation);
                }
            });

        // Kênh private của riêng admin này
        window.Echo.private(`users.${AUTH_ID}`)
            .listen('.conversation.created', (e) => {
                if (e.conversation.type === 'internal') {
                    showToastNotification(`Bạn được mời vào chat nội bộ: ${e.conversation.subject || ''}`, 'info');
                    createConversationElement(e.conversation);
                }
            })
            .listen('.message.sent', (e) => {
        // Bỏ qua nếu tin nhắn là của chính mình
        if (e.message.sender_id == AUTH_ID) {
            return;
        }

        // Tìm item hội thoại trong danh sách
        const conversationElement = document.querySelector(`.conversation-item[data-conversation-id="${e.conversation.id}"]`);

        if (conversationElement) {
            // Cập nhật tin nhắn cuối cùng
            const lastMessageP = conversationElement.querySelector('.last-message');
            if (lastMessageP) {
                lastMessageP.textContent = e.message.content;
            }

            // Di chuyển item hội thoại lên đầu danh sách
            const list = conversationElement.parentElement;
            list.prepend(conversationElement);
            
            // Tùy chọn: Hiển thị thông báo toast
            const conversationName = conversationElement.querySelector('.name').textContent;
            showToastNotification(`Tin nhắn mới từ "${conversationName}"`, 'info');
        }
    });
    }

    // Tự động tải cuộc hội thoại đầu tiên nếu có
    if (INITIAL_SUPPORT_CONVERSATIONS?.length > 0) {
        loadConversation(INITIAL_SUPPORT_CONVERSATIONS[0].id, "support");
    } else if (INITIAL_INTERNAL_CONVERSATIONS?.length > 0) {
        loadConversation(INITIAL_INTERNAL_CONVERSATIONS[0].id, "internal");
    }
});