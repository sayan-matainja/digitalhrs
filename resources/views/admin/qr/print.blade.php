<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@lang('index.attendance_qr') </title>
    <style>
        .center {
            border: 5px solid;
            position: absolute;
            padding: 20px;
            Text-align:center
        }
        .scan-text {
            border: 2px solid;
            padding: 20px;
            Text-align:center;
            border-radius: 25px;
        }
        .qr {
            padding: 20px;
            border: 20px;
        }
    </style>
</head>

<body>
    <div class="center">
        <h1 >{{$qrData->title}}</h1>
        <div class="qr">{!! $qrData->qr_code !!}</div>
        <h1 class="scan-text" >@lang('index.scan_me') </h1>
    </div>
</body>

<script>

    window.print();
    window.onfocus = function () {
        window.close();
    }

</script>
</html>
