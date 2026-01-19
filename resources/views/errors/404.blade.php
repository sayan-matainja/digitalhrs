<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <meta name="description" content="Digital HR Complete HR Attendance System">
    <meta name="author" content="Digital HR">
    <meta name="keywords" content="Attendance, Digital HR">

    <title>404</title>
    @include('admin.section.head_links')
</head>
<body>
    <div class="container">
        <div class="main-wrapper">
            <div class="page-wrapper full-page">
                <div class="page-content d-flex align-items-center justify-content-center">
                    <div class="row w-100 mx-0 auth-page">
                        <div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">
                            <img src="{{asset('assets/images/404-attendance.png')}}" class="img-fluid mb-2 mt-6" alt="404">
                            <a href="{{route('admin.dashboard')}}">Back to home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('admin.section.body_links')
</body>
</html>





