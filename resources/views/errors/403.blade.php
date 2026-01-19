<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Digital HR Complete HR Attendance System">
    <meta name="author" content="Digital HR">
    <meta name="keywords" content="Attendance, Digital HR">

    <title>403</title>
    @include('admin.section.head_links')
</head>
<body>
<section class="403-error py-5">
    <div class="container">
        <div class="403-error-inner w-75 mx-auto text-center">
            <img src="{{asset('assets/images/403-error.png')}}" class="w-50" alt="403">
            <h3 class="mt-2 mb-0">403 Permission Denied</h3>
            <a href="{{route('admin.dashboard')}}">Back to home</a>
        </div>
    </div>
</section>
@include('admin.section.body_links')
</body>
</html>
