// chat.js
// Các biến Blade được truyền vào đối tượng window từ Blade view
const AUTH_ID = window.APP_AUTH_ID;
const INITIAL_CONVERSATIONS = window.APP_CONVERSATIONS_DATA; // Bao gồm messages

// --- LẤY CÁC PHẦN TỬ DOM ---
const chatBubble = document.getElementById('chatBubble');
const chatModal = document.getElementById('chatModal');
const closeModalBtn = document.getElementById('closeModal');
const welcomeScreen = document.getElementById('welcomeScreen');
const welcomeForm = document.getElementById('welcomeForm');
const guestNameInput = document.getElementById('guestName');
const guestPhoneInput = document.getElementById('guestPhone');
const mainChatInterface = document.getElementById('mainChatInterface');
const tabButtons = document.querySelectorAll('.tab-button');
const tabContents = document.querySelectorAll('.tab-content');
const humanChatTab = document.getElementById('humanChat');
const aiChatTab = document.getElementById('aiChat');
const humanChatBody = document.getElementById('humanChatBody');
const aiChatBody = document.getElementById('aiChatBody');
const humanMessageInput = document.getElementById('humanMessageInput');
const humanSendButton = document.getElementById('humanSendButton');
const aiMessageInput = document.getElementById('aiMessageInput');
const aiSendButton = document.getElementById('aiSendButton');

let currentConversationId = null; // Quản lý cuộc hội thoại đang hoạt động cho chat với nhân viên

// --- HÀM CHỨC NĂNG ---

function displayMessage(container, text, type, timestamp = null) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('message', type);

    if (type === 'received' && container.parentElement.id === 'aiChat') {
        messageElement.classList.add('ai');
    }

    const time = timestamp ? new Date(timestamp).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) : new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

    // Kiểm tra nếu marked.parse không tồn tại, sử dụng text thuần
    const renderedText = (typeof marked !== 'undefined' && marked.parse) ? marked.parse(text) : text;

    messageElement.innerHTML = `
        <div class="content">${renderedText}</div>
        <div class="timestamp">${time}</div>
    `;
    container.appendChild(messageElement);
    container.scrollTop = container.scrollHeight;
}

function showMainChat() {
    welcomeScreen.style.display = 'none';
    mainChatInterface.style.display = 'flex';
    humanChatBody.scrollTop = humanChatBody.scrollHeight;
}

function openModal() {
    chatModal.classList.add('show');
    chatBubble.style.opacity = '0';
    chatBubble.style.visibility = 'hidden';
    chatBubble.style.transform = 'scale(0)';

    initializeChatState(); // Kiểm tra trạng thái và hiển thị đúng màn hình
}

function closeModal() {
    chatModal.classList.remove('show');
    chatBubble.style.opacity = '1';
    chatBubble.style.visibility = 'visible';
    chatBubble.style.transform = 'scale(1)';
}

// Hàm để đăng ký kênh Reverb cho một conversation_id
function subscribeToConversation(convId, currentUserId) {
    if (typeof window.Echo === 'undefined') {
        console.error('Warning: Echo is not initialized. Cannot subscribe to channel.');
        return;
    }
    // Hủy đăng ký kênh cũ nếu có để tránh trùng lặp listener
    if (window.Echo.connector.channels[`private-chat.conversation.${convId}`]) {
        window.Echo.leave(`chat.conversation.${convId}`);
        console.log(`Unsubscribed from chat.conversation.${convId}`);
    }

    window.Echo.private(`chat.conversation.${convId}`)
        .listen('.message.sent', (e) => {
            console.log('Message received:', e.message);
            const messageType = (e.message.sender_id == currentUserId) ? 'sent' : 'received';
            displayMessage(humanChatBody, e.message.content, messageType, e.message.created_at);
        });
    console.log(`Subscribed to chat.conversation.${convId} for user ${currentUserId}`);
}


// --- GÁN SỰ KIỆN VÀ KHỞI TẠO LOGIC ---
document.addEventListener('DOMContentLoaded', function() {
    console.log('chat.js: DOMContentLoaded event fired.');

    // Gán sự kiện cho các phần tử chính
    if (chatBubble) chatBubble.addEventListener('click', openModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);

    // Hàm khởi tạo trạng thái chat (gọi khi DOM sẵn sàng và khi mở modal)
    function initializeChatState() {
        const guestIdFromLocalStorage = localStorage.getItem('guest_user_id');
        const currentSenderId = AUTH_ID || guestIdFromLocalStorage;

        humanChatBody.innerHTML = ''; // Xóa tin nhắn cũ/mặc định

        if (INITIAL_CONVERSATIONS && INITIAL_CONVERSATIONS.length > 0) {
            const firstConv = INITIAL_CONVERSATIONS[0];
            currentConversationId = firstConv.id;

            showMainChat();
            if (currentConversationId && currentSenderId) {
                subscribeToConversation(currentConversationId, currentSenderId);
            }

            firstConv.messages.forEach(msg => {
                const messageType = (msg.sender_id == currentSenderId) ? 'sent' : 'received';
                displayMessage(humanChatBody, msg.content, messageType, msg.created_at);
            });
            humanChatBody.scrollTop = humanChatBody.scrollHeight;
        } else if (currentSenderId) {
    // Có ID của khách cũ, gọi lên server để lấy lại lịch sử chat
    fetchAndDisplayHistory(currentSenderId);
} else {
            // Không có ID nào, hiển thị màn hình chào mừng
            welcomeScreen.style.display = 'flex';
            mainChatInterface.style.display = 'none';
        }
    }

    initializeChatState(); // Gọi lần đầu khi DOMContentLoaded

    if (welcomeForm) {
        welcomeForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            const name = guestNameInput.value.trim();
            const phone_number = guestPhoneInput.value.trim();

            if (name === '' || phone_number === '') {
                alert('Vui lòng nhập đầy đủ Tên và Số điện thoại.');
                return;
            }

            try {
                const response = await fetch('/chat/register-guest', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ name, phone_number })
                });
                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem('guest_user_id', data.user_id);
                    currentConversationId = data.conversation_id;
                    const currentSenderId = data.user_id;

                    showMainChat();
                    humanChatBody.innerHTML = '';

                    if (data.conversation_messages && data.conversation_messages.length > 0) {
                        data.conversation_messages.forEach(msg => {
                            const messageType = (msg.sender_id == currentSenderId) ? 'sent' : 'received';
                            displayMessage(humanChatBody, msg.content, messageType, msg.created_at);
                        });
                    } else {
                        displayMessage(humanChatBody, `Xin chào ${name}! Tôi có thể giúp gì cho bạn?`, 'received');
                    }

                    if (currentConversationId && currentSenderId) {
                        subscribeToConversation(currentConversationId, currentSenderId);
                    }

                } else {
                    let errorMessage = 'Đăng ký khách thất bại.';
                    if (response.status === 422 && data.errors) {
                        errorMessage += '\n' + Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMessage = data.message;
                    }
                    alert(errorMessage);
                }
            } catch (error) {
                console.error('Lỗi khi đăng ký khách:', error);
                alert('Đã xảy ra lỗi trong quá trình đăng ký khách.');
            }
        });
    }

    if (tabButtons) {
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                button.classList.add('active');
                document.querySelector(button.dataset.target).classList.add('active');
            });
        });
    }

    if (humanSendButton && humanMessageInput) {
        humanSendButton.addEventListener('click', async () => {
            const content = humanMessageInput.value.trim();
            const senderId = AUTH_ID || localStorage.getItem('guest_user_id');

            if (content === '') {
                alert('Vui lòng nhập tin nhắn.');
                return;
            }
            if (!senderId) {
                alert('Không thể xác định người gửi tin nhắn. Vui lòng đăng nhập hoặc cung cấp thông tin khách.');
                return;
            }

            displayMessage(humanChatBody, content, 'sent');
            humanMessageInput.value = '';
            humanMessageInput.focus();

            try {
                const payload = {
                    content: content,
                    sender_id: senderId
                };

                if (currentConversationId) {
                    payload.conversation_id = currentConversationId;
                }

                const response = await fetch('/chat/send-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Socket-ID': window.Echo.socketId()
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                if (response.ok) {
                    if (!currentConversationId && data.conversation_id) {
                        currentConversationId = data.conversation_id;
                        subscribeToConversation(currentConversationId, senderId);
                    }
                } else {
                    alert(data.message || 'Lỗi khi gửi tin nhắn.');
                }
            } catch (error) {
                console.error('Lỗi:', error);
                alert('Đã xảy ra lỗi khi gửi tin nhắn.');
            }
        });

        humanMessageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') humanSendButton.click();
        });
    }

    if (aiSendButton && aiMessageInput) {
        aiSendButton.addEventListener('click', () => {
            const messageText = aiMessageInput.value.trim();
            if (messageText === '') return;
            displayMessage(aiChatBody, messageText, 'sent');
            aiMessageInput.value = '';
            aiMessageInput.focus();
            simulateAiResponse(aiChatBody, messageText);
        });

        aiMessageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') aiSendButton.click();
        });
    }
    async function fetchAndDisplayHistory(userId) {
    try {
        const response = await fetch('/chat/get-history', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ user_id: userId })
        });

        if (!response.ok) {
            throw new Error('Failed to fetch history');
        }

        const data = await response.json();
        const conversation = data.conversation;

        showMainChat(); // Hiển thị giao diện chat

        if (conversation && conversation.messages.length > 0) {
            currentConversationId = conversation.id;
            
            // Hiển thị các tin nhắn cũ
            conversation.messages.forEach(msg => {
                const messageType = (msg.sender_id == userId) ? 'sent' : 'received';
                displayMessage(humanChatBody, msg.content, messageType, msg.created_at);
            });
            humanChatBody.scrollTop = humanChatBody.scrollHeight;

            // Đăng ký kênh real-time để nhận tin nhắn mới
            subscribeToConversation(currentConversationId, userId);
        } else {
            // Nếu không có lịch sử, hiển thị lời chào
            displayMessage(humanChatBody, `Xin chào! Tôi có thể giúp gì cho bạn?`, 'received');
        }

    } catch (error) {
        console.error("Error fetching chat history:", error);
        // Nếu lỗi, vẫn hiển thị khung chat với lời chào
        showMainChat();
        displayMessage(humanChatBody, `Xin chào! Tôi có thể giúp gì cho bạn?`, 'received');
    }
}

    function simulateAiResponse(container, userMessage) {
        setTimeout(() => {
            let aiReply = "Tôi chưa hiểu câu hỏi của bạn. Bạn có thể hỏi về 'bảo hành', 'khuyến mãi', hoặc 'địa chỉ'.";
            const lowerCaseMessage = userMessage.toLowerCase();

            if (lowerCaseMessage.includes('bảo hành')) {
                aiReply = "Sản phẩm chính hãng được bảo hành 12 tháng tại tất cả các cửa hàng trên toàn quốc ạ.";
            } else if (lowerCaseMessage.includes('khuyến mãi') || lowerCaseMessage.includes('giảm giá')) {
                aiReply = "Hiện tại đang có chương trình giảm giá 10% cho các phụ kiện khi mua kèm điện thoại. Bạn xem chi tiết tại trang khuyến mãi nhé.";
            } else if (lowerCaseMessage.includes('địa chỉ') || lowerCaseMessage.includes('cửa hàng')) {
                aiReply = "Cửa hàng chính của chúng tôi ở tại 123 Đường ABC, Quận 1, TP.HCM. Rất hân hạnh được phục vụ bạn!";
            }

            displayMessage(container, aiReply, 'received');
        }, 1000);
    }
});
