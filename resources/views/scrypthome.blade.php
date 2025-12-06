<script>
$(function(){
    $('.status-toggle').on('change', function(){
        let checkbox = $(this);
        let doctorId = checkbox.data('id');
        let newStatus = checkbox.is(':checked') ? 1 : 0;
        let token = '{{ csrf_token() }}';
        checkbox.prop('disabled', true);

        $.ajax({
            url: "{{ route('admin.doctors.change-status', $doctor->id) }}",
            method: "POST",
            data: {
                _token: token,
                id: doctorId,
                status: newStatus,
            },
            success: function(response){
                if(response.status == 200){
                    let statusBadge = $('#status_' + doctorId);
                    if(response.data.status == 1){
                        statusBadge.removeClass('badge-danger').addClass('badge-success')
                            .html('<i class="fas fa-check-circle"></i> {{ __("doctor.active") }}');
                    } else {
                        statusBadge.removeClass('badge-success').addClass('badge-danger')
                            .html('<i class="fas fa-times-circle"></i> {{ __("doctor.inactive") }}');
                    }
                } else {
                    alert("{{ __('doctor.status_update_failed') }}");
                    checkbox.prop('checked', !checkbox.is(':checked'));
                }
            },
            error: function(){
                alert("{{ __('doctor.status_update_failed') }}");
                checkbox.prop('checked', !checkbox.is(':checked'));
            },
            complete: function(){
                checkbox.prop('disabled', false);
            }
        });
    });
});
</script>