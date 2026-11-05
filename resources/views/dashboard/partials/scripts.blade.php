<script src="{{ asset('assets/assets-back/vendors/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/assets-back/vendors/js/extensions/toastr.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('[data-hr-menu-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const sidebar = document.querySelector('[data-hr-sidebar]');
            const overlay = document.querySelector('.hr-sidebar-overlay');
            if (!sidebar || window.innerWidth >= 992) return;
            sidebar.classList.toggle('is-open');
            overlay?.classList.toggle('is-visible');
        });
    });
    const hrSidebar = document.querySelector('[data-hr-sidebar]');
    const hrOverlay = document.querySelector('.hr-sidebar-overlay');
    if (hrOverlay) {
        hrOverlay.addEventListener('click', function () {
            hrSidebar?.classList.remove('is-open');
            hrOverlay.classList.remove('is-visible');
        });
    }
    document.querySelectorAll('.hr-sidebar .hr-side-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) {
                hrSidebar?.classList.remove('is-open');
                hrOverlay?.classList.remove('is-visible');
            }
        });
    });
</script>
@stack('js')
@include('dashboard.partials.toaster_notification')
