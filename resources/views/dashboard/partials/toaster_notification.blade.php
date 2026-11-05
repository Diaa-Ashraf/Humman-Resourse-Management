@php
    $hrToastFlash = [];
    foreach (['success', 'error', 'warning', 'info'] as $flashType) {
        if (session()->has($flashType)) {
            $hrToastFlash[] = ['type' => $flashType, 'message' => (string) session($flashType)];
        }
    }
    if (session()->has('message')) {
        $hrToastFlash[] = ['type' => session('alert-type', 'info'), 'message' => (string) session('message')];
    }
    if (session()->has('status')) {
        $hrToastFlash[] = ['type' => 'success', 'message' => (string) session('status')];
    }
    $hrToastErrors = $errors->all();
@endphp
<script>
(function () {
    if (!window.toastr) return;

    toastr.options = {
        closeButton: true,
        progressBar: true,
        newestOnTop: true,
        preventDuplicates: false,
        escapeHtml: true,
        positionClass: 'toast-top-left',
        rtl: true,
        containerId: 'toast-container',
        timeOut: 5200,
        extendedTimeOut: 1400,
        showDuration: 220,
        hideDuration: 320,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    };

    const queued = [];
    const seen = new Set();
    const normalize = value => String(value || '').replace(/\s+/g, ' ').trim();
    const addToast = (type, message, title, duration) => {
        const cleanMessage = String(message || '').replace(/[^\S\r\n]+/g, ' ').replace(/\r\n/g, '\n').trim();
        if (!cleanMessage) return;
        const key = type + ':' + normalize(cleanMessage);
        if (seen.has(key)) return;
        seen.add(key);
        queued.push({ type, message: cleanMessage, title, duration });
    };

    (@json($hrToastFlash)).forEach(item => {
        const type = ['success', 'error', 'warning', 'info'].includes(item.type) ? item.type : 'info';
        const title = { success: 'تم بنجاح', error: 'تعذر إتمام العملية', warning: 'تنبيه', info: 'معلومة' }[type];
        addToast(type, item.message, title, type === 'error' ? 7200 : 5200);
    });

    const validationErrors = @json($hrToastErrors);
    if (validationErrors.length) {
        addToast('error', validationErrors.map(message => '• ' + message).join('\n'), 'يرجى مراجعة البيانات', 8200);
    }

    document.querySelectorAll('.alert.alert-success, .alert.alert-danger, .alert.alert-warning').forEach(alert => {
        const alertContent = alert.cloneNode(true);
        alertContent.querySelectorAll('.close, .btn-close, button, [aria-hidden="true"]').forEach(element => element.remove());
        const text = alertContent.innerText || alertContent.textContent;
        const isErrorAlert = alert.classList.contains('alert-danger');
        if (text && !(isErrorAlert && validationErrors.length)) {
            const type = alert.classList.contains('alert-success') ? 'success' : (isErrorAlert ? 'error' : 'warning');
            const title = { success: 'تم بنجاح', error: 'تنبيه', warning: 'تنبيه' }[type];
            addToast(type, text, title, type === 'error' ? 7200 : 5200);
        }
        alert.remove();
    });

    queued.forEach(item => {
        const options = { timeOut: item.duration };
        toastr[item.type](item.message, item.title, options);
    });
})();
</script>
