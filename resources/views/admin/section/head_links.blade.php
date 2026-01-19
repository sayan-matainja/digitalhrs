
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<!-- End fonts -->

{{--<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />--}}
<link rel="stylesheet" href="{{asset('assets/vendors/select2/select2.min.css')}}">

<!--nepali datePicker -->
<link rel="stylesheet" href=" {{asset('assets/css/nepaliDatepicker.min.css') }} " type="text/css">
<!-- end-->

<!-- core:css -->
<link rel="stylesheet" href=" {{asset('assets/vendors/core/core.css') }} ">
<!-- end -->

<!-- Plugin css for this page -->
<link rel="stylesheet" href="{{asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
<!-- End plugin css for this page -->

<!-- inject:css -->
<link rel="stylesheet" href="{{asset('assets/fonts/feather-font/css/iconfont.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendors/flag-icon-css/css/flag-icon.min.css')}}">
<!-- endinject -->

<!-- Layout styles -->

<link rel="stylesheet" href="{{ (\App\Helpers\AppHelper::getTheme() == 'dark') ? asset('assets/css/style_dark.css') : asset('assets/css/style.css') }}" id="themeColor">

<!-- End layout styles -->
<!-- RTL -->
@if(in_array(App::getLocale(),['ar','fa']))
    <link rel="stylesheet" href="{{ asset('assets/css/rtl_style.css') }}" id="rtl">
@endif


<link rel="shortcut icon" href="{{asset('assets/images/favicon.png')}}" />
<link rel="stylesheet" href="{{asset('assets/vendors/sweetalert2/sweetalert2.min.css')}}"/>














