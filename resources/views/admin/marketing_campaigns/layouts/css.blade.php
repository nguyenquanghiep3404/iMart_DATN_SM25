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

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        /* gray-50 */
    }

    /* Custom styled select arrow */
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* Ensure icons have correct size */
    i[data-lucide] {
        width: 1em;
        height: 1em;
    }

    select#selectedCoupon {
        position: relative;
        z-index: 9999;
        appearance: none;
        /* reset style mặc định, tùy chọn */
    }
</style>
