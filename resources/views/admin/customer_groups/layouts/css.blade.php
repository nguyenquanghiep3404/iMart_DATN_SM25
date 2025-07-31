<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        /* gray-50 */
    }

    /* Custom scrollbar for better aesthetics */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
        /* gray-100 */
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        /* gray-300 */
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
        /* gray-400 */
    }

    /* Modal styles */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
    }

    @keyframes fade-in-scale {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-fade-in-scale {
        animation: fade-in-scale 0.3s forwards cubic-bezier(0.16, 1, 0.3, 1);
    }
</style>
