<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f3f4f6;
    }

    .card-custom {
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        background-color: #fff;
    }

    .card-custom-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background-color: #f9fafb;
    }

    .card-custom-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }

    .card-custom-body {
        padding: 1.5rem;
    }

    .card-custom-footer {
        background-color: #f9fafb;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }

    .btn {
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1.25rem;
        border: 1px solid transparent;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .btn-primary {
        background-color: #4f46e5;
        color: white;
    }

    .btn-primary:hover {
        background-color: #4338ca;
    }

    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        border-color: #d1d5db;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
    }

    .btn-info {
        background-color: #3b82f6;
        color: white;
    }

    .btn-info:hover {
        background-color: #2563eb;
    }

    .btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        font-size: 0.875rem;
        background-color: white;
    }

    .form-input:focus,
    .form-select:focus {
        border-color: #4f46e5;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
    }

    .table-custom {
        width: 100%;
        min-width: 800px;
        color: #374151;
    }

    .table-custom th,
    .table-custom td {
        padding: 0.75rem 1rem;
        vertical-align: middle !important;
        border-bottom-width: 1px;
        border-color: #e5e7eb;
        white-space: nowrap;
    }

    .table-custom thead th {
        font-weight: 600;
        color: #4b5563;
        background-color: #f9fafb;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        text-align: left;
        border-bottom-width: 2px;
    }

    .status-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        font-size: 0.8rem;
    }

    .status-sent {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-pending {
        background-color: #e5e7eb;
        color: #6b7280;
    }

    [x-cloak] {
        display: none !important;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f3f4f6;
    }

    .card-custom {
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        background-color: #fff;
        overflow: hidden;
        /* Ensures child elements respect border radius */
    }

    .card-custom-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background-color: #f9fafb;
    }

    .card-custom-title {
        font-size: 1.125rem;
        /* 18px */
        font-weight: 600;
        color: #1f2937;
    }

    .card-custom-body {
        padding: 1.5rem;
    }

    .card-custom-body.p-0 {
        padding: 0;
    }

    .card-custom-footer {
        background-color: #f9fafb;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1.25rem;
        border: 1px solid transparent;
    }

    .btn-primary {
        background-color: #4f46e5;
        color: white;
    }

    .btn-primary:hover {
        background-color: #4338ca;
    }

    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        border-color: #d1d5db;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
    }

    .btn-info {
        background-color: #3b82f6;
        color: white;
    }

    .btn-info:hover {
        background-color: #2563eb;
    }

    .btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .table-custom {
        width: 100%;
        color: #374151;
    }

    .table-custom th,
    .table-custom td {
        padding: 0.75rem 1.5rem;
        vertical-align: middle !important;
        border-bottom-width: 1px;
        border-color: #e5e7eb;
        white-space: nowrap;
    }

    .table-custom tr:last-child td {
        border-bottom-width: 0;
    }

    .table-custom thead th {
        font-weight: 600;
        color: #4b5563;
        background-color: #f9fafb;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        text-align: left;
    }

    .form-textarea {
        width: 100%;
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        font-size: 0.875rem;
        background-color: white;
    }

    .form-textarea:focus {
        border-color: #4f46e5;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
    }

    /* Timeline styles */
    .timeline {
        position: relative;
        padding-left: 2.5rem;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.8rem;
        top: 4px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background-color: #fff;
        border: 3px solid #6b7280;
        /* Gray */
    }

    .timeline-item.timeline-email::before {
        border-color: #4f46e5;
    }

    /* Indigo */
    .timeline-item.timeline-notification::before {
        border-color: #3b82f6;
    }

    /* Blue */
    .timeline-item.timeline-system::before {
        border-color: #6b7280;
    }

    /* Gray for system */
    .timeline-item.timeline-recovered::before {
        border-color: #10b981;
    }

    /* Green */


    .timeline::after {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 4px;
        bottom: 4px;
        width: 2px;
        background-color: #e5e7eb;
    }
</style>
