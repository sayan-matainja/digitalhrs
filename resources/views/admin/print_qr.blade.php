<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('index.qr') }}</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f5f5f5;
        }
        .center {
            border: 5px solid #000;
            padding: 20px;
            text-align: center;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .scan-text {
            border: 2px solid #000;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 25px;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
        .qr {
            margin: 20px 0;
        }
    </style>
</head>

<body>
<div class="center">
    <h4 class="scan-text-url">{{ __('index.app_qr') }}</h4>
    @php
        $qrImage = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(400)
            ->format('svg')
            ->errorCorrection('H')
            ->generate($url);
    @endphp
    <div class="qr">{!! $qrImage !!}</div>
    <h6 class="scan-text">Scan Me</h6>
</div>
</body>
<script>
    window.print();
    window.onfocus = function () {
        window.close();
    }
</script>
</html>
