<style>
    /* إعادة استخدام الأنماط الأساسية */
    .content-wrapper, .content-body, .row, .col-12 {
        margin: 0 !important;
        padding: 0 !important;
    }

    .main-card {
        background: #ffffff;
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin: 20px;
        min-height: 85vh;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-bottom: 1px solid #b45309;
        padding: 25px 30px;
        border-radius: 16px 16px 0 0;
    }

    .content-header-title {
        color: white !important;
        font-weight: 700;
        font-size: 1.6rem;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .title-icon {
        color: #fef3c7;
        margin-right: 12px;
    }

    .add-country-btn {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 25px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(107, 114, 128, 0.3);
        font-size: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .add-country-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
        color: white;
        text-decoration: none;
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
    }

    .card-body {
        padding: 30px;
        flex: 1;
    }

    /* تنسيق النموذج */
    .coach-form {
        max-width: 100%;
    }

    .form-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid #e2e8f0;
    }

    .section-title {
        color: #1e293b;
        font-weight: 600;
        font-size: 1.2rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f59e0b;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #f59e0b;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }

    .form-text {
        font-size: 0.875rem;
        margin-top: 5px;
        color: #6b7280;
    }

    /* تنسيق الأزرار */
    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        margin-top: 20px;
    }

    .btn {
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }

    .btn-save {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
    }

    .btn-save:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .btn-cancel {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        box-shadow: 0 2px 6px rgba(107, 114, 128, 0.3);
    }

    .btn-cancel:hover {
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
        color: white;
    }

    /* رسائل الخطأ */
    .text-danger {
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 5px;
    }

    /* الـ Tostar */
    .tostar-success-container {
        margin-bottom: 25px;
    }

    /* تحسينات إضافية */
    .text-danger {
        color: #dc2626 !important;
    }

    input:required, select:required {
        border-left: 3px solid #f59e0b;
    }

    /* تحسين الـ select */
    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    /* تحسين الـ file input */
    input[type="file"] {
        padding: 8px 15px;
    }

    input[type="file"]::file-selector-button {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        margin-right: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    input[type="file"]::file-selector-button:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    }
</style>