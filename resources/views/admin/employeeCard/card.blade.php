
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ID Card - {{ $employee->employee_code }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>

        .page {
            page-break-after:always;
            min-height:100px;
            display:flex;
            border-radius: 4mm;
            justify-content:center;
            align-items:center;
            border: 1px solid #d4d4d9;
        }
        .controls {
            position:fixed;
            top:20px;
            right:20px;
            z-index:1000;
            display: flex;
            align-items: center;
            gap:20px;
        }
        .btn {
            padding:10px 18px;
            background:#0d6efd;
            color:white;
            border:none;
            border-radius:4px;
            cursor:pointer;
            text-decoration:none;
        }
        .btn-success { background:#198754; }
        @media print {
            body, .page, [class*="card-container"] {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .controls {
                display: none !important;
            }


        }
        @media(max-width:639px){
            .controls{ display: none;}
        }
    </style>
</head>
<body>

<div class="controls">
    <button onclick="window.print()" class="btn">Print</button>
{{--    <a href="{{ route('employee.card.download', $employee->employee_code) }}" class="btn btn-success">--}}
{{--        Download PDF--}}
{{--    </a>--}}
</div>

<div class="page">
    @if($choices->orientation === 'landscape')
        @include('admin.employeeCard.preview.landscape.front',compact('employee' ,'choices','extra_fields',
            'assets',
            'qrCode',
            'barcode'
        ))
    @else
        @include('admin.employeeCard.preview.portrait.front',compact('employee' ,'choices','extra_fields',
            'assets',
            'qrCode',
            'barcode'
        ))
    @endif
</div>

<div class="page">
    @if($choices->orientation === 'landscape')
        @include('admin.employeeCard.preview.landscape.back',compact('employee' ,'choices','extra_fields',
            'assets',
            'qrCode',
            'barcode'
        ))
    @else
        @include('admin.employeeCard.preview.portrait.back',compact('employee' ,'choices','extra_fields',
            'assets',
            'qrCode',
            'barcode'
        ))
    @endif
</div>

</body>
</html>
