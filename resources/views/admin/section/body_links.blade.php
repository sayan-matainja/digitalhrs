<!-- core:js -->
<script src="{{asset('assets/vendors/core/core.js') }}"></script>
<!-- endinject -->

<!-- Plugin js for this page -->
<script src="{{asset('assets/vendors/chartjs/Chart.min.js')}}"></script>
<script src="{{asset('assets/vendors/jquery.flot/jquery.flot.js')}}"></script>
<script src="{{asset('assets/vendors/jquery.flot/jquery.flot.resize.js')}}"></script>
<script src="{{asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
<script src="{{asset('assets/vendors/apexcharts/apexcharts.min.js')}}"></script>
<!-- End plugin js for this page -->

<!-- inject:js -->
<script src="{{asset('assets/vendors/feather-icons/feather.min.js')}}"></script>
<script src="{{asset('assets/js/template.js')}}"></script>
<!-- endinject -->

<!-- Custom js for this page -->
<script src="{{asset('assets/js/dashboard-light.js')}}"></script>
<script src="{{asset('assets/js/datepicker.js')}}"></script>


<!-- End custom js for this page -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- sweet alert -->
{{--<script defer src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>--}}
<script src="{{asset('assets/vendors/sweetalert2/sweetalert2.min.js')}}"></script>


{{--<script src="https://common.olemiss.edu/_js/sweet-alert/sweet-alert.min.js"></script>--}}
{{--<link rel="stylesheet" type="text/css" href="https://common.olemiss.edu/_js/sweet-alert/sweet-alert.css">--}}
<script src="{{asset('assets/js/nepaliDatepicker.min.js')}}" type="text/javascript"></script>

<script>
    $('div.alert.alert-danger').not('.alert-important').delay(10000).slideUp(900);
    $('div.alert.alert-success').not('.alert-important').delay(5000).slideUp(900);
</script>


