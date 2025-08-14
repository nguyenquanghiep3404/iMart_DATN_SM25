@push('styles')
    <style>
        /* Custom styles */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            /* Light gray background */
        }

        .gallery-top .swiper-slide {
            height: 700px;
            /* Chiều cao cố định của khung */
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f8f8;
            /* màu nền dễ nhìn nếu ảnh không lấp đầy */
            overflow: hidden;
        }

        .gallery-top .swiper-slide img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            /* <-- giữ nguyên tỉ lệ, không cắt */
            width: auto;
            height: auto;
            border-radius: 8px;
        }


        .carousel::-webkit-scrollbar {
            display: none;
        }

        .carousel {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .accordion-icon {
            transition: transform 0.3s ease;
        }

        .tab-button {
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease-in-out;
        }

        .tab-active {
            border-color: #3b82f6;
            /* blue-500 */
            color: #2563eb;
            /* blue-600 */
            background-color: #eff6ff;
            /* blue-50 */
        }

        .description-content.collapsed {
            max-height: 300px;
            overflow: hidden;
            position: relative;
        }

        .description-content.collapsed::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to top, white, rgba(255, 255, 255, 0));
        }

        #favorite-btn.favorited {
            color: #ef4444;
            /* red-500 */
        }

        #sticky-bar {
            transition: transform 0.3s ease-in-out;
        }

        #lightbox-main-image {
            transition: transform 0.3s ease;
            cursor: zoom-in;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .gallery-top {
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .gallery-thumbs {
            padding-top: 0.5rem;
        }

        .gallery-thumbs .swiper-slide {
            width: 20%;
            height: auto;
            opacity: 0.6;
            cursor: pointer;
            transition: opacity 0.3s ease;
            border: 2px solid transparent;
            border-radius: 0.375rem;
        }

        .gallery-thumbs .swiper-slide:hover {
            opacity: 1;
        }

        .gallery-thumbs .swiper-slide-thumb-active {
            opacity: 1;
            border-color: #3b82f6;
        }

        .gallery-thumbs .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            aspect-ratio: 1 / 1;
            border-radius: 0.25rem;
        }

        .thumb-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.9);
            padding: 0.5rem;
            border-radius: 9999px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            z-index: 10;
            transition: opacity 0.2s;
        }

        .thumb-nav-btn:hover {
            background-color: white;
        }

        .thumb-nav-btn.swiper-button-disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .thumb-prev-btn {
            left: -12px;
        }

        .thumb-next-btn {
            right: -12px;
        }

        #main-gallery-prev-btn.swiper-button-disabled,
        #main-gallery-next-btn.swiper-button-disabled {
            opacity: 0.2;
            cursor: not-allowed;
        }
    </style>
@endpush
