  <script src="{{ asset('assets/assets-back/vendors/js/vendors.min.js') }}" type="text/javascript"></script>
  <!-- BEGIN VENDOR JS-->
  <!-- BEGIN PAGE VENDOR JS-->
  <script src="{{ asset('assets/assets-back/vendors/js/charts/chart.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('assets/assets-back/vendors/js/charts/raphael-min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('assets/assets-back/vendors/js/charts/morris.min.js') }}" type="text/javascript"></script>
  <!-- END PAGE VENDOR JS-->
  <!-- BEGIN MODERN JS-->
  <script src="{{ asset('assets/assets-back/js/core/app-menu.js') }}" type="text/javascript"></script>
  <script src="{{ asset('assets/assets-back/js/core/app.js') }}" type="text/javascript"></script>
  <script src="{{ asset('assets/assets-back/js/scripts/customizer.js') }}" type="text/javascript"></script>
  <!-- END MODERN JS-->
  <!-- BEGIN PAGE LEVEL JS-->
  <script src="{{ asset('assets/assets-back/js/scripts/pages/dashboard-sales.js') }}" type="text/javascript"></script>
  <!-- END PAGE LEVEL JS-->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- for toaster-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.js"></script>
<!-- for toaster-->
  @include('dashboard.partials.toaster_notification')
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const translations = {
        new_order_text: "{{ __('dashboard.new_customer_placed_an_order_with_total_price') }}",
        currency: "{{ __('dashboard.eg.currency') }}"
    };
</script>

<script>
    window.notificationSound = "{{ asset('assets/sound.mp3') }}";
</script>

<script src="{{ asset('build/assets/app-CtZ7iwrp.js') }}"></script>

@stack('js')
