<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Card - Back</title>
    <style>
        .footer {
            font-size: 12px;
            color: #333;
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
        }
        .signature img {
            width: 40%;
            display: block;
            margin: 0 auto;
        }
        .signature span {
            border-top: 1px solid #333;
            padding-top: 3px;
            display: inline-block;
            margin: 5px 0;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="card">

    <div class="port-head" style="padding-bottom:10px;">
        <div class="port-logo">
        <img src="{{ $assets->back_logo ?? $assets->front_logo }}" alt="Logo">
        </div>
    </div>
    <div class="card-item-info" style="text-align:center; position:relative; padding:10px; height:100%;">
        <h6>{{ $choices->back_title }}</h6>
        <p>
            {!! Str::of($choices->back_text ?? '')
                ->split('/(?<=[.?!])\s+/')
                ->take(2)
                ->implode(' ') !!}
        </p>
        <div class="footer">
            @if(isset($assets->signature_image))
            <div class="signature">
                <img src="{{ $assets->signature_image }}" alt="Signature">
                <span>Authorized Personnel</span>
            </div>
            @endif
            {{ $assets->footer_text ?? 'www.company.com' }}
        </div>
    </div>

</div>

</body>
</html>
