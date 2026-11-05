<!-- Modal تأكيد الحذف الموحد -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" style="direction: rtl; text-align: right;">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
    <div class="modal-content" style="border: 0; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.18); overflow: hidden;">
      <div class="modal-body p-4 text-center">
        <!-- أيقونة التحذير التفاعلية -->
        <div style="width: 72px; height: 72px; margin: 0 auto 18px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #ef4444; font-size: 36px; box-shadow: 0 8px 16px rgba(239, 68, 68, 0.15);">
          <i class="la la-trash-alt"></i>
        </div>

        <h4 id="deleteConfirmModalLabel" style="font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 8px;">تأكيد الحذف</h4>
        <p id="deleteConfirmMessage" style="font-size: 14.5px; color: #64748b; margin-bottom: 24px; line-height: 1.6;">هل أنت متأكد من رغبتك في حذف هذا العنصر نهائياً؟ لن يمكنك التراجع عن هذا الإجراء.</p>

        <div style="display: flex; gap: 12px; justify-content: center;">
          <button type="button" class="btn" data-bs-dismiss="modal" data-dismiss="modal" style="flex: 1; padding: 11px 20px; border-radius: 12px; background: #f1f5f9; color: #475569; font-weight: 700; border: none; font-size: 14.5px; transition: background 0.2s;">
            إلغاء
          </button>
          <button type="button" id="confirmDeleteSubmitBtn" class="btn" style="flex: 1; padding: 11px 20px; border-radius: 12px; background: #ef4444; color: #fff; font-weight: 700; border: none; font-size: 14.5px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transition: all 0.2s;">
            نعم، احذف
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let activeDeleteForm = null;
    const deleteModalEl = document.getElementById('deleteConfirmModal');
    const deleteMessageEl = document.getElementById('deleteConfirmMessage');
    const confirmBtn = document.getElementById('confirmDeleteSubmitBtn');

    // تفعيل نافذة الحذف المودال عند الضغط على أي زر حذف أو إرسال فورم حذف
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('button, a');
        if (!btn) return;

        // التحقق مما إذا كان الزر مخصص للحذف
        const form = btn.closest('form');
        const isDeleteAction = (form && form.querySelector('input[name="_method"][value="DELETE"]')) || 
                               btn.hasAttribute('onclick') && btn.getAttribute('onclick').includes('confirm') ||
                               btn.classList.contains('btn-delete') || 
                               btn.classList.contains('delete-btn');

        if (isDeleteAction && form) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            activeDeleteForm = form;

            // استخراج رسالة التأكيد المخصصة إن وجدت
            let customMsg = 'هل أنت متأكد من رغبتك في حذف هذا العنصر نهائياً؟ لا يمكن التراجع بعد إتمام الحذف.';
            const onclickAttr = btn.getAttribute('onclick') || (form.hasAttribute('onsubmit') ? form.getAttribute('onsubmit') : '');
            const match = onclickAttr.match(/confirm\(['"](.*?)['"]\)/);
            if (match && match[1]) {
                customMsg = match[1];
            } else if (btn.getAttribute('data-confirm-message')) {
                customMsg = btn.getAttribute('data-confirm-message');
            }

            deleteMessageEl.textContent = customMsg;

            // فتح المودال باستخدام Bootstrap (Bootstrap 5 أو Bootstrap 4)
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
                modalInstance.show();
            } else if (window.jQuery && $(deleteModalEl).modal) {
                $(deleteModalEl).modal('show');
            } else {
                deleteModalEl.style.display = 'block';
                deleteModalEl.classList.add('show');
            }
            return false;
        }
    }, true);

    // عند الضغط على زر "نعم، احذف" داخل المودال
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (activeDeleteForm) {
                // إزالة أي onsubmit قديمة لتفادي ظهور نافذة المتصفح مجدداً
                activeDeleteForm.onsubmit = null;
                activeDeleteForm.removeAttribute('onsubmit');
                const submitBtn = activeDeleteForm.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn) {
                    submitBtn.onclick = null;
                    submitBtn.removeAttribute('onclick');
                }
                activeDeleteForm.submit();
            }
        });
    }
});
</script>

