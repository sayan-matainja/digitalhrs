<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Card - Back (Landscape)</title>
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
            width: 20%;
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

<div class="id-card">

    <!-- Header with logo (same as front) -->
    <div class="card-head" style="background-color: {{ $choices->background_color }}">
        <div class="card-logo">
            <img src="{{ $assets->back_logo ?? $assets->front_logo }}" alt="Company Logo">
        </div>
    </div>

    <!-- Main back content -->
    <div class="card-detail" style="text-align:center;">

        <div class="card-item-info">
            <h6>{{ $choices->back_title }}</h6>
            <p>
                {!! Str::of($choices->back_text ?? '')
                    ->split('/(?<=[.?!])\s+/')
                    ->take(2)
                    ->implode(' ') !!}
            </p>
        </div>


        <div class="footer">
            @if(isset($assets->signature_image))
                <div class="signature">
                    <img src="{{ $assets->signature_image }}" alt="Signature">
                    <span>Authorized Personnel</span>
                </div>
            @endif
            {{ $assets->footer_text ?? 'This card is the property of the company and must be returned upon termination of employment.' }}
        </div>

    </div>
</div>

</body>
</html>
