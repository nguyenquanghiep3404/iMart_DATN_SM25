<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Thêm thẻ meta CSRF Token cho Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Hỏi Đáp Sản Phẩm Apple</title>
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Marked.js for Markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        /* Custom styles */
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom scrollbar for chat box */
        #chat-box::-webkit-scrollbar {
            width: 6px;
        }
        #chat-box::-webkit-scrollbar-track {
            background: #f1f5f9; /* gray-100 */
        }
        #chat-box::-webkit-scrollbar-thumb {
            background: #94a3b8; /* gray-400 */
            border-radius: 3px;
        }
        #chat-box::-webkit-scrollbar-thumb:hover {
            background: #64748b; /* gray-500 */
        }
        .message-bubble-user {
            background-color: #007aff; /* Apple's classic blue */
            color: white;
        }
        .message-bubble-ai {
            background-color: #e5e5ea; /* A light gray, similar to iMessage */
            color: #1c1c1e;
        }
        /* Styling for Markdown content inside AI bubbles */
        .message-bubble-ai p {
            margin: 0;
        }
        .message-bubble-ai ul {
            list-style-type: disc;
            padding-left: 20px;
            margin-top: 8px;
            margin-bottom: 8px;
        }
         .message-bubble-ai ol {
            list-style-type: decimal;
            padding-left: 20px;
            margin-top: 8px;
            margin-bottom: 8px;
        }
        .message-bubble-ai li {
            margin-bottom: 4px;
        }
        .message-bubble-ai strong, .message-bubble-ai b {
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">

    <!-- Trigger Button to open Chatbot -->
    <button id="open-chatbot-btn" class="fixed bottom-5 right-5 bg-blue-600 text-white p-4 rounded-full shadow-lg hover:bg-blue-700 transition-transform duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 z-40">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
    </button>
    
    <!-- Chatbot Modal/Offcanvas -->
    <div id="chatbot-modal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div id="chatbot-backdrop" class="fixed inset-0 bg-black/50"></div>

        <!-- Chatbot Container -->
        <div id="chatbot-container" class="fixed top-0 right-0 h-full w-full max-w-md flex flex-col bg-white dark:bg-gray-800 shadow-2xl border-l border-gray-200 dark:border-gray-700 transform translate-x-full transition-transform duration-300 ease-in-out">
            
            <!-- Header -->
            <header class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-700 dark:text-gray-200 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.657 7.343A8 8 0 0117.657 18.657z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1014.12 11.88a3 3 0 00-4.242 4.242z" />
                    </svg>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800 dark:text-white">Trợ lý Sản phẩm Apple</h1>
                        <p class="text-sm text-green-500 font-medium">● Online</p>
                    </div>
                </div>
                <button id="close-chatbot-btn" class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <!-- Chat Box -->
            <main id="chat-box" class="flex-1 p-6 overflow-y-auto space-y-6">
                <!-- Initial AI Welcome Message -->
                <div class="flex items-start gap-3">
                    <div class="h-8 w-8 rounded-full bg-gray-600 flex items-center justify-center text-white flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="message-bubble-ai px-4 py-3 rounded-lg max-w-lg">
                        <p class="text-sm">Xin chào! Tôi là trợ lý AI của bạn. Bạn muốn hỏi gì về các sản phẩm của Apple như iPhone, MacBook, Apple Watch, hay các dịch vụ khác?</p>
                    </div>
                </div>
            </main>

            <!-- Loading Indicator -->
            <div id="loading-indicator" class="hidden flex items-center justify-start p-6">
                 <div class="flex items-start gap-3">
                    <div class="h-8 w-8 rounded-full bg-gray-600 flex items-center justify-center text-white flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="message-bubble-ai px-4 py-3 rounded-lg flex items-center space-x-2">
                        <div class="w-2 h-2 bg-gray-500 rounded-full animate-pulse" style="animation-delay: 0s;"></div>
                        <div class="w-2 h-2 bg-gray-500 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                        <div class="w-2 h-2 bg-gray-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                    </div>
                </div>
            </div>

            <!-- Input Form -->
            <footer class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0">
                <form id="message-form" class="flex items-center space-x-4">
                    <input type="text" id="message-input" placeholder="Nhập câu hỏi của bạn ở đây..."
                        class="flex-1 w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                    <button type="submit" id="send-button"
                        class="p-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-transform duration-200 active:scale-95 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </form>
            </footer>
        </div>
    </div>


    <script>
        // --- Modal Elements ---
        const openChatbotBtn = document.getElementById('open-chatbot-btn');
        const closeChatbotBtn = document.getElementById('close-chatbot-btn');
        const chatbotModal = document.getElementById('chatbot-modal');
        const chatbotContainer = document.getElementById('chatbot-container');
        const chatbotBackdrop = document.getElementById('chatbot-backdrop');

        // --- Chatbot Core Elements ---
        const chatBox = document.getElementById('chat-box');
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        const loadingIndicator = document.getElementById('loading-indicator');

        // Thêm prompt hệ thống vào đầu lịch sử chat
        const systemInstruction = "Bạn là một chuyên gia tư vấn về các sản phẩm của Apple. Hãy trả lời các câu hỏi một cách thân thiện, chi tiết và chính xác. Luôn sử dụng định dạng Markdown cho danh sách, chữ in đậm và các định dạng khác để câu trả lời dễ đọc. Chỉ tập trung vào các sản phẩm và dịch vụ của Apple. Nếu câu hỏi không liên quan đến Apple, hãy lịch sự từ chối và gợi ý hỏi về sản phẩm Apple.";
        
        let chatHistory = [{
            role: "user",
            parts: [{ text: systemInstruction }]
        }, {
            role: "model",
            parts: [{ text: "Chào bạn, tôi đã hiểu vai trò của mình. Tôi đã sẵn sàng trả lời các câu hỏi về sản phẩm Apple và sẽ định dạng câu trả lời cho dễ đọc." }]
        }];
        
        // --- Modal Logic ---
        function openChatbot() {
            chatbotModal.classList.remove('hidden');
            requestAnimationFrame(() => {
                chatbotContainer.classList.remove('translate-x-full');
            });
            messageInput.focus();
        }

        function closeChatbot() {
            chatbotContainer.classList.add('translate-x-full');
            setTimeout(() => {
                chatbotModal.classList.add('hidden');
            }, 300); // Must match transition duration
        }

        // --- Event Listeners ---
        openChatbotBtn.addEventListener('click', openChatbot);
        closeChatbotBtn.addEventListener('click', closeChatbot);
        chatbotBackdrop.addEventListener('click', closeChatbot);
        messageForm.addEventListener('submit', handleSendMessage);
        
        /**
         * Handles the form submission to send a message.
         * @param {Event} e The submission event.
         */
        async function handleSendMessage(e) {
            e.preventDefault();
            const userMessage = messageInput.value.trim();

            if (userMessage === '') return;

            displayMessage(userMessage, 'user');
            chatHistory.push({ role: "user", parts: [{ text: userMessage }] });

            messageInput.value = '';
            toggleForm(false);

            loadingIndicator.classList.remove('hidden');
            scrollToBottom();

            try {
                const aiResponse = await getGeminiResponse();
                chatHistory.push({ role: "model", parts: [{ text: aiResponse }] });
                displayMessage(aiResponse, 'ai');

            } catch (error) {
                console.error("Error fetching AI response:", error);
                displayMessage("Xin lỗi, tôi đang gặp sự cố kết nối đến máy chủ. Vui lòng thử lại sau.", 'ai');
            } finally {
                loadingIndicator.classList.add('hidden');
                toggleForm(true);
            }
        }
        
        function toggleForm(isEnabled) {
            messageInput.disabled = !isEnabled;
            sendButton.disabled = !isEnabled;
            if (isEnabled) {
                messageInput.focus();
            }
        }

        function displayMessage(message, sender) {
            const messageWrapper = document.createElement('div');
            
            if (sender === 'user') {
                messageWrapper.className = "flex items-start gap-3 justify-end";
                messageWrapper.innerHTML = `
                    <div class="message-bubble-user px-4 py-3 rounded-lg max-w-lg">
                        <p class="text-sm">${message}</p>
                    </div>
                    <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                           <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0012 11z" clip-rule="evenodd" />
                        </svg>
                    </div>
                `;
            } else {
                messageWrapper.className = "flex items-start gap-3 text-sm";
                // Convert Markdown to HTML using marked.js
                const formattedMessage = marked.parse(message); 
                messageWrapper.innerHTML = `
                    <div class="h-8 w-8 rounded-full bg-gray-600 flex items-center justify-center text-white flex-shrink-0">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                         </svg>
                    </div>
                    <div class="message-bubble-ai px-4 py-3 rounded-lg max-w-lg">
                        ${formattedMessage}
                    </div>
                `;
            }
            
            chatBox.appendChild(messageWrapper);
            scrollToBottom();
        }

        function scrollToBottom() {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        /**
         * Fetches a response from the Laravel backend.
         * @returns {Promise<string>} The text response from the AI.
         */
        async function getGeminiResponse() {
            // **** FIXED HERE: Removed the /api prefix ****
            const apiUrl = '/gemini-chat'; 
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const requestPayload = {
                chatHistory: chatHistory
            };

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken 
                },
                body: JSON.stringify(requestPayload),
            });

            if (!response.ok) {
                const errorData = await response.json();
                console.error("Backend Error:", errorData);
                // Throwing an error to be caught by the calling function
                throw new Error(`API request failed with status ${response.status}`);
            }

            const result = await response.json();
            
            if (result.candidates && result.candidates.length > 0 &&
                result.candidates[0].content && result.candidates[0].content.parts &&
                result.candidates[0].content.parts.length > 0) {
                return result.candidates[0].content.parts[0].text;
            } else {
                console.error("Unexpected API response structure from backend:", result);
                return "Tôi không thể xử lý yêu cầu này ngay bây giờ.";
            }
        }
    </script>
</body>
</html>
