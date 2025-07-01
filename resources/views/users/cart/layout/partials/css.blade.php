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
</style>
