<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__('index.employee_payslip')}}</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            box-sizing: border-box;
        }

        .wrapper{
            padding:20px;
            border: 1px solid #f1f1f1;
            margin: 20px 0;
        }

        header div {
            display: flex;
            justify-content: center;
        }

        header img {
            width: 150px;
        }
        header h2, header p {
            text-align: center;
        }

        header h2{margin-top:0;}

        table{width: 100%;}

        .separator {
            border-top: 1px solid #f1f1f1;
            margin: 20px 0;
        }

        .payslip-heading {
            margin: 15px 0;
            text-align:center;
        }

        .employee-info {
            margin-bottom: 20px;
            text-align: left;
        }

        .employee-info table{width: 100%;}

        .employee-info table th,  .employee-info table td {
            padding: 10px;
            border:1px solid #f1f1f1;
        }

        .attendance-info {
            margin-bottom: 20px;
            text-align: left;
        }
        .attendance-info table th, .attendance-info table td {
            padding: 10px;
            border: 1px solid #f1f1f1;
            background: #fbfbfb;
        }

        .tables-wrapper {
            display: flex;
            margin: 20px 0;
            gap:20px;
        }

        .table-container {
            flex: 1; /* Adjust as needed */
            width: 50%;
            margin-right: 2px; /* Add margin between tables if needed */
        }

        .table-container table {
            width: 100%;
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
            border-collapse: collapse;
        }


        .table-container th{
            background-color: #efefef;
            padding: 10px;
            text-align: left;
        }
        .table-container td {
            padding: 10px;
            text-align: left;
            border:1px solid #f1f1f1;
        }

        .other_info{
            display: flex;
            margin: 20px 0;
        }
        .other_info table {
            width: 100%;
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
            border-collapse: collapse;
        }

        .other_info th, .other_info td{
            padding: 10px;
            text-align: left;
            border:1px solid #f1f1f1;
        }
        .salary{
            background-color: #efefef;
        }

        .horizontal-line {
            border-top: 1px solid #f1f1f1;
            margin: 20px 0;
        }

        .net-salary {
            text-align: center;
            margin: 0;
            font-weight: bold;
        }

        .net-salary p{margin-bottom:0;}

    </style>
</head>
<body>

<div class="wrapper">
    <header>
        <!-- Company Logo, Name, Address, Email, and Phone -->
        @if(isset($payrollData->company_logo))
            <div>
                <img src="{{ asset($companyLogoPath.$payrollData->company_logo) }}" alt="Company Logo">
            </div>
        @endif

        <h2>{{ $payrollData->company_name ?? '' }}</h2>
        <p> {{ $payrollData->company_address ?? '' }} | {{ __('index.email') }}: {{ $payrollData->company_email ?? '' }} | {{ __('index.phone') }}: {{ $payrollData->company_phone ?? '' }}</p>
    </header>

    <!-- Horizontal Line -->
    <div class="separator"></div>

    <!-- Payslip Heading -->
    <div class="payslip-heading">
        <h3>
            @if($payrollData->salary_cycle == 'monthly')
                {{ __('index.payslip_for_the_month_of') }} {{  \App\Helpers\AppHelper::getMonthYear($payrollData->salary_from) }}
            @else
                {{ __('index.payslip') }}  {{ __('index.from') }} {{  \App\Helpers\AttendanceHelper::payslipDate($payrollData->salary_from) }}  {{ __('index.to') }} {{ \App\Helpers\AttendanceHelper::payslipDate($payrollData->salary_to)  }}
            @endif

        </h3>
    </div>

    <!-- Employee Information -->
    <div class="employee-info">
        <table>
            <tr>
                <th>{{ __('index.employee_id') }}:</th><td>{{ $payrollData->employee_code }}</td>
                <th>{{ __('index.name') }}:</th><td>{{ $payrollData->employee_name }}</td>
            </tr>
            <tr>
                <th>{{ __('index.salary_slip') }}:</th><td>{{  $payrollData->id ?? '' }}</td>
                <th>{{ __('index.department') }}:</th><td>{{ $payrollData->department }}</td>
            </tr>
            <tr>
                <th>{{ __('index.designation') }}:</th><td>{{ $payrollData->designation }}</td>
                <th>{{ __('index.joining_date') }}:</th><td>{{ $payrollData->joining_date }}</td>
            </tr>

        </table>

    </div>
    <div class="attendance-info">
        <table>
            @if($payrollData->salary_cycle == 'weekly')
                <tr>
                    <th>{{ __('index.total_working_hours') }}</th>
                    <td>{{  $payrollData->working_hours }}</td>
                    <th>{{ __('index.total_worked_hours') }}</th>
                    <td>{{ $payrollData->worked_hours }}</td>
                    <th>{{ __('index.total_deficit_hours') }}</th>
                    <td>{{ $payrollData->working_hours - $payrollData->worked_hours }}</td>
                    <th>{{ __('index.total_overtime_hours') }}</th>
                    <td>{{ $payrollData->overtime_hours }}</td>
                    <th>{{ __('index.total_undertime_hours') }}</th>
                    <td>{{ $payrollData->undertime_hours }}</td>

                </tr>

            @else
                <tr>
                    <th>{{ __('index.total_day') }}</th>
                    <td>{{  $payrollData->total_days }}</td>
                    <th>{{ __('index.present') }}</th>
                    <td>{{ $payrollData->present_days }}</td>
                    <th>{{ __('index.absent') }}</th>
                    <td>{{ $payrollData->absent_days }}</td>
                    <th>{{ __('index.leave') }}</th>
                    <td>{{ $payrollData->leave_days }}</td>
                    <th>{{ __('index.holidays') }}</th>
                    <td>{{ $payrollData->holidays }}</td>
                    <th>{{ __('index.weekend') }}</th>
                    <td>{{ $payrollData->weekends }}</td>
                </tr>
            @endif


        </table>

    </div>
    <!-- Tables for Earnings and Deductions -->
    <div class="tables-wrapper">
        <!-- Table for Earnings -->
        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>{{ __('index.earnings') }}</th>
                    <th>{{ __('index.amount') }}</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $totalEarning = 0;

                    if ($payrollData->salary_cycle == 'weekly'){
                        $totalEarning += ($payrollData->weekly_basic_salary + $payrollData->weekly_fixed_allowance);

                    }else{
                        $totalEarning += ( $payrollData->monthly_basic_salary + $payrollData->monthly_fixed_allowance);
                    }
                @endphp
                <tr>
                    <td>{{ __('index.basic_salary') }}</td>
                    <td> {{ ($payrollData->salary_cycle == 'weekly') ? $payrollData->weekly_basic_salary :$payrollData->monthly_basic_salary }}</td>
                </tr>

                @forelse($earnings as $earning)
                    <tr>
                        <td>{{ $earning['name'] }}</td>
                        <td>{{ $earning['amount'] }}</td>
                        @php $totalEarning+=$earning['amount']; @endphp
                    </tr>
                @empty
                @endforelse
                <tr>
                    <td>{{ __('index.fixed_allowance') }}</td>
                    <td> {{ ($payrollData->salary_cycle == 'weekly') ? $payrollData->weekly_fixed_allowance :  $payrollData->monthly_fixed_allowance }}</td>
                </tr>
                @forelse($additionalEarnings as $earning)
                    <tr class="salary">
                        <td>{{ $earning['name'] }}</td>
                        <td>{{ $earning['amount'] }}</td>
                    </tr>
                    @php $totalEarning += $earning['amount']; @endphp
                @empty
                @endforelse
                <tr class="totals">
                    <th>{{ __('index.gross_earnings') }}</th>
                    <th> {{ $totalEarning }}</th>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Table for Deductions -->
        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>{{ __('index.deductions') }}</th>
                    <th>{{ __('index.amount') }}</th>
                </tr>
                </thead>
                <tbody>
                @php $totalDeduction = $payrollData->ssf_deduction + $payrollData->pf_deduction; @endphp
                @forelse( $deductions as $deduction)
                    <tr>
                        <td>{{ $deduction['name'] }}</td>
                        <td>{{ $deduction['amount'] }}</td>
                        @php $totalDeduction+=$deduction['amount']; @endphp
                    </tr>
                @empty
                @endforelse
                @if($payrollData->ssf_deduction > 0)
                    <tr>
                        <td>{{ __('index.ssf_deduction') }}</td>
                        <td>{{ $payrollData->ssf_deduction }}</td>
                    </tr>
                @endif
                @if($payrollData->pf_deduction > 0)
                    <tr>
                        <td>{{ __('index.pf_deduction') }}</td>
                        <td>{{ $payrollData->pf_deduction }}</td>
                    </tr>
                @endif

                @forelse($additionalDeductions as $deduction)

                    <tr class="salary">
                        <td>{{ $deduction['name'] }} (less)</td>
                        <td>{{ $deduction['amount'] }}</td>
                    </tr>
                    @php $totalDeduction += $deduction['amount']; @endphp
                @empty
                @endforelse
                <tr class="totals">
                    <th>{{ __('index.total_deduction') }}</th>
                    <th> {{ $totalDeduction }}</th>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="other_info">
        <table>

            <tr class="salary">
                <th>{{ __('index.actual_salary') }} <span style="font-weight: normal">{{ __('index.actual_salary_formula') }}</span></th>
                <th>{{ $currency.' '. $totalEarning - $totalDeduction }}</th>
            </tr>
            @if($payrollData->bonus > 0)
                <tr class="salary">
                    <th>{{ __('index.bonus') }}</th>
                    <th>{{ $currency.' '. $payrollData->bonus }}</th>
                </tr>
                <tr class="salary">
                    <th>{{ __('index.taxable_salary') }} <span style="font-weight: normal">{{ __('index.taxable_salary_formula') }}</span></th>
                    <th>{{ $currency.' '. $totalEarning + $payrollData->bonus - $totalDeduction }}</th>
                </tr>
            @endif
            <tr class="salary">
                <th>{{ __('index.tax') }}</th>
                <th>{{ $currency.' '. $payrollData->tds }}</th>
            </tr>
            <tr class="salary">
                <th>{{ __('index.salary_after_tax') }}</th>
                <th>{{ $currency.' '. $totalEarning + $payrollData->bonus - $totalDeduction - $payrollData->tds }}</th>
            </tr>


            @if($payrollData->include_advance_salary ==1)
                <tr>
                    <th>{{ __('index.advance_salary') }}<span style="font-weight: normal">(-)</span></th>
                    <td> {{ $payrollData->advance_salary ?? 0 }}</td>
                </tr>
            @endif

            @if($payrollData->include_tada ==1)
                <tr>
                    <th>{{ __('index.tada') }} <span style="font-weight: normal">(+)</span></th>
                    <td> {{ $payrollData->tada ?? 0 }}</td>
                </tr>
            @endif
            @if($payrollData->loan_amount >0)
                <tr>
                    <th>{{ __('index.loan_amount') }} <span style="font-weight: normal">(-)</span></th>
                    <td> {{ $payrollData->loan_amount ?? 0 }}</td>
                </tr>
            @endif

            <tr>

            </tr>
            <tr>
                <th>{{ __('index.absent_deduction') }}<span style="font-weight: normal">
                            @if($payrollData->salary_cycle == 'monthly')
                            {{ __('index.absent_deduction_formula') }}
                        @else
                            {{ __('index.weekly_absent_deduction_formula') }}
                        @endif</span>
                </th>
                <th>

                    {{ $payrollData->absent_deduction ?? 0 }}
                </th>
            </tr>
            @if(isset($payrollData->ot_status) && $payrollData->ot_status  == 1)

                <tr>
                    <th>{{ __('index.overtime_income') }} </th>
                    <th>

                        {{ $payrollData->overtime }}
                    </th>
                </tr>
            @endif
            @if(isset($underTimeSetting) && $underTimeSetting->is_active  == 1)
                <tr>
                    <th>{{ __('index.undertime_deduction') }}</th>
                    <th>

                        {{ $payrollData->undertime }}
                    </th>
                </tr>
            @endif
        </table>
    </div>

    <!-- Net Salary -->
    <div class="net-salary">
        <p>{{ __('index.net_salary') }}: {{ $currency.' '. $payrollData->net_salary }}</p>
        <p>
            ({{ $numberToWords->get($payrollData->net_salary) }})</p>
        <p style="font-weight: normal">{{ __('index.net_salary_formula') }}</p>
    </div>
</div>
</body>
<script>
    window.print();
    window.onfocus = function () {
        window.close();
    }
</script>
</html>
