<script>
"use strict";

toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "3000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

@if ($errors->any())
    @foreach ($errors->all() as $emsg)
        toastr.error('{{ $emsg }}');
    @endforeach
@endif

@if(session()->has('alert'))
    @if(session('alert')[0] == 'danger')
        toastr.error('{{ session('alert')[1] }}');
    @elseif(session('alert')[0] == 'success')
        toastr.success('{{ session('alert')[1] }}');
    @else
        toastr.error('{{ session('alert')[1] }}');
    @endif
@endif

function systemAlert(type, message) {
    if (type === 'danger') {
        toastr.error(message);
    } else if (type === 'success') {
        toastr.success(message);
    } else {
        toastr.error(message);
    }
}

$('.language').change(function () {
    window.location.href = `{{ url('/language') }}/${$(this).val()}`;
});
</script>