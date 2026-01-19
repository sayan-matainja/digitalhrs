<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .highlight {
            background-color: #f2f2f2;
        }
        .section-header {
            font-weight: bold;
            background-color: #e6e6e6;
        }
    </style>
</head>
<body>
<h1>Salary Sheet</h1>
<table>
    <thead>
    <tr>
        <th colspan="2">Particular</th>
        <th>Total</th>
        <th>Shrawan</th>
        <th>Bhadra</th>
        <th>Ashoj</th>
        <th>Kartik</th>
        <th>Mangsir</th>
        <th>Poush</th>
        <th>Magh</th>
        <th>Falgun</th>
        <th>Chaitra</th>
        <th>Baisakh</th>
        <th>Jestha</th>
        <th>Asadh</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th colspan="15">Income</th>
    </tr>
    @php
        $incomeComponents = [
            'Basic Salary' => $payrollData['payslipData']->monthly_basic_salary,
            'Allowance' => $payrollData['payslipData']->monthly_fixed_allowance,
            'SSF Contribution' => $payrollData['payslipData']->ssf_contribution
        ];
        $totalAnnualIncome = 0;
        $monthlyTotal = 0;
    @endphp

    @foreach($incomeComponents as $name => $amount)
        <tr>
            <td></td>
            <td>{{ $name }}</td>
            @php
                $annualAmount = $amount * 12;
                $totalAnnualIncome += $annualAmount;
                $monthlyTotal += $amount;
            @endphp
            <td>{{ $currency.' '.$annualAmount }}</td>
            @for ($i = 1; $i <= 12; $i++)
                <td>{{ $currency.' '.$amount }}</td>
            @endfor
        </tr>
    @endforeach

    @if($payrollData['payslipData']->bonus > 0)
        <tr>
            <td></td>
            <td>Bonus</td>
            <td>{{ $currency.' '.$payrollData['payslipData']->bonus }}</td>
            @for($month = 4; $month <= 12; $month++)
                <td>{{ isset($bonus->applicable_month) && $bonus->applicable_month == $month ? $currency.' '.$payrollData['payslipData']->bonus : '' }}</td>
            @endfor
            @for($month = 1; $month <= 3; $month++)
                <td>{{ isset($bonus->applicable_month) && $bonus->applicable_month == $month ? $currency.' '.$payrollData['payslipData']->bonus : '' }}</td>
            @endfor
        </tr>
        @php $totalAnnualIncome += $payrollData['payslipData']->bonus; @endphp
    @endif

    @foreach($payrollData['earnings'] as $earning)
        <tr>
            <td></td>
            <td>{{ $earning['name'] }}</td>
            @php
                $annualEarning = $earning['amount'] * 12;
                $totalAnnualIncome += $annualEarning;
                $monthlyTotal += $earning['amount'];
            @endphp
            <td>{{ $currency.' '.$annualEarning }}</td>
            @for ($i = 1; $i <= 12; $i++)
                <td>{{ $currency.' '.$earning['amount'] }}</td>
            @endfor
        </tr>
    @endforeach

    <tr class="highlight">
        <td colspan="2">Total Income</td>
        <td>{{ $currency . ' ' . number_format($totalAnnualIncome, 2) }}</td>
        @for ($i = 4; $i <= 12; $i++)
            @php
                $currentMonthTotal = $monthlyTotal + (isset($bonus->applicable_month) && $bonus->applicable_month == $i ? $payrollData['payslipData']->bonus : 0);
            @endphp
            <td>{{ $currency . ' ' . number_format($currentMonthTotal, 2) }}</td>
        @endfor
        @for ($i = 1; $i <= 3; $i++)
            @php
                $currentMonthTotal = $monthlyTotal + (isset($bonus->applicable_month) && $bonus->applicable_month == $i ? $payrollData['payslipData']->bonus : 0);
            @endphp
            <td>{{ $currency . ' ' . number_format($currentMonthTotal, 2) }}</td>
        @endfor
    </tr>

    <tr>
        <th colspan="15">Deductions</th>
    </tr>
    <tr>
        <td></td>
        <td>SSF Contribution</td>
        <td>{{ $currency.' '.($payrollData['payslipData']->ssf_deduction*12) }}</td>
        @for ($i = 1; $i <= 12; $i++)
            <td>{{ $currency.' '.$payrollData['payslipData']->ssf_deduction }}</td>
        @endfor
    </tr>
    @php
        $totalAnnualDeduction = $payrollData['payslipData']->ssf_deduction * 12;
        $totalMonthlyDeduction = $payrollData['payslipData']->ssf_deduction;
    @endphp
    @foreach($payrollData['deductions'] as $deduction)
        <tr>
            <td></td>
            <td>{{ $deduction['name'] }}</td>
            <td>{{ $currency.' '.($deduction['amount']*12) }}</td>
            @for ($i = 1; $i <= 12; $i++)
                <td>{{ $currency.' '.$deduction['amount'] }}</td>
            @endfor
        </tr>
        @php
            $totalAnnualDeduction += $deduction['amount'] * 12;
            $totalMonthlyDeduction += $deduction['amount'];
        @endphp
    @endforeach

    <tr class="highlight">
        <td colspan="2">Total Deduction</td>
        <td>{{ $currency.' '.number_format($totalAnnualDeduction, 2) }}</td>
        @for ($i = 1; $i <= 12; $i++)
            <td>{{ $currency.' '.number_format($totalMonthlyDeduction, 2) }}</td>
        @endfor
    </tr>
    <tr>
        <td colspan="2">TDS Paid</td>
        <td>{{ $payrollData['payslipData']->tds }}</td>
        @for ($i = 4; $i <= 12; $i++)
            @if($i == $month)
                <td>{{ $currency . ' ' . $payrollData['payslipData']->tds }}</td>
            @endif

        @endfor
        @for ($i = 1; $i <= 3; $i++)
            @if($i == $month)
                <td>{{ $currency . ' ' . $payrollData['payslipData']->tds }}</td>
            @endif
        @endfor
    </tr>
    <tr class="highlight">
        <td colspan="2">Total Payable</td>
        @php
            $annualTotalPayable = $totalAnnualIncome - $totalAnnualDeduction - ($payrollData['payslipData']->tds * 12);
        @endphp
        <td>{{ $currency . ' ' . number_format($annualTotalPayable, 2) }}</td>
        @for ($i = 4; $i <= 12; $i++)
            @php
                $monthlyIncome = $monthlyTotal + (isset($bonus->applicable_month) && $bonus->applicable_month == $i ? $payrollData['payslipData']->bonus : 0);
                $monthlyTDS = ($i == $month) ? $payrollData['payslipData']->tds : 0;
                $monthlyTotalPayable = $monthlyIncome - $totalMonthlyDeduction - $monthlyTDS;
            @endphp
            <td>{{ $currency . ' ' . number_format($monthlyTotalPayable, 2) }}</td>
        @endfor
        @for ($i = 1; $i <= 3; $i++)
            @php
                $monthlyIncome = $monthlyTotal + (isset($bonus->applicable_month) && $bonus->applicable_month == $i ? $payrollData['payslipData']->bonus : 0);
                $monthlyTDS = ($i == $month) ? $payrollData['payslipData']->tds : 0;
                $monthlyTotalPayable = $monthlyIncome - $totalMonthlyDeduction - $monthlyTDS;
            @endphp
            <td>{{ $currency . ' ' . number_format($monthlyTotalPayable, 2) }}</td>
        @endfor
    </tr>
    </tbody>
</table>

<h1>Additional Information</h1>
{{--<table>--}}
{{--    <thead>--}}
{{--    <tr>--}}
{{--        <th colspan="3">Particular</th>--}}
{{--        <th>Amount</th>--}}
{{--        <th>Amount</th>--}}
{{--    </tr>--}}
{{--    </thead>--}}
{{--    <tbody>--}}
{{--    <tr>--}}
{{--        <td colspan="3">Total Income</td>--}}
{{--        <td></td>--}}
{{--        <td>{{ $currency . ' ' . number_format($totalAnnualIncome, 2) }}</td>--}}
{{--    </tr>--}}
{{--    @php--}}
{{--        $totalRetirementContribution = $payrollData['payslipData']->ssf_contribution * 12;--}}
{{--        foreach($payrollData['earnings'] as $earning) {--}}
{{--            $totalRetirementContribution += $earning['amount'] * 12;--}}
{{--        }--}}
{{--    @endphp--}}

{{--    <tr>--}}
{{--        <td>less</td>--}}
{{--        <td colspan="2">Total Retirement Contribution Allowed</td>--}}
{{--        <td></td>--}}
{{--        <td>{{ $currency . ' ' . number_format($totalRetirementContribution, 2) }}</td>--}}
{{--    </tr>--}}
{{--    <tr>--}}
{{--        <td></td>--}}
{{--        <td></td>--}}
{{--        <td>SSF Contribution Salary</td>--}}
{{--        <td>{{ $currency . ' ' . number_format($payrollData['payslipData']->ssf_contribution * 12, 2) }}</td>--}}
{{--        <td></td>--}}
{{--    </tr>--}}
{{--    @foreach($payrollData['earnings'] as $earning)--}}
{{--        <tr>--}}
{{--            <td></td>--}}
{{--            <td></td>--}}
{{--            <td>{{ $earning['name'] }}</td>--}}
{{--            @php--}}
{{--                $annualEarning = $earning['amount'] * 12;--}}
{{--            @endphp--}}
{{--            <td>{{ $currency . ' ' . number_format($annualEarning, 2) }}</td>--}}
{{--            <td></td>--}}
{{--        </tr>--}}
{{--    @endforeach--}}

{{--    @foreach ($otherComponents as $component)--}}
{{--        <tr>--}}
{{--            <td></td>--}}
{{--            <td>{{ $component->name }}</td>--}}
{{--            <td></td>--}}
{{--            <td></td>--}}
{{--            <td>--}}
{{--                <input type="number"--}}
{{--                       name="other_component[{{ $component->id }}]"--}}
{{--                       value="{{ $component->annual_component_value }}"--}}
{{--                       step="0.01"--}}
{{--                       class="form-control other-component-input"--}}
{{--                       data-id="{{ $component->id }}"--}}
{{--                >--}}
{{--            </td>--}}
{{--        </tr>--}}
{{--    @endforeach--}}
{{--    @php--}}
{{--        $totalDeductions = $totalRetirementContribution;--}}
{{--        foreach ($otherComponents as $component) {--}}
{{--            $totalDeductions += $component->annual_component_value;--}}
{{--        }--}}
{{--        $taxableIncome = $totalAnnualIncome - $totalDeductions;--}}
{{--    @endphp--}}

{{--    <tr class="highlight">--}}
{{--        <td colspan="3">Taxable Income</td>--}}
{{--        <td></td>--}}
{{--        <td id="taxable-income">{{ $currency . ' ' . number_format($taxableIncome, 2) }}</td>--}}
{{--    </tr>--}}
{{--    </tbody>--}}
{{--</table>--}}

{{--@php--}}
{{--    $maritalStatus = $payrollData['payslipData']->marital_status;--}}
{{--    $remainingIncome = $taxableIncome;--}}
{{--    $totalTax = 0;--}}
{{--    $isFirstBracket = true;--}}
{{--@endphp--}}

{{--@if (isset($taxData[$maritalStatus]))--}}
{{--    <h1>Tax Calculation on Taxable Income ({{ ucfirst($maritalStatus) }})</h1>--}}
{{--    <table>--}}
{{--        <thead>--}}
{{--        <tr>--}}
{{--            <th>From</th>--}}
{{--            <th>To</th>--}}
{{--            <th>Income</th>--}}
{{--            <th>Percent</th>--}}
{{--            <th>Tax Amount</th>--}}
{{--        </tr>--}}
{{--        </thead>--}}
{{--        <tbody>--}}
{{--        @foreach($taxData[$maritalStatus] as $bracket)--}}
{{--            @php--}}
{{--                $from = $bracket->annual_salary_from;--}}
{{--                $to = $bracket->annual_salary_to >= 1.0E+20 ? null : $bracket->annual_salary_to;--}}
{{--                $percent = $bracket->tds_in_percent;--}}
{{--                $bracketIncome = min(max($remainingIncome, 0), $to ? $to - $from : $remainingIncome);--}}
{{--                $taxAmount = $bracketIncome * ($percent / 100);--}}

{{--                if ($isFirstBracket && $payrollData['payslipData']->ssf_contribution > 0) {--}}
{{--                    $taxAmount = 0;--}}
{{--                }--}}

{{--                $totalTax += $taxAmount;--}}
{{--                $remainingIncome -= $bracketIncome;--}}
{{--                $isFirstBracket = false;--}}
{{--            @endphp--}}
{{--            <tr>--}}
{{--                <td>{{ $currency . ' ' . number_format($from, 2) }}</td>--}}
{{--                <td>{{ $to ? $currency . ' ' . number_format($to, 2) : '' }}</td>--}}
{{--                <td>{{ $currency . ' ' . number_format($bracketIncome, 2) }}</td>--}}
{{--                <td>{{ number_format($percent, 2) }}%</td>--}}
{{--                <td>{{ $currency . ' ' . number_format($taxAmount, 2) }}</td>--}}
{{--            </tr>--}}
{{--        @endforeach--}}
{{--        <tr class="highlight">--}}
{{--            <td>Total</td>--}}
{{--            <td></td>--}}
{{--            <td>{{ $currency . ' ' . number_format($taxableIncome, 2) }}</td>--}}
{{--            <td></td>--}}
{{--            <td>{{ $currency . ' ' . number_format($totalTax, 2) }}</td>--}}
{{--        </tr>--}}
{{--        </tbody>--}}
{{--    </table>--}}
{{--@else--}}
{{--    <p>No tax data available for the current marital status ({{ $maritalStatus }}).</p>--}}
{{--@endif--}}

<table>
    <thead>
    <tr>
        <th colspan="3">Particular</th>
        <th>Amount</th>
        <th>Amount</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td colspan="3">Total Income</td>
        <td></td>
        <td>{{ $currency . ' ' . number_format($totalAnnualIncome, 2) }}</td>
    </tr>
    @php
        $totalRetirementContribution = $payrollData['payslipData']->ssf_contribution * 12;
        foreach($payrollData['earnings'] as $earning) {
            $totalRetirementContribution += $earning['amount'] * 12;
        }
    @endphp

    <tr>
        <td>less</td>
        <td colspan="2">Total Retirement Contribution Allowed</td>
        <td></td>
        <td>{{ $currency . ' ' . number_format($totalRetirementContribution, 2) }}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td>SSF Contribution Salary</td>
        <td>{{ $currency . ' ' . number_format($payrollData['payslipData']->ssf_contribution * 12, 2) }}</td>
        <td></td>
    </tr>
    @foreach($payrollData['earnings'] as $earning)
        @php $annualEarning = $earning['amount'] * 12; @endphp
        <tr>
            <td></td>
            <td></td>
            <td>{{ $earning['name'] }}</td>
            <td>{{ $currency . ' ' . number_format($annualEarning, 2) }}</td>
            <td></td>
        </tr>
    @endforeach

    @foreach ($otherComponents as $component)
        <tr>
            <td></td>
            <td>{{ $component->name }}</td>
            <td></td>
            <td></td>
            <td>
                <input type="number"
                       name="other_component[{{ $component->id }}]"
                       value="{{ $component->annual_component_value }}"
                       step="0.01"
                       class="form-control other-component-input"
                       data-id="{{ $component->id }}">
            </td>
        </tr>
    @endforeach
    @php
        $totalDeductions = $totalRetirementContribution;
        foreach ($otherComponents as $component) {
            $totalDeductions += $component->annual_component_value;
        }
        $taxableIncome = $totalAnnualIncome - $totalDeductions;
    @endphp

    <tr class="highlight">
        <td colspan="3">Taxable Income</td>
        <td></td>
        <td id="taxable-income">{{ $currency . ' ' . number_format($taxableIncome, 2) }}</td>
    </tr>
    </tbody>
</table>

@if (isset($taxData[$payrollData['payslipData']->marital_status]))
    <h1>Tax Calculation on Taxable Income ({{ ucfirst($payrollData['payslipData']->marital_status) }})</h1>
    <table>
        <thead>
        <tr>
            <th>From</th>
            <th>To</th>
            <th>Income</th>
            <th>Percent</th>
            <th>Tax Amount</th>
        </tr>
        </thead>
        <tbody>
        @php
            $remainingIncome = $taxableIncome;
            $totalTax = 0;
            $isFirstBracket = true;
        @endphp
        @foreach($taxData[$payrollData['payslipData']->marital_status] as $bracket)
            @php
                $from = $bracket->annual_salary_from;
                $to = $bracket->annual_salary_to >= 1.0E+20 ? null : $bracket->annual_salary_to;
                $percent = $bracket->tds_in_percent;
                $bracketIncome = min(max($remainingIncome, 0), $to ? $to - $from : $remainingIncome);
                $taxAmount = $bracketIncome * ($percent / 100);

                if ($isFirstBracket && $payrollData['payslipData']->ssf_contribution > 0) {
                    $taxAmount = 0;
                }

                $totalTax += $taxAmount;
                $remainingIncome -= $bracketIncome;
                $isFirstBracket = false;
            @endphp
            <tr>
                <td>{{ $currency . ' ' . number_format($from, 2) }}</td>
                <td>{{ $to ? $currency . ' ' . number_format($to, 2) : '' }}</td>
                <td>{{ $currency . ' ' . number_format($bracketIncome, 2) }}</td>
                <td>{{ number_format($percent, 2) }}%</td>
                <td>{{ $currency . ' ' . number_format($taxAmount, 2) }}</td>
            </tr>
        @endforeach
        <tr class="highlight">
            <td>Total</td>
            <td></td>
            <td>{{ $currency . ' ' . number_format($taxableIncome, 2) }}</td>
            <td></td>
            <td>{{ $currency . ' ' . number_format($totalTax, 2) }}</td>
        </tr>
        </tbody>
    </table>
@else
    <p>No tax data available for the current marital status ({{ $payrollData['payslipData']->marital_status }}).</p>
@endif

<table id="tax-deduction-table">
    <tbody>
    <tr class="section-header">
        <td>less: Tax Deduction</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>Medical Claim</td>
        <td></td>
        <td><input type="number" class="form-control editable-amount" id="medical-claim" value="750" step="0.01"></td>
    </tr>
    <tr>
        <td>Female Discount</td>
        <td></td>
        <td><input type="number" class="form-control editable-amount" id="female-discount" value="0" step="0.01"></td>
    </tr>
    <tr>
        <td>Other Discount</td>
        <td></td>
        <td><input type="number" class="form-control editable-amount" id="other-discount" value="0" step="0.01"></td>
    </tr>
    <tr>
        <td>Total Payable TDS</td>
        <td></td>
        <td><input type="number" class="form-control editable-amount" id="total-payable-tds" value="{{ $totalTax }}" step="0.01"></td>
    </tr>
    <tr>
        <td>Total Paid TDS</td>
        <td></td>
        <td><input type="number" class="form-control editable-amount" id="total-paid-tds" value="{{ $payrollData['payslipData']->tds }}" step="0.01"></td>
    </tr>
    <tr class="highlight">
        <td>Total Due TDS</td>
        <td></td>
        <td id="total-due-tds">{{ $totalTax - $payrollData['payslipData']->tds - 750 - 0 - 0 }}</td>
    </tr>
    </tbody>
</table>

<script>

    document.addEventListener('DOMContentLoaded', function() {
        {{--const otherComponentInputs = document.querySelectorAll('.other-component-input');--}}
        {{--const taxableIncomeCell = document.getElementById('taxable-income');--}}
        {{--const totalAnnualIncome = {{ $totalAnnualIncome }};--}}
        {{--const totalRetirementContribution = {{ $totalRetirementContribution }};--}}
        {{--const currency = '{{ $currency }}';--}}

        {{--function updateTaxableIncome() {--}}
        {{--    let totalDeductions = totalRetirementContribution;--}}
        {{--    otherComponentInputs.forEach(input => {--}}
        {{--        totalDeductions += parseFloat(input.value) || 0;--}}
        {{--    });--}}
        {{--    const taxableIncome = totalAnnualIncome - totalDeductions;--}}
        {{--    taxableIncomeCell.textContent = currency + ' ' + taxableIncome.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");--}}
        {{--}--}}

        {{--otherComponentInputs.forEach(input => {--}}
        {{--    input.addEventListener('input', updateTaxableIncome);--}}
        {{--});--}}
        const otherComponentInputs = document.querySelectorAll('.other-component-input');
        const taxableIncomeCell = document.getElementById('taxable-income');
        const totalAnnualIncome = {{ $totalAnnualIncome }};
        const totalRetirementContribution = {{ $totalRetirementContribution }};
        const currency = '{{ $currency }}';

        function updateTaxableIncome() {
            let totalDeductions = totalRetirementContribution;
            otherComponentInputs.forEach(input => {
                totalDeductions += parseFloat(input.value) || 0;
            });
            const taxableIncome = totalAnnualIncome - totalDeductions;
            taxableIncomeCell.textContent = currency + ' ' + taxableIncome.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        otherComponentInputs.forEach(input => {
            input.addEventListener('input', updateTaxableIncome);
        });


        // tds deduction
        const editableInputs = document.querySelectorAll('.editable-amount');
        const totalDueTdsCell = document.getElementById('total-due-tds');

        function updateTotalDueTds() {
            const medicalClaim = parseFloat(document.getElementById('medical-claim').value) || 0;
            const femaleDiscount = parseFloat(document.getElementById('female-discount').value) || 0;
            const otherDiscount = parseFloat(document.getElementById('other-discount').value) || 0;
            const totalPayableTds = parseFloat(document.getElementById('total-payable-tds').value) || 0;
            const totalPaidTds = parseFloat(document.getElementById('total-paid-tds').value) || 0;

            const totalDueTds = totalPayableTds - totalPaidTds - medicalClaim - femaleDiscount - otherDiscount;
            totalDueTdsCell.textContent = totalDueTds.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        editableInputs.forEach(input => {
            input.addEventListener('input', updateTotalDueTds);
        });

        // Initial calculation
        updateTotalDueTds();
    });
</script>
</body>
</html>
