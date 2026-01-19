@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.employee_tax_report'))

@section('action',__('index.tax_report_edit'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.payroll.taxReport.common.breadcrumb')
    </section>

    <div class="card">
        <div
            class="card-header d-md-flex justify-content-md-between align-items-center pb-2 justify-content-center text-md-start text-center">
            <h6 class="card-title mb-2">{{ __('index.tax_report_detail_of') }} {{ $reportData->employee->name }}
                ({{ $reportData->fiscalYear->year }})</h6>

        </div>
        <div class="card-body">
            <div class="salary-sheet">
                <form action="{{ route('admin.payroll.tax-report.update', $reportData->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="table-responsive mb-4">
                        <h5 class="text-lg-start text-center mb-3">{{ __('index.salary_sheet') }}</h5>

                        <table class="table table-bordered">
                            <thead class="thead-dark">
                            <tr>
                                <th colspan="2">{{ __('index.particular') }}</th>
                                <th>{{ __('index.total') }}</th>
                                @php
                                    $isBsEnabled = AppHelper::ifDateInBsEnabled();
                                    $enableTaxExemption = \App\Helpers\AppHelper::enableTaxExemption();

                                    $monthData = json_decode($reportData->months, true) ?? [];
                                    $totalMonth = count($monthData);
                                    $firstDay = $reportData->fiscalYear->start_date;
                                    $lastDay = $reportData->fiscalYear->end_date;
                                    $startMonth =  (int)AppHelper::getMonthValue($firstDay) ;
                                    $endMonth =  (int)AppHelper::getMonthValue($lastDay) ;
                                    $allMonths = [];
                                    $totalBonusTax = 0;
                                    $totalBonusAmount = 0;
                                    for ($i = 0; $i < 12; $i++) {
                                    $allMonths[] = (($startMonth + $i - 1) % 12) + 1;
                                    }
                                    $months = $isBsEnabled ? [
                                     1 => 'Baishakh', 2 => 'Jestha', 3 => 'Asar', 4 => 'Shrawan', 5 => 'Bhadra', 6 => 'Ashwin',
                                     7 => 'kartik', 8 => 'Mangsir', 9 => 'Poush', 10 => 'Magh', 11 => 'Falgun', 12 => 'Chaitra'
                                    ] :[
                                     1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
                                     7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                    ];
                                    $monthlyTotals = array_fill(1, 12, 0);
                                    $totalMonthlyDeduction = array_fill(1, 12, 0);
                                    $monthlyTDS = array_fill(1, 12, 0);
                                    $totalAnnualIncome = 0;
                                    $totalAnnualDeduction = 0;
                                    $totalTds = 0;
                                    // Initialize monthly values from reportDetail
                                    $monthlyBasicSalary = [];
                                    $monthlyFixedAllowance = [];
                                    $monthlySsfContribution = [];
                                    $monthlySsfDeduction = [];
                                    $monthlyPfContribution = [];
                                    $monthlyPfDeduction = [];

                                    foreach ($reportData->reportDetail as $detail) {
                                     $month = $detail->month;
                                     $monthlyBasicSalary[$month] = $detail->basic_salary ?? 0;
                                     $monthlyFixedAllowance[$month] = $detail->fixed_allowance ?? 0;
                                     $monthlySsfContribution[$month] = $detail->ssf_contribution ?? 0;
                                     $monthlySsfDeduction[$month] = $detail->ssf_deduction ?? 0;
                                     $monthlyPfContribution[$month] = $detail->pf_contribution ?? 0;
                                     $monthlyPfDeduction[$month] = $detail->pf_deduction ?? 0;

                                    }

                                    // Aggregate earning components by month and component name
                                     $earningComponentsByName = [];
                                     foreach ($reportData->componentDetail as $detail) {
                                         if ($detail->salaryComponent->component_type === 'earning') {
                                             $name = $detail->salaryComponent->name ?? 'Unknown Earning';
                                             $month = $detail->month;
                                             $earningComponentsByName[$name][$month] = $detail->amount ?? 0;
                                             if (in_array($month, $monthData)) {
                                                 $monthlyTotals[$month] += $detail->amount ?? 0;
                                                 $totalAnnualIncome += $detail->amount ?? 0;
                                             }
                                         }
                                     }

                                     // Aggregate deduction components by month and component name
                                     $deductionComponentsByName = [];
                                     foreach ($reportData->componentDetail as $detail) {
                                         if ($detail->salaryComponent->component_type === 'deductions') {
                                             $name = $detail->salaryComponent->name ?? 'Unknown Deduction';
                                             $month = $detail->month;
                                             $deductionComponentsByName[$name][$month] = $detail->amount ?? 0;
                                             if (in_array($month, $monthData)) {
                                                 $totalMonthlyDeduction[$month] += $detail->amount ?? 0;
                                                 $totalAnnualDeduction += $detail->amount ?? 0;
                                             }
                                         }
                                     }
                                    // Aggregate additional earning components by month and component name
                                    $additionalEarningComponentsByName = [];
                                    foreach ($reportData->additionalDetail as $detail) {
                                        if ($detail->salaryComponent->component_type === 'earning') {
                                            $name = $detail->salaryComponent->name ?? 'Unknown Earning';
                                            $month = $detail->month;
                                            $additionalEarningComponentsByName[$name][$month] = $detail->amount ?? 0;
                                            if (in_array($month, $monthData)) {
                                                $monthlyTotals[$month] += $detail->amount ?? 0;
                                                $totalAnnualIncome += $detail->amount ?? 0;
                                            }
                                        }
                                    }
                                    // additional components
                                    $additionalDeductionComponentsByName = [];
                                    foreach ($reportData->additionalDetail as $detail) {
                                        if ($detail->salaryComponent->component_type === 'deductions') {
                                            $name = $detail->salaryComponent->name ?? 'Unknown Deduction';
                                            $month = $detail->month;
                                            $additionalDeductionComponentsByName[$name][$month] = $detail->amount ?? 0;
                                            if (in_array($month, $monthData)) {
                                                $totalMonthlyDeduction[$month] += $detail->amount ?? 0;
                                                $totalAnnualDeduction += $detail->amount ?? 0;
                                            }
                                        }
                                    }

                                    // Aggregate bonus details
                                    $bonusComponentsByMonth = [];
                                    foreach ($reportData->bonusDetail as $bonus) {
                                        $month = $bonus->month;
                                        if (in_array($month, $monthData)) {
                                             $name = $bonus->bonus->title ?? 'Bonus';
                                             $bonusComponentsByMonth[$name][$month] = $bonus->amount ?? 0;
                                             $monthlyTotals[$month] += $bonus->amount ?? 0;
                                             $totalAnnualIncome += $bonus->amount ?? 0;
                                             $totalBonusTax += $bonus->tax ?? 0;
                                             $totalBonusAmount += $bonus->amount ?? 0;
                                        }
                                    }

                                    // Aggregate TDS details
                                    foreach ($reportData->tdsDetail as $tdsDetail) {
                                        $monthlyTDS[$tdsDetail->month] = $tdsDetail->amount;
                                    }
                                    $totalTds =  $reportData->total_payable_tds;
                                 // Add totals from reportDetail
                                 $totalAnnualIncome += $reportData->total_basic_salary + $reportData->total_allowance;
                                 $totalAnnualDeduction += ($reportData->total_ssf_deduction +$reportData->total_pf_deduction);
                                @endphp
                                @foreach($allMonths as $month)
                                    <th>{{ $months[$month] }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th colspan="{{ 3 + count($allMonths) }}" class="bg-light">{{ __('index.income') }}</th>
                            </tr>
                            <tr>
                                <td></td>
                                <td>{{ __('index.basic_salary') }}</td>
                                <td>{{ $currency }} {{ $reportData->total_basic_salary }}</td>
                                @foreach($allMonths as $month)
                                    @php $showData = in_array($month, $monthData); @endphp
                                    @if($showData)
                                        @php $monthlyTotals[$month] += $monthlyBasicSalary[$month] ?? 0; @endphp
                                        <td>{{ $currency }} {{ $monthlyBasicSalary[$month] ?? 0 }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr>
                                <td></td>
                                <td>{{ __('index.fixed_allowance') }}</td>
                                <td>{{ $currency }} {{ $reportData->total_allowance }}</td>
                                @foreach($allMonths as $month)
                                    @php $showData = in_array($month, $monthData); @endphp
                                    @if($showData)
                                        @php $monthlyTotals[$month] += $monthlyFixedAllowance[$month] ?? 0; @endphp
                                        <td>{{ $currency }} {{ $monthlyFixedAllowance[$month] ?? 0 }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                            </tr>
                            @foreach($earningComponentsByName as $name => $values)
                                <tr>
                                    <td></td>
                                    <td>{{ $name }}</td>
                                    @php
                                        $isFixed = false;
                                        $annualAmount = 0;
                                        foreach ($reportData->componentDetail as $detail) {
                                            if ($detail->salaryComponent->component_type === 'earning' && $detail->salaryComponent->name === $name) {
                                                $isFixed = $detail->salaryComponent->value_type === 'fixed';
                                                if ($isFixed) {
                                                    $annualAmount = $detail->salaryComponent->annual_component_value ?? 0;
                                                } else {
                                                    $annualAmount = array_sum(array_intersect_key($values, array_flip($monthData)));
                                                }
                                                break;
                                            }
                                        }
                                        if (!$isFixed) {
                                            $annualAmount = array_sum(array_intersect_key($values, array_flip($monthData)));
                                        }
                                    @endphp
                                    <td>{{ $currency }} {{ $annualAmount }}</td>
                                    @foreach($allMonths as $month)
                                        @php $showData = in_array($month, $monthData); @endphp
                                        @if($showData)
                                            <td>{{ $currency }} {{ $values[$month] ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                            @foreach($additionalEarningComponentsByName as $name => $values)
                                <tr>
                                    <td></td>
                                    <td>{{ $name }}</td>
                                    @php
                                        $isFixed = false;
                                        $annualAmount = 0;
                                        foreach ($reportData->additionalDetail as $detail) {
                                            if ($detail->salaryComponent->component_type === 'earning' && $detail->salaryComponent->name === $name) {
                                                $isFixed = $detail->salaryComponent->value_type === 'fixed';
                                                if ($isFixed) {
                                                    $annualAmount = $detail->salaryComponent->annual_component_value ?? 0;
                                                } else {
                                                    $annualAmount = array_sum(array_intersect_key($values, array_flip($monthData)));
                                                }
                                                break;
                                            }
                                        }
                                        if (!$isFixed) {
                                            $annualAmount = array_sum(array_intersect_key($values, array_flip($monthData)));
                                        }
                                    @endphp
                                    <td>{{ $currency }} {{ $annualAmount }}</td>
                                    @foreach($allMonths as $month)
                                        @php $showData = in_array($month, $monthData); @endphp
                                        @if($showData)
                                            <td>{{ $currency }} {{ $values[$month] ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                            @foreach($bonusComponentsByMonth as $name => $values)
                                <tr>
                                    <td></td>
                                    <td>{{ $name }}</td>
                                    @php
                                        $annualAmount = array_sum(array_intersect_key($values, array_flip($monthData)));
                                    @endphp
                                    <td>{{ $currency }} {{ $annualAmount }}</td>
                                    @foreach($allMonths as $month)
                                        @php $showData = in_array($month, $monthData); @endphp
                                        @if($showData)
                                            <td>{{ $currency }} {{ $values[$month] ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                            <tr class="highlight">
                                <th colspan="2">{{ __('index.total_earning') }}</th>
                                <th>{{ $currency }} {{ $totalAnnualIncome }}</th>
                                @foreach($allMonths as $month)
                                    @if(in_array($month, $monthData))
                                        <th>{{ $currency }} {{ $monthlyTotals[$month] }}</th>
                                    @else
                                        <th></th>
                                    @endif
                                @endforeach
                            </tr>
                            <tr>
                                <th colspan="{{ 3 + count($allMonths) }}"
                                    class="bg-light">{{ __('index.deductions') }}</th>
                            </tr>
                            @if($reportData->total_ssf_deduction >0)
                                <tr>
                                    <td></td>
                                    <td>{{ __('index.ssf_deduction') }}</td>
                                    <td>{{ $currency }} {{ $reportData->total_ssf_deduction }}</td>
                                    @foreach($allMonths as $month)
                                        @php $showData = in_array($month, $monthData); @endphp
                                        @if($showData)
                                            @php $totalMonthlyDeduction[$month] += $monthlySsfDeduction[$month] ?? 0; @endphp
                                            <td>{{ $currency }} {{ $monthlySsfDeduction[$month] ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endif
                            @if($reportData->total_pf_deduction >0)
                                <tr>
                                    <td></td>
                                    <td>{{ __('index.pf_deduction') }}</td>
                                    <td>{{ $currency }} {{ $reportData->total_pf_deduction }}</td>
                                    @foreach($allMonths as $month)
                                        @php $showData = in_array($month, $monthData); @endphp
                                        @if($showData)
                                            @php $totalMonthlyDeduction[$month] += $monthlyPfDeduction[$month] ?? 0; @endphp
                                            <td>{{ $currency }} {{ $monthlyPfDeduction[$month] ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endif
                            @foreach($deductionComponentsByName as $name => $values)
                                <tr>
                                    <td></td>
                                    <td>{{ $name }}</td>
                                    @php
                                        $isFixed = false;
                                        $annualAmount = 0;
                                        foreach ($reportData->componentDetail as $detail) {
                                            if ($detail->salaryComponent->component_type === 'deductions' && $detail->salaryComponent->name === $name) {
                                                $isFixed = $detail->salaryComponent->value_type === 'fixed';
                                                if ($isFixed) {
                                                    $annualAmount = $detail->salaryComponent->annual_component_value ?? 0;
                                                } else {
                                                    $annualAmount = array_sum(array_intersect_key($values, array_flip($monthData)));
                                                }
                                                break;
                                            }
                                        }
                                        if (!$isFixed) {
                                            $annualAmount = array_sum(array_intersect_key($values, array_flip($monthData)));
                                        }
                                    @endphp
                                    <td>{{ $currency }} {{ $annualAmount }}</td>
                                    @foreach($allMonths as $month)
                                        @php $showData = in_array($month, $monthData); @endphp
                                        @if($showData)
                                            <td>{{ $currency }} {{ $values[$month] ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                            @foreach($additionalDeductionComponentsByName as $name => $values)
                                <tr>
                                    <td></td>
                                    <td>{{ $name }}</td>
                                    @php
                                        $isFixed = false;
                                        $annualAmount = 0;
                                        foreach ($reportData->additionalDetail as $detail) {
                                            if ($detail->salaryComponent->component_type === 'deductions' && $detail->salaryComponent->name === $name) {
                                                $isFixed = $detail->salaryComponent->value_type === 'fixed';
                                                if ($isFixed) {
                                                    $annualAmount = $detail->salaryComponent->annual_component_value ?? 0;
                                                } else {
                                                    $annualAmount = array_sum(array_intersect_key($values, array_flip($monthData)));
                                                }
                                                break;
                                            }
                                        }
                                        if (!$isFixed) {
                                            $annualAmount = array_sum(array_intersect_key($values, array_flip($monthData)));
                                        }
                                    @endphp
                                    <td>{{ $currency }} {{ $annualAmount }}</td>
                                    @foreach($allMonths as $month)
                                        @php $showData = in_array($month, $monthData); @endphp
                                        @if($showData)
                                            <td>{{ $currency }} {{ $values[$month] ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                            <tr class="highlight">
                                <th colspan="2">{{ __('index.total_deduction') }}</th>
                                <th>{{ $currency }} {{ $totalAnnualDeduction }}</th>
                                @foreach($allMonths as $month)
                                    @if(in_array($month, $monthData))
                                        <th>{{ $currency }} {{ $totalMonthlyDeduction[$month] }}</th>
                                    @else
                                        <th></th>
                                    @endif
                                @endforeach
                            </tr>
                            <tr class="highlight">
                                <th colspan="2">{{ __('index.actual_salary') }}</th>
                                <th>{{ $currency }} {{ $totalAnnualIncome - $totalBonusAmount - $totalAnnualDeduction }}</th>
                                @foreach($allMonths as $month)
                                    @if(in_array($month, $monthData))
                                        <th>{{ $currency }} {{ $monthlyTotals[$month] - $totalMonthlyDeduction[$month]  }}</th>
                                    @else
                                        <th></th>
                                    @endif
                                @endforeach
                            </tr>
                            <tr>
                                <td colspan="2">Tds</td>
                                <td>{{ $currency }} {{ $totalTds }}</td>
                                @foreach($allMonths as $month)
                                    @if(in_array($month, $monthData))
                                        <td>{{ $monthlyTDS[$month] ? $currency . ' ' . $monthlyTDS[$month] : '' }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr class="highlight">
                                <td colspan="2">{{ __('index.total_payable') }}</td>
                                @php $annualTotalPayable = $totalAnnualIncome - $totalAnnualDeduction - $totalTds; @endphp
                                <td>{{ $currency }} {{ $annualTotalPayable }}</td>
                                @foreach($allMonths as $month)
                                    @if(in_array($month, $monthData))
                                        @php $monthlyTotalPayable = $monthlyTotals[$month] - $totalMonthlyDeduction[$month] - ($monthlyTDS[$month] ?? 0); @endphp
                                        <td>{{ $currency }} {{ $monthlyTotalPayable }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                            </tr>


                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <h5 class="text-lg-start text-center mb-4">{{ __('index.additional_information') }} <span class="text-warning text-sm-start">(This is for office use only. it doesn't fall under taxable income. )</span></h5>

                        <table class="table table-bordered mb-4">
                            <thead class="thead-dark">
                            <tr>
                                <th colspan="2">{{ __('index.particular') }}</th>
                                <th>{{ __('index.total') }}</th>
                                @foreach($allMonths as $month)
                                    <th>{{ $months[$month] }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @if($reportData->total_ssf_contribution >0)
                                <tr>
                                    <td></td>
                                    <td>{{ __('index.ssf_contribution') }} ({{ __('index.office') }})</td>
                                    <td>{{ $currency }} {{ $reportData->total_ssf_contribution }}</td>
                                    @foreach($allMonths as $month)
                                        @php $showData = in_array($month, $monthData); @endphp
                                        @if($showData)
                                            @php $monthlyTotals[$month] += $monthlySsfContribution[$month] ?? 0; @endphp
                                            <td>{{ $currency }} {{ $monthlySsfContribution[$month] ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endif
                            @if($reportData->total_pf_contribution >0)
                                <tr>
                                    <td></td>
                                    <td>{{ __('index.pf_contribution') }} ({{ __('index.office') }})</td>
                                    <td>{{ $currency }} {{ $reportData->total_pf_contribution }}</td>
                                    @foreach($allMonths as $month)
                                        @php $showData = in_array($month, $monthData); @endphp
                                        @if($showData)
                                            @php $monthlyTotals[$month] += $monthlyPfContribution[$month] ?? 0; @endphp
                                            <td>{{ $currency }} {{ $monthlyPfContribution[$month] ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endif
                            </tbody>
                        </table>
                        @php $taxableIncome = $totalAnnualIncome - $totalAnnualDeduction - $totalBonusAmount; @endphp


                    @if (isset($taxData[$reportData->employee->marital_status]))
                            <h5 class="text-lg-start text-center mb-3">{{ __('index.tax_calculation_on_taxable_income') }}
                                ({{ ucfirst($reportData->employee->marital_status) }})</h5>
                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                <tr>
                                    <th>{{ __('index.from') }}</th>
                                    <th>{{ __('index.to') }}</th>
                                    <th>{{ __('index.income') }}  </th>
                                    <th>{{ __('index.percent') }}</th>
                                    <th>{{ __('index.tax_amount') }}</th>
                                </tr>
                                </thead>
                                <tbody id="tax-calculation-body">
                                @php
                                    $remainingIncome = $taxableIncome;
                                    $totalTax = 0;
                                    $isFirstBracket = true;
                                @endphp
                                @foreach($taxData[$reportData->employee->marital_status] as $bracket)
                                    @php
                                        $from = $bracket->annual_salary_from;
                                        $to = $bracket->annual_salary_to >= 1.0E+20 ? null : $bracket->annual_salary_to;
                                        $percent = $bracket->tds_in_percent;
                                        $bracketIncome = min(max($remainingIncome, 0), $to ? $to - $from : $remainingIncome);
                                        $taxAmount = $bracketIncome * ($percent / 100);
                                        if (($enableTaxExemption == 1) && $isFirstBracket && $reportData->total_ssf_deduction > 0) {
                                            $taxAmount = 0;
                                        }
                                        $totalTax += $taxAmount;
                                        $remainingIncome -= $bracketIncome;
                                        $isFirstBracket = false;
                                    @endphp
                                    <tr>
                                        <td>{{ $currency }} {{ $from }}</td>
                                        <td>{{ $to ? $currency . ' ' . $to : '' }}</td>
                                        <td>{{ $currency }} {{ $bracketIncome }} </td>
                                        <td>{{ $percent }}%</td>
                                        <td>{{ $currency }} {{ $taxAmount }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr class="highlight">
                                    <td>{{ __('index.total') }}</td>
                                    <td></td>
                                    <td id="taxableIncome"> {{ $taxableIncome }}</td>
                                    <td></td>
                                    <td id="total-tax">{{ $totalTax }}</td>
                                </tr>
                                @php
                                    $totalTax += $totalBonusTax;
                                    $taxDeduction = ($reportData->medical_claim ?? 0) + ($reportData->female_discount ?? 0) + ($reportData->other_discount ?? 0);
                                @endphp
                                <tr>
                                    <td>{{ __('index.less_tax_deduction') }}</td>
                                    <td>{{ __('index.medical_claim') }}</td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="number" class="form-control editable-amount" name="medical_claim"
                                               id="medical_claim" value="{{ $reportData->medical_claim }}" step="0.01"
                                               oninput="updateTotal()"></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>{{ __('index.female_discount') }}</td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="number" class="form-control editable-amount" name="female_discount"
                                               id="female_discount" value="{{ $reportData->female_discount }}"
                                               step="0.01" oninput="updateTotal()"></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>{{ __('index.other_discount') }}</td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="number" class="form-control editable-amount" name="other_discount"
                                               id="other_discount" value="{{ $reportData->other_discount }}" step="0.01"
                                               oninput="updateTotal()"></td>
                                </tr>
                                <tr>
                                    <td>{{ __('index.total_payable_tds') }} (* with bonus tax)</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td id="totalPayableTax">{{  ($totalTax - ($reportData->medical_claim+$reportData->female_discount+$reportData->other_discount)) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('index.total_paid_tds') }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <input type="number" class="form-control editable-amount" name="total_paid_tds"
                                               id="total-paid-tds" value="{{ $reportData->total_paid_tds }}"
                                               step="0.01">
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ __('index.total_due_tds') }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td id="dueTds">{{ $totalTax - ($reportData->medical_claim+$reportData->female_discount+$reportData->other_discount) - $reportData->total_paid_tds }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('index.tds_calculation_msg') }}  </th>
                                    <th></th>
                                    <th>{{ __('index.remaining_month') }} </th>
                                    <th><input type="number" class="form-control editable-amount" name="total_month"
                                               id="totalMonth" value="{{ $totalMonth }}" step="0.01"></th>
                                    <th><span
                                            id="remainTdsByMonth">{{ $reportData->total_due_tds/$totalMonth }}</span> {{ __('index.remain_tds_formula') }}
                                    </th>
                                </tr>
                                </tfoot>
                            </table>
                        @else
                            <p class="text-center">{{ __('index.tax_data_not_available') }}
                                ({{ $reportData->employee->name }}).</p>
                        @endif

                    </div>
                    <div class="row justify-content-center mt-4">
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">{{ __('index.update') }}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const otherComponentInputs = document.querySelectorAll('.other-component-input');
            const taxableIncomeElement = document.getElementById('taxableIncome');
            const totalTaxElement = document.getElementById('total-tax');
            const totalPayableTaxElement = document.getElementById('totalPayableTax');
            const totalPaidTdsInput = document.getElementById('total-paid-tds');
            const totalMonthInput = document.getElementById('totalMonth');
            const totalDueTdsElement = document.getElementById('dueTds');
            const remainTdsByMonthElement = document.getElementById('remainTdsByMonth');
            const taxCalculationBody = document.getElementById('tax-calculation-body');
            let totalAnnualIncome = {{ $totalAnnualIncome }};
            let totalAnnualDeduction = {{ $totalAnnualDeduction }};
            let totalBonusAmount = {{ $totalBonusAmount }};
            let totalBonusTax = {{ $totalBonusTax }};
            let taxBrackets = @json($taxData[$reportData?->employee?->marital_status] ?? []);
            let enableTaxExemption = {{ $enableTaxExemption }};
            let totalMonths = {{ $totalMonth }};
            let ssfDeduction = {{ $reportData['total_ssf_deduction'] }};
            let pfDeduction = {{ $reportData['total_pf_deduction'] }};


            function recalculateTaxableIncome() {

                const taxableIncome = totalAnnualIncome  - totalAnnualDeduction - totalBonusAmount;

                taxableIncomeElement.textContent = taxableIncome.toFixed(2);

                recalculateTax(taxableIncome);
            }

            function recalculateTax(taxableIncome) {
                let remainingIncome = taxableIncome;
                let totalTax = 0;
                let isFirstBracket = true;

                taxCalculationBody.innerHTML = '';

                taxBrackets.forEach(bracket => {
                    let from = bracket.annual_salary_from;
                    let to = bracket.annual_salary_to >= 1.0E+20 ? null : bracket.annual_salary_to;
                    let percent = bracket.tds_in_percent;
                    let bracketIncome = Math.min(Math.max(remainingIncome, 0), to ? to - from : remainingIncome);
                    let taxAmount = bracketIncome * (percent / 100);

                    if ((enableTaxExemption === 1) && isFirstBracket && ssfDeduction > 0) {
                        taxAmount = 0;
                    }

                    totalTax += taxAmount;
                    remainingIncome -= bracketIncome;
                    isFirstBracket = false;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                                <td>${from}</td>
                                <td>${to ? to : ''}</td>
                                <td>${bracketIncome}</td>
                                <td>${percent}%</td>
                                <td>${taxAmount}</td>
                            `;
                    taxCalculationBody.appendChild(row);
                });

                totalTaxElement.textContent = totalTax.toFixed(2);

                updateTotalPayableTax(totalTax);
            }

            function updateTotalPayableTax(totalTax) {
                const medicalClaim = parseFloat(document.getElementById('medical_claim').value) || 0;
                const femaleDiscount = parseFloat(document.getElementById('female_discount').value) || 0;
                const otherDiscount = parseFloat(document.getElementById('other_discount').value) || 0;

                const totalPayableTDS = totalTax - (medicalClaim + femaleDiscount + otherDiscount) + totalBonusTax;

                updateTotalDueTds(totalPayableTDS);
            }

            function updateTotalDueTds(totalPayableTDS) {
                const totalPaidTds = parseFloat(totalPaidTdsInput.value) || 0;
                const totalDueTds = totalPayableTDS - (totalPaidTds || 0);
                totalDueTdsElement.textContent = totalDueTds.toFixed(2);

                const totalMonth = parseFloat(totalMonthInput.value) || 1;
                let duePerMonth = 0;
                if (totalMonths == totalMonth) {
                    duePerMonth = totalDueTds;
                } else {
                    duePerMonth = (totalDueTds / totalMonths) * totalMonth;
                }

                remainTdsByMonthElement.textContent = duePerMonth.toFixed(2);
            }

            function number, decimals) {
                return parseFloat(number).toFixed(decimals);
            }

            otherComponentInputs.forEach(input => {
                input.addEventListener('input', recalculateTaxableIncome);
            });

            document.querySelectorAll('.editable-amount').forEach(input => {
                input.addEventListener('input', () => {
                    updateTotalPayableTax(parseFloat(totalTaxElement.textContent));
                });
            });

            recalculateTaxableIncome();
        });
    </script>
@endsection
