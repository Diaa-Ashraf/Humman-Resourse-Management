<style>
    /* إعادة استخدام كل الأنماط من صفحة المستخدمين مع تعديلات بسيطة */
    .content-wrapper, .content-body, .row, .col-12 {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Switch CSS */
    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 26px;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: #ccc;
      transition: 0.4s;
      border-radius: 34px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: 0.4s;
      border-radius: 50%;
    }

    input:checked + .slider {
      background-color: #28a745;
    }

    input:focus + .slider {
      box-shadow: 0 0 1px #28a745;
    }

    input:checked + .slider:before {
      transform: translateX(24px);
    }

    /* الكارد الرئيسي - أبيض نظيف */
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

    /* Table Styles - تحسينات جديدة */
    .table-responsive {
        max-height: 700px;
        overflow-y: auto;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        margin: 20px auto;
        background: white;
        border: 1px solid #f1f5f9;
        width: calc(100% - 40px);
    }

    table.table {
        font-size: 1rem;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    /* تحسين الهيدر - ألوان برتقالي ذهبي */
    .table thead th {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        color: white !important;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 16px 8px;
        border: none;
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
    }

    /* تحسين الصفوف */
    .table tbody tr {
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
        height: 65px;
    }

    .table tbody tr:hover {
        background-color: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
        border-left: 3px solid #f59e0b;
    }

    .table tbody td {
        padding: 14px 8px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* تنسيق الصفوف الزوجية والفردية */
    .table tbody tr:nth-child(even) {
        background-color: #fafbff;
    }

    .table tbody tr:nth-child(even):hover {
        background-color: #f0f4ff;
    }

    /* تحسين الـ Badges - ألوان برتقالي ذهبي */
    .badge-category-name {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }

    .badge-status {
        padding: 8px 12px;
        border-radius: 16px;
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
    }

    /* زر الإضافة - ألوان برتقالي ذهبي */
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-bottom: 1px solid #b45309;
        padding: 25px 30px;
        border-radius: 16px 16px 0 0;
    }

    .add-country-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 25px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        font-size: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .add-country-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        color: white;
        text-decoration: none;
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    /* تحسين الـ Pagination - توسيط */
    .pagination {
        margin: 20px 0 0 0;
        padding: 15px 0;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: center;
    }

    .page-link {
        color: #64748b;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        margin: 0 2px;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        font-weight: 500;
        background: white;
    }

    .page-link:hover {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-color: #f59e0b;
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-color: #f59e0b;
    }

    /* حالة عدم وجود بيانات */
    .no-data-cell {
        padding: 60px !important;
        text-align: center;
        color: #64748b;
        font-style: italic;
        font-size: 1.1rem;
        background: #f8fafc;
    }

    /* تحسين العنوان */
    .content-header-title {
        color: white !important;
        font-weight: 700;
        font-size: 1.6rem;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* تحسين الأرقام في العمود الأول - أصغر بكثير */
    .table tbody th {
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        padding: 14px 2px;
        width: 30px !important;
        min-width: 30px !important;
        max-width: 30px !important;
        text-align: center;
    }

    /* جعل الـ container يأخذ المساحة كاملة - خلفية بيضاء */
    .content-wrapper {
        min-height: 100vh;
        background: #ffffff;
    }

    .content-body {
        min-height: calc(100vh - 100px);
        background: #ffffff;
    }

    /* تحسين الـ Tostar */
    .tostar-success {
        margin: 0 auto 20px auto;
        width: calc(100% - 40px);
    }

    /* تحسين أيقونة العنوان */
    .title-icon {
        color: #fef3c7;
        margin-right: 12px;
    }

    /* توزيع أفضل للأعمدة */
    .col-id {
        width: 30px !important;
        min-width: 30px !important;
        max-width: 30px !important;
        padding: 8px 2px !important;
    }

    .col-name {
        width: 25% !important;
        min-width: 200px;
    }

    .col-actions {
        width: 15% !important;
        min-width: 120px;
    }

    /* تحسين الهيدر للعمود الأول */
    .table thead th.col-id {
        width: 30px !important;
        min-width: 30px !important;
        max-width: 30px !important;
        padding: 16px 2px !important;
        font-size: 0.9rem;
    }

    /* Card Body لتوسيط المحتوى */
    .card-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
    }

    /* Table Section لتوسيط الجدول */
    .table-section {
        width: 100%;
        display: flex;
        justify-content: center;
    }

    /* تنسيق أزرار الإجراءات */
    .action-buttons {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .btn-edit {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
    }

    .btn-edit:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
    }

    .btn-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-delete:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.4);
    }

    .delete-form {
        margin: 0;
        padding: 0;
        display: inline;
    }

    /* تحسين الـ Switch */
    .switch {
        transform: scale(0.9);
        margin-left: 10px;
    }

    .switch:hover .slider {
        box-shadow: 0 0 6px rgba(245, 158, 11, 0.4);
    }

    /* تحسين حاوية الـ status */
    .status-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    /* صورة الطبيب */
    .avatar-placeholder {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
    }
</style>