    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Card - Front (Landscape)</title>
    <style>
        body { margin:0; padding:0; display:flex; justify-content:center; align-items:center; height:100vh; background:#f0f0f0; gap: 30px;}
        .id-card {
            width: 85.6mm; height: 54mm; background:#fff; border-radius: 10px; overflow:hidden;
             font-family: 'Arial', sans-serif; position:relative;
        }
        .card-head {
            height:12mm;
            position:relative;
            display: flex;
            align-items: center;
            justify-content: center;

        }
        .card-logo {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card-logo img { width: 45%; }

        .card-detail {padding: 10px; border-radius: 10px; }

        .card-item {
            display: flex;
            align-items: center;
            gap: 20px;
            justify-content: space-between;
        }
        .card-item-info h5, .card-item-info h6, .card-item-info p {
            margin: 0;
        }
        .card-item-info h5,.card-item-info h6{
            margin-bottom: 5px;
            text-transform:uppercase;
        }

        .card-item-info p {
            font-size: 11px;
        }

        .card-photo {
            width: 16mm; height: 16mm; border-radius:8px; overflow:hidden; border: 1px solid #ccc;
        }
        .card-photo img { width:100%; height:100%; object-fit:cover; }

        .info-details li {
            font-size: 11px;
            line-height: 1.5;
            display: flex;
            gap: 4px;
        }

        .info-details li span {
            font-weight: 700;
        }

        .qr svg{
            width:14mm !important;
            height:14mm!important;
        }

        .barcode svg {
            width: 32mm !important;
            height: 9mm !important;
            transform: rotate(90deg);
            position: absolute;
            bottom: 61px;
            right: -27px;
        }

        .barcode svg g#bars {
            fill: #333;
        }

        @media(max-width:639px){
            body{
                align-content: center;
                flex-wrap: wrap;
            }
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
    </style>
</head>
<body>
    <div class="id-card">
        <!-- Header + Logo -->
        <div class="card-head" style="background-color: {{ $choices->background_color }}">
            <div class="card-logo">
                <img src="{{ $assets->front_logo }}" alt="Company Logo">
            </div>
        </div>

        <div class="card-detail">
            <div class="card-item" style="justify-content: start; margin-bottom:5px;">
                <!-- Employee Photo -->
                <div class="card-photo">
                    @if($employee->photo && (filter_var($employee->photo, FILTER_VALIDATE_URL) || file_exists(public_path($employee->photo))))
                        <img src="{{ $employee->photo }}" alt="Employee Photo">
                    @else
                        <i class="fas fa-user-hard-hat"></i>
                    @endif
                </div>
                <div class="card-item-info">
                    <!-- Name & Designation -->
                    <h5>{{ strtoupper($employee->name) }}</h5>
                    <p>{{ $employee->designation }}</p>
                </div>
            </div>
            <div class="card-item">
                <!-- Dynamic & Ordered Extra Fields -->
                <div class="info-details">
                    <ul style="padding:0; margin:0;">
                    @foreach($extra_fields as $field)
                        @switch($field)
                            @case('employee_code')
                                {!! '<li>ID No <span class="label">' . $employee->employee_code .' </span></li>' !!}
                                @break

                            @case('department')
                                @if($employee->department)
                                    {!! '<li>Department <span class="label">' . $employee->department . '</span></li>' !!}
                                @endif
                                @break

                            @case('email')
                                @if($employee->email)
                                    {!! '<li>Email <span class="label">' . $employee->email . ' </span></li>' !!}
                                @endif
                                @break

                            @case('phone')
                                @if($employee->phone)
                                    {!! '<li>Phone <span class="label">' . $employee->phone . ' </span></li>' !!}
                                @endif
                                @break

                            @case('joining_date')
                                @if($employee->join_date)
                                    {!! '<li>Joined Date <span class="label">' . $employee->join_date . ' </span></li>' !!}
                                @endif
                                @break

                            @case('blood_group')
                                @if($employee->blood_group)
                                    {!! '<li>Blood Group <span class="label">' . $employee->blood_group . ' </span></li>' !!}
                                @endif
                                @break

                            @case('dob')
                                @if($employee->dob)
                                    {!! '<li>Date of Birth <span class="label">' .
                                        \Carbon\Carbon::parse($employee->dob)->format('d M Y') .
                                    ' </span></li>' !!}
                                @endif
                                @break
                        @endswitch
                    @endforeach
                </div>

                <div class="info-qr-bar">
                    <!-- QR Code or Barcode -->
                    @if($choices->graph_type === 'qr' && $qrCode)
                        <div class="qr">{!! $qrCode !!}</div>
                    @elseif($choices->graph_type === 'barcode' && $barcode)
                        <div class="barcode">{!! $barcode !!}</div>
                    @endif
                </div>
            </div>
        </div>



        <!-- Signature -->
        <!-- <div class="signature">
            <img src="{{ $assets->signature_image }}" style="height:20px; margin-top:4px;">
        </div> -->
    </div>
</body>
</html>
