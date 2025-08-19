<style>
    #slide-alert {
        position: fixed;
        top: 24px;
        right: 0;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.3s ease;
        z-index: 9999;
        background: linear-gradient(135deg, #e3342f, #cc1f1a);
        color: #fff;
        padding: 14px 22px;
        border-radius: 8px 0 0 8px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        gap: 14px;
        opacity: 0;
        pointer-events: none;
        font-size: 15px;
        max-width: 360px;
        min-height: 48px;
    }

    #slide-alert.show {
        transform: translateX(0);
        opacity: 1;
        pointer-events: auto;
    }

    #slide-alert .close-btn {
        cursor: pointer;
        font-weight: bold;
        font-size: 20px;
        line-height: 1;
        opacity: 0.75;
        background: none;
        border: none;
        color: inherit;
        padding: 0 6px;
        transition: opacity 0.2s ease;
    }

    #slide-alert .close-btn:hover {
        opacity: 1;
    }

    .slide-alert {
        position: fixed;
        top: 20px;
        right: -400px;
        padding: 15px 20px;
        margin-top: 10px;
        border-radius: 8px;
        color: #fff;
        z-index: 9999;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: right 0.4s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .slide-alert.success {
        background-color: #28a745;
    }

    .slide-alert.error {
        background-color: #dc3545;
    }

    .slide-alert.show {
        right: 20px;
    }

    .slide-alert .close-btn {
        background: none;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
    }

    .count-input {
        max-width: 130px;
        /* Giới hạn chiều rộng toàn bộ cụm */
        border: 1px solid #ddd;
        border-radius: 6px;
        overflow: hidden;
    }

    .count-input .btn {
        padding: 0 8px;
        height: 36px;
        font-size: 16px;
        border: none;
        background: transparent;
        box-shadow: none;
    }

    .count-input .quantity-input {
        width: 40px;
        height: 36px;
        padding: 0;
        border: none;
        box-shadow: none;
        font-size: 16px;
        background-color: transparent;
    }

    @keyframes slideInRightCustom {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .toast {
        animation: slideInRightCustom 0.5s ease forwards !important;
    }

    .count-input {
        display: flex;
        align-items: center;
        border: 1px solid #d1d5db;
        /* border-gray-300 */
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .count-input button {
        width: 40px;
        height: 40px;
        background: white;
        border: none;
        font-size: 1.5rem;
        color: #374151;
        /* text-gray-700 */
        cursor: pointer;
        transition: background 0.2s;
    }

    .count-input button:hover {
        background: #f3f4f6;
        /* hover:bg-gray-100 */
    }

    body {
        font-family: 'Be Vietnam Pro', sans-serif;
    }

    /* Custom scrollbar for better aesthetics */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Custom radio button appearance */
    .custom-radio {
        appearance: none;
        -webkit-appearance: none;
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        outline: none;
        transition: all 0.2s;
        cursor: pointer;
    }

    .custom-radio:checked {
        border-color: #ef4444;
        /* red-500 */
        background-color: #ef4444;
        /* red-500 */
        box-shadow: 0 0 0 3px white, 0 0 0 5px #ef4444;
        /* red-500 */
    }

    .promo-item.selected {
        border-color: #ef4444;
        /* red-500 */
        background-color: #fef2f2;
        /* red-50 */
    }

    #loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.3);
        /* giảm mờ nền */
        z-index: 9999;

        /* căn giữa loader theo cả chiều ngang và dọc */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .loader {
        /* dùng flex theo chiều cột, căn giữa nội dung */
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .loader img {
        width: 100px;
        height: auto;
        animation: bounce 1s infinite;
        display: block;
        /* tránh khoảng trống dưới ảnh */
        margin: 0;
    }

    .loader p {
        font-size: 18px;
        font-weight: bold;
        color: #333;
        margin-top: 10px;
        line-height: 1.2;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }
</style>
