// chat.js
// Các biến Blade được truyền vào đối tượng window từ Blade view
const AUTH_ID = window.APP_AUTH_ID;
const GUEST_USER_ID = window.APP_GUEST_USER_ID; // Đổi tên để tránh nhầm lẫn với biến cục bộ guestUserIdFromBlade
const INITIAL_CONVERSATIONS = window.APP_CONVERSATIONS_DATA;

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

    const renderedText = marked.parse(text);

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

    let activeGuestId = localStorage.getItem('guest_user_id') || GUEST_USER_ID;

    // Kiểm tra xem đã đăng nhập hoặc có ID khách chưa
    if (AUTH_ID !== null || activeGuestId !== null) {
        showMainChat();
    } else {
        welcomeScreen.style.display = 'flex';
        mainChatInterface.style.display = 'none';
    }
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
    window.Echo.private(`chat.conversation.${convId}`)
        .listen('.message.sent', (e) => {
            const messageType = (e.message.sender_id == currentUserId) ? 'sent' : 'received';
            displayMessage(humanChatBody, e.message.content, messageType, e.message.created_at);
        });
}


// --- GÁN SỰ KIỆN ---
// Chỉ gán sự kiện sau khi DOM đã sẵn sàng
document.addEventListener('DOMContentLoaded', function() {
    console.log('chat.js: DOMContentLoaded event fired.');
    // Kiểm tra xem các phần tử DOM đã tồn tại chưa trước khi gán sự kiện
    if (chatBubble) chatBubble.addEventListener('click', openModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);

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
                    // Cập nhật biến GUEST_USER_ID cục bộ để sử dụng
                    // Không cần gán lại GUEST_USER_ID vì nó là const, chỉ cần cập nhật logic sử dụng nó
                    // openModal sẽ lấy từ localStorage nếu có
                    showMainChat();

                    // Hiển thị tin nhắn chào mừng cho khách mới đăng ký
                    humanChatBody.innerHTML = '';
                    displayMessage(humanChatBody, `Xin chào ${name}! Tôi có thể giúp gì cho bạn?`, 'received');


                    if (data.conversation_id) {
                        currentConversationId = data.conversation_id;
                        subscribeToConversation(currentConversationId, data.user_id); // Dùng user_id mới
                    }
                } else {
                    alert(data.message || 'Lỗi khi đăng ký khách.');
                }
            } catch (error) {
                console.error('Lỗi:', error);
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
            if (content === '' || (AUTH_ID === null && (localStorage.getItem('guest_user_id') === null && GUEST_USER_ID === null))) {
                alert('Vui lòng đăng nhập hoặc cung cấp thông tin khách để gửi tin nhắn.');
                return;
            }

            displayMessage(humanChatBody, content, 'sent');
            humanMessageInput.value = '';
            humanMessageInput.focus();

            try {
                const payload = { content: content };
                if (currentConversationId) {
                    payload.conversation_id = currentConversationId;
                }
                const senderId = AUTH_ID || localStorage.getItem('guest_user_id') || GUEST_USER_ID;

                if (senderId) {
                     payload.sender_id = senderId;
                } else {
                    console.error('Sender ID not determined.');
                    alert('Không thể xác định người gửi tin nhắn.');
                    return;
                }

                const response = await fetch('/chat/send-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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

    // Xử lý gửi tin nhắn cho Chat với AI (mô phỏng)
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

    // Khởi tạo đăng ký kênh Reverb khi tải trang, nếu có cuộc hội thoại và guestUserId hợp lệ
    // Lấy dữ liệu hội thoại ban đầu từ biến Blade
    if (INITIAL_CONVERSATIONS && INITIAL_CONVERSATIONS.length > 0) {
        const firstConversation = INITIAL_CONVERSATIONS[0];
        currentConversationId = firstConversation.id;

        // Xác định người gửi hiện tại (ưu tiên AUTH_ID, sau đó là từ localStorage, cuối cùng là từ GUEST_USER_ID ban đầu)
        const currentSenderId = AUTH_ID || localStorage.getItem('guest_user_id') || GUEST_USER_ID;

        if (currentConversationId && currentSenderId) {
            subscribeToConversation(currentConversationId, currentSenderId);
        }
        // Cuộn xuống tin nhắn cuối cùng nếu có cuộc hội thoại ban đầu
        if (humanChatBody) {
             humanChatBody.scrollTop = humanChatBody.scrollHeight;
        }

    }
});
