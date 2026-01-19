<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Card - Front</title>
    <style>
        body {
            margin:0; padding:0;
            background:#e5e7eb;
            display:flex; justify-content:center; align-items:center;
            min-height:100vh;
            font-family:Arial,sans-serif;
            gap: 30px;
        }

        .card {
            width: 54mm;
            height: 86mm;
            background: #fff;
            border-radius: 4mm;
            position: relative;
            overflow: hidden;
            color: #333;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .port-head {
            padding: 10px 10px 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:  {{ $choices->background_color }}!important;
        }

        .port-logo {
            width: 100%;
            text-align: center;
            display: inline-block;
        }

        .port-logo img {
            width: 75%;
            height: auto;
        }

        .port-main {
            width: 100%;
            text-align: center;
            height: 100%;
            padding: 10px;
        }

        .port-photo {
            width: 20mm;
            height: 20mm;
            border: 2px solid #f1f1f1;
            border-radius: 50%;
            overflow: hidden;
            margin: -30px auto 0;
            background: #f1f1f1;
        }

        .port-photo img { width:100%; height:100%; object-fit:cover; }

        .card-item-info h5, .card-item-info h6, .card-item-info p {
            margin: 0;
        }
        .card-item-info h5, .card-item-info h6 {
            margin-bottom: 5px;
            text-transform:uppercase;
        }

        .card-item-info p {
            font-size: 11px;
        }

        .card-item-info li {
            font-size: 11px;
            line-height: 1.5;
            display: flex;
            gap: 4px;
            justify-content: center;
        }

        .card-item-info li span {
            font-weight: 700;
        }

        .qr, .barcode {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
        }

        .qr svg{
            width:14mm !important;
            height:14mm!important;
        }

        .barcode svg {
            width: 100%;
            height: 8mm !important;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .port-head {
                -webkit-print-color-adjust: exact; /* For Chrome, Safari */
                print-color-adjust: exact;  
            }
            
        }

        @media(max-width:639px){
            body{
                align-content: center;
                flex-wrap: wrap;
            }

        }

    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="card">

    <!-- Logo -->
    <div class="port-head">
        <div class="port-logo">
            <img src="{{ $assets->front_logo }}" alt="DigitalHRS Logo">
        </div>
    </div>
    <div class="port-main">
        <!-- Photo -->
        <div class="port-photo">
            @if($employee->photo && (filter_var($employee->photo, FILTER_VALIDATE_URL) || file_exists(public_path($employee->photo))))
                <img src="{{ $employee->photo }}" alt="Employee Photo">
            @else
                <i class="fas fa-user-hard-hat"></i>
            @endif
        </div>

        <div class="card-item-info" style="text-align:center; margin-top:5px; width:100%;">
            <!-- Name -->
            <h5>{{ strtoupper($employee->name) }}</h5>

            <!-- Designation -->
            <p>{{ strtoupper($employee->designation) }}</p>

            <!-- Dynamic Extra Fields - Clean Label + Value Layout -->
            <ul style="padding:0; margin:8px 0; list-style-type: none;">
                @foreach($extra_fields as $field)
                    @switch($field)
                        @case('department')
                            @if($employee->department)
                                <li class="field">

                                    Department: <span class="value">{{ $employee->department }}</span>
                                </li>
                            @endif
                            @break

                        @case('email')
                            @if($employee->email)
                                <li class="field">
                                    Email:<span class="value">{{ $employee->email }}</span>
                                </li>
                            @endif
                            @break

                        @case('phone')
                            @if($employee->phone)
                                <li class="field">

                                    Phone:<span class="value">{{ $employee->phone }}</span>
                                </li>
                            @endif
                            @break

                        @case('employee_code')
                            @if($employee->employee_code)
                                <li class="field">

                                    ID No:<span class="value">{{ $employee->employee_code }}</span>
                                </li>
                            @endif
                            @break

                        @case('joining_date')
                            @if($employee->join_date)
                                <li class="field">

                                    Joined Date:<span class="value">{{ \Carbon\Carbon::parse($employee->join_date)->format('d M Y') }}</span>
                                </li>
                            @endif
                            @break

                        @case('blood_group')
                            @if($employee->blood_group)
                                <li class="field">

                                    Blood Group:<span class="value">{{ $employee->blood_group }}</span>
                                </li>
                            @endif
                            @break

                        @case('dob')
                            @if($employee->dob)
                                <li class="field">
                                    DOB: <span class="value">{{ \Carbon\Carbon::parse($employee->dob)->format('d M Y') }}</span>
                                </li>
                            @endif
                            @break
                    @endswitch
                @endforeach
            </ul>
            @if($choices->graph_type === 'qr' && $qrCode)
                <div class="qr">{!! $qrCode !!}</div>
            @elseif($choices->graph_type === 'barcode' && $barcode)
                <div class="barcode">{!! $barcode !!}</div>
            @endif
        </div>
    </div>

</div>

</body>
</html>
