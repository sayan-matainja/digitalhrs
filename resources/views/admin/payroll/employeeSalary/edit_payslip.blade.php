@extends('layouts.master')
@php
    $enableTaxExemption = \App\Helpers\AppHelper::enableTaxExemption();
@endphp
@section('title',__('index.employee_payroll'))

@section('action',__('index.payroll_edit'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.employee-salary.payroll')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection
@section('style')
    <style>
        #net_salary:focus {
            outline: none !important;
            border: none !important;
        }
        .no-outline {
            outline: none !important;
            border: none !important;
        }
    </style>

@endsection
@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.employeeSalary.common.breadcrumb')

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('index.edit_salary_slip') }}</h5>
            </div>
            <div class="card-body" >

                <form action="{{ route('admin.employee-salary.payroll-update',$payrollData['payslipData']->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div>
                        <div class="payroll-personal">
                            <h5 class="border-bottom mb-4 pb-4">{{ __('index.payslip') }}
                                @if( isset($payroll['salary_cycle']) && $payroll['salary_cycle'] == 'monthly')
                                    {{__('index.for_the_month_of')}} {{ \App\Helpers\AppHelper::getMonthYear($payrollData['payslipData']->salary_from) }}
                                @else
                                    {{ __('index.from') }} {{ \App\Helpers\AttendanceHelper::payslipDate($payrollData['payslipData']->salary_from) }} to {{ \App\Helpers\AttendanceHelper::payslipDate($payrollData['payslipData']->salary_to) }}
                                @endif
                            </h5>
                            <table class="table table-responsive mb-4">
                                <tr>
                                    <td>{{ __('index.employee_name') }}</td> <td>{{ $payrollData['payslipData']->employee_name }}</td> <td>{{ __('index.joining_date') }}</td> <td>{{ $payrollData['payslipData']->joining_date ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('index.employee_id') }}</td> <td>{{ $payrollData['payslipData']->employee_code ?? 'N/A' }}</td><td>{{ __('index.designation') }}</td><td>{{ $payrollData['payslipData']->designation }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('index.marital_status') }}</td><td>{{ $payrollData['payslipData']->marital_status }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="payroll-fil border-bottom mb-4">

                            <div class="row">
                                <div class="col-lg-4 col-md-2 mb-4">
                                    <h5 class="mb-3">{{ __('index.status') }}</h5>
                                    @if($payrollData['payslipData']->status ==  $paidStatus)
                                        <input type="hidden" name="status" readonly value="{{ $payrollData['payslipData']->status }}">
                                        <span class="p-2 alert alert-primary">{{ ucfirst($payrollData['payslipData']->status) }}</span>

                                    @else
                                        <select name="status" class="form-control select2" id="payroll_status">
                                            @forelse(\App\Enum\PayslipStatusEnum::cases() as $case)
                                                <option @if($payrollData['payslipData']->status == $case->value) selected @endif  value="{{ $case->value }}"> {{ ucfirst($case->value) }} </option>
                                            @empty
                                            @endforelse
                                        </select>
                                    @endif

                                </div>


                                <div class="col-lg-4 col-md-5 md-4 paidPayslip @if($payrollData['payslipData']->status !=  $paidStatus) d-none @endif">
                                    <h5 class="mb-3">{{ __('index.payment_method') }}</h5>
                                    <select name="payment_method_id" class="form-control">
                                        <option selected disabled>{{ __('index.payment_method_placeholder') }}</option>
                                        @foreach($paymentMethods as $method)
                                            <option @if($payrollData['payslipData']->payment_method_id  ==  $method['id']) selected @endif value="{{ $method['id'] }}"> {{ $method['name'] }}</option>
                                        @endforeach

                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-5 mb-4 paidPayslip @if($payrollData['payslipData']->status !=  $paidStatus) d-none @endif">
                                    <h5 class="mb-3">{{ __('index.paid_on') }}</h5>
                                    <input type="date" class="form-control" name="paid_on" value="{{ isset($payrollData['payslipData']->paid_on) ? date('Y-m-d', strtotime($payrollData['payslipData']->paid_on)) : date('Y-m-d') }}">
                                </div>

                            </div>
                        </div>

                        <div class="payroll-earn-ded">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 mb-4">
                                    <h4 class="mb-2">{{ __('index.earning') }}</h4>
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr class="earning">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ __('index.basic_salary') }}</strong>
                                                <input type="text" readonly class="form-control w-50" name="monthly_basic_salary" id="monthly_basic_salary" value="{{ ($payrollData['payslipData']->salary_cycle == 'weekly') ? $payrollData['payslipData']->weekly_basic_salary :$payrollData['payslipData']->monthly_basic_salary }}">
                                            </td>
                                        </tr>
                                        @php
                                            if($payrollData['payslipData']->salary_cycle == 'weekly'){
                                                $totalEarning = $payrollData['payslipData']->weekly_basic_salary+$payrollData['payslipData']->weekly_fixed_allowance;

                                            }else{
                                                $totalEarning = $payrollData['payslipData']->monthly_basic_salary+$payrollData['payslipData']->monthly_fixed_allowance;
                                            }
                                        @endphp
                                        @forelse($payrollData['earnings'] as $earning)
                                            <tr class="earning">
                                                <td class="d-flex align-items-center justify-content-between">
                                                    <strong>{{ $earning['name'] }}</strong>
                                                    <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif oninput="restrictNumber(this)" id="earning_amount[{{$earning['salary_component_id']}}]" name="component_amount[{{$earning['salary_component_id']}}]" class="form-control w-50 income_amount" value="{{ $earning['amount'] }}" >
                                                </td>
                                            </tr>
                                            @php $totalEarning += $earning['amount'];  @endphp
                                        @empty

                                        @endforelse
                                        <tr class="earning">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ __('index.fixed_allowance') }}</strong>
                                                <input type="text" readonly oninput="restrictNumber(this)" class="form-control w-50" name="monthly_fixed_allowance" id="monthly_fixed_allowance" value="{{ ($payrollData['payslipData']->salary_cycle == 'weekly') ? $payrollData['payslipData']->weekly_fixed_allowance :$payrollData['payslipData']->monthly_fixed_allowance }}">
                                            </td>
                                        </tr>
                                        @forelse($payrollData['additionalEarnings'] as $earning)
                                            <tr class="deductions">
                                                <td class="d-flex align-items-center justify-content-between">
                                                    <strong>{{ $earning['name'] }}</strong>
                                                    <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif oninput="restrictNumber(this)" id="additional_earning_amount[{{$earning['salary_component_id']}}]" name="additional_component_amount[{{$earning['salary_component_id']}}]" class="form-control w-50 additional_income_amount"
                                                           value="{{ $earning['amount'] }}">
                                                </td>
                                            </tr>

                                            @php $totalEarning += $earning['amount']; @endphp
                                        @empty
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-lg-6 col-md-6 mb-4">
                                    <h4 class="mb-2">{{ __('index.deduction') }}</h4>
                                    <table class="table table-bordered">
                                        <tbody>
                                        @php $totalDeduction = $payrollData['payslipData']->ssf_deduction + $payrollData['payslipData']->pf_deduction; @endphp
                                        @forelse($payrollData['deductions'] as $deduction)
                                            <tr class="deductions">
                                                <td class="d-flex align-items-center justify-content-between">
                                                    <strong>{{ $deduction['name'] }}</strong>
                                                    <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif oninput="restrictNumber(this)" name="component_amount[{{$deduction['salary_component_id']}}]" id="deduction_amount[{{$deduction['salary_component_id']}}]" class="form-control w-50 deduction_amount" value="{{ $deduction['amount'] }}" >
                                                </td>
                                                @php $totalDeduction += $deduction['amount']; @endphp
                                            </tr>
                                        @empty

                                        @endforelse
                                        @if($payrollData['payslipData']->ssf_deduction > 0)
                                            <tr class="deductions">
                                                <td class="d-flex align-items-center justify-content-between">
                                                    <strong>{{ __('index.ssf_deduction') }}</strong>
                                                    <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif class="form-control w-50" oninput="restrictNumber(this)" name="ssf_deduction" id="ssf_deduction" value="{{ $payrollData['payslipData']->ssf_deduction }}">
                                                </td>
                                            </tr>
                                        @endif
                                         @if($payrollData['payslipData']->pf_deduction > 0)
                                            <tr class="deductions">
                                                <td class="d-flex align-items-center justify-content-between">
                                                    <strong>{{ __('index.pf_deduction') }}</strong>
                                                    <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif class="form-control w-50" oninput="restrictNumber(this)" name="pf_deduction" id="pf_deduction" value="{{ $payrollData['payslipData']->pf_deduction }}">
                                                </td>
                                            </tr>
                                        @endif


                                        @forelse($payrollData['additionalDeductions'] as $deduction)
                                            <tr class="deductions">
                                                <td class="d-flex align-items-center justify-content-between">
                                                    <strong>{{ $deduction['name'] }}</strong>
                                                    <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif oninput="restrictNumber(this)" name="additional_component_amount[{{$deduction['salary_component_id']}}]" id="additional_deduction_amount[{{$deduction['salary_component_id']}}]" class="form-control w-50 additional_deduction_amount" value="{{ $deduction['amount'] }}">
                                                </td>
                                            </tr>
                                            @php $totalDeduction += $deduction['amount']; @endphp
                                        @empty
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-lg-12 pb-3">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <label class="mb-1 fw-bold">{{ __('index.actual_salary') }}</label>{{ __('index.actual_salary_formula') }}
                                        </div>
                                        <div class="col-lg-3">
                                            <span class="h5" id="actual_salary">{{ $currency.' '. $totalEarning - $totalDeduction  }}</span>
                                        </div>

                                    </div>
                                </div>
                                @if($payrollData['payslipData']->bonus > 0)
                                    <div class="col-lg-12 border-top py-3">
                                        <div class="row">
                                            <div class="col-lg-9">
                                                <label class="mb-1 fw-bold">{{ __('index.bonus') }}</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="hidden" class="form-control" name="bonus" oninput="restrictNumber(this)" id="bonus" value="{{ $payrollData['payslipData']->bonus }}">
                                                <span class="h5" id="bonusAmount">{{ $currency.' '. $payrollData['payslipData']->bonus }}</span>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-lg-12 border-top py-3">
                                        <div class="row">
                                            <div class="col-lg-9">
                                                <label class="mb-1 fw-bold">{{ __('index.taxable_salary') }} </label> {{ __('index.taxable_salary_formula') }}
                                            </div>
                                            <div class="col-lg-3">
                                                <span class="h5" id="taxable_salary">{{ $currency.' '. $totalEarning + $payrollData['payslipData']->bonus - $totalDeduction  }}</span>
                                            </div>

                                        </div>
                                    </div>
                                @endif
                                <div class="col-lg-12 border-top py-3">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <label class="mb-1 fw-bold">{{ __('index.tax') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input type="hidden" class="form-control" name="tds" oninput="restrictNumber(this)" id="tds" value="{{ $payrollData['payslipData']->tds }}">
                                            <span class="h5" id="tax">{{ $currency.' '.$payrollData['payslipData']->tds }}</span>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-lg-12 border-top py-3">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <label class="mb-1 fw-bold">{{ __('index.salary_after_tax') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <span class="h5" id="salaryAfterTax">{{ $currency.' '.$totalEarning + $payrollData['payslipData']->bonus - $totalDeduction - $payrollData['payslipData']->tds }}</span>
                                        </div>

                                    </div>
                                </div>
                                @if($payrollData['payslipData']->include_tada == 1)
                                    <div class="col-lg-6 col-md-6 border-top py-3">
                                        <div class="row">
                                            <div class="col-lg-9">
                                                <small style="color:#e82e5f;">{{ __('index.earning') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.expenses_claim') }}</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif id="tada" oninput="restrictNumber(this)" name="tada" value="{{ $payrollData['payslipData']->tada }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($payrollData['payslipData']->include_advance_salary == 1)

                                    <div class="col-lg-6 col-md-6 border-top py-3">
                                        <div class="row">
                                            <div class="col-lg-9">
                                                <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.advance_salary') }}</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif id="advanceSalary" oninput="restrictNumber(this)" name="advance_salary" class="form-control" value="{{ $payrollData['payslipData']->advance_salary }}">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($payrollData['payslipData']->loan_amount > 0)

                                    <div class="col-lg-6 col-md-6 border-top py-3">
                                        <div class="row">
                                            <div class="col-lg-9">
                                                <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.loan_amount') }}</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="hidden" id="original_loan_amount" value="{{ $payrollData['payslipData']->loan_amount }}">

                                                <input type="text" readonly id="loan_amount" oninput="restrictNumber(this)" name="loan_amount" class="form-control" value="{{ $payrollData['payslipData']->loan_amount }}">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-lg-6 col-md-6 border-top py-3">
                                    <div class="row">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 border-top py-3">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.absent') }}</label>
                                            @if($payrollData['payslipData']->salary_cycle == 'monthly')
                                                {{ __('index.absent_deduction_formula') }}
                                            @else
                                                {{ __('index.weekly_absent_deduction_formula') }}
                                            @endif
                                        </div>
                                        <div class="col-lg-4">
                                            <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif name="absent_deduction" class="form-control" id="absentDeduction" value="{{ $payrollData['payslipData']->absent_deduction }}">
                                        </div>
                                    </div>
                                </div>
                                @if(isset($payrollData['payslipData']->ot_status) && $payrollData['payslipData']->ot_status  == 1)

                                    <div class="col-lg-6 col-md-6 border-top py-3">
                                        <div class="row">
                                            <div class="col-lg-9">
                                                <small style="color:#e82e5f;">{{ __('index.earning') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.overtime') }}</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif oninput="restrictNumber(this)" id="overtime" name="overtime" value="{{ $payrollData['payslipData']->overtime }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if(isset($underTimeSetting) && $underTimeSetting->is_active  == 1)
                                    <div class="col-lg-6 col-md-6 border-top py-3">
                                        <div class="row">
                                            <div class="col-lg-9">
                                                <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.undertime') }}</label>
                                            </div>
                                            <div class="col-lg-3">
                                                <input type="text" @if($payrollData['payslipData']->status ==  $paidStatus) readonly @endif oninput="restrictNumber(this)" id="undertime" name="undertime" value="{{ $payrollData['payslipData']->undertime  }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-lg-12 border-top py-3">
                                    <input type="hidden" readonly name="net_salary" id="netSalary" value="{{ $payrollData['payslipData']->net_salary }}">
                                    <h4 class="mb-1">{{ __('index.net_salary') }} : {{ $currency }} <span id="net_salary">{{ $payrollData['payslipData']->net_salary }}</span></h4>
                                    {{ __('index.net_salary_formula') }}
                                </div>
                            </div>
                        </diV>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('index.update') }}</button>
                </form>
            </div>

        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.payroll.employeeSalary.common.scripts')
    <script>
        function restrictNumber(input) {
            let value = input.value;

            value = value.replace(/[^0-9.]/g, '');

            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            if (parts[1] && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].slice(0, 2);
            }

            if (value.startsWith('-')) {
                value = value.replace('-', '');
            }

            if (value.length > 1 && value.startsWith('0') && !value.startsWith('0.')) {
                value = value.replace(/^0+/, '') || '0';
            }

            if (value === '') {
                input.value = '';
                return;
            }

            input.value = value;
        }

        $(document).ready(function () {
            function debounce(func, wait) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            function calculateAllowance() {
                let totalEarnings = 0;
                let grossSalary = parseFloat({{ $payrollData['payslipData']->gross_salary }}) || 0;
                let newBasicSalary = parseFloat($('#monthly_basic_salary').val()) || 0;

                $('.income_amount').each(function () {
                    let earningAmount = parseFloat($(this).val()) || 0;
                    totalEarnings += earningAmount;
                });

                let newAllowance = (grossSalary - newBasicSalary - totalEarnings);
                $('#monthly_fixed_allowance').val(newAllowance.toFixed(2));
                changeNetSalary();
            }

            function changeNetSalary() {
                $('#net_salary').text('Calculating...');
                let salaryCycle = '{{ $payrollData['payslipData']->salary_cycle }}';
                let enableTaxExemption = '{{ $enableTaxExemption }}';
                let maritalStatus = '{{ $payrollData['payslipData']->marital_status }}';
                let basicSalary = parseFloat($('#monthly_basic_salary').val()) || 0;
                let fixedAllowance = parseFloat($('#monthly_fixed_allowance').val()) || 0;
                let ssf_deduction = parseFloat($('#ssf_deduction').val()) || 0;
                let pf_deduction = parseFloat($('#pf_deduction').val()) || 0;
                let bonus = parseFloat($('#bonus').val()) || 0;

                let totalEarnings = 0;
                $('.income_amount').each(function () {
                    let earningAmount = parseFloat($(this).val()) || 0;
                    totalEarnings += earningAmount;
                });

                $('.additional_income_amount').each(function () {
                    let additionalEarningAmount = parseFloat($(this).val()) || 0;
                    totalEarnings += additionalEarningAmount;
                });

                let totalDeduction = 0;
                $('.deduction_amount').each(function () {
                    let deductionAmount = parseFloat($(this).val()) || 0;
                    totalDeduction += deductionAmount;
                });

                $('.additional_deduction_amount').each(function () {
                    let additionalDeductionAmount = parseFloat($(this).val()) || 0;
                    totalDeduction += additionalDeductionAmount;
                });

                let grossSalary = basicSalary + fixedAllowance + totalEarnings;
                if (grossSalary <= 0) {
                    $('#netSalary').val(0);
                    $('#net_salary').text(0);
                    return;
                }

                let actualSalary = grossSalary - (totalDeduction + ssf_deduction + pf_deduction);
                let tadaAmount = parseFloat($('#tada').val()) || 0;
                let loanAmount = parseFloat($('#loan_amount').val()) || 0;
                let advanceSalary = parseFloat($('#advanceSalary').val()) || 0;
                let absentDeduction = parseFloat($('#absentDeduction').val()) || 0;
                let overtime = parseFloat($('#overtime').val()) || 0;
                let undertime = parseFloat($('#undertime').val()) || 0;

                let taxableNormalSalary = actualSalary * (salaryCycle === 'weekly' ? 52 : 12);
                let taxableBonusAmount = bonus; // Treat bonus as a one-time payment

                Promise.all([
                    calculateTaxPromise(taxableNormalSalary, maritalStatus),
                    calculateTaxPromise(taxableBonusAmount, maritalStatus, true)
                ]).then(([normalTaxData, bonusTaxData]) => {
                    let monthlyTax = 0;
                    let yearlyTax = normalTaxData.total_tax;
                    let bonusTax = bonusTaxData.total_tax; // Use total_tax for one-time bonus

                    if (ssf_deduction > 0 && enableTaxExemption == 1) {
                        yearlyTax -= normalTaxData.sst;
                        monthlyTax = yearlyTax / (salaryCycle === 'weekly' ? 52 : 12);
                    } else {
                        monthlyTax = normalTaxData.monthly_tax;
                    }

                    let totalTax = monthlyTax + bonusTax;

                    $('#tds').val(totalTax.toFixed(2));
                    $('#tax').text(totalTax.toFixed(2));

                    let taxableSalary = actualSalary + bonus;
                    let salaryAfterTax = taxableSalary - totalTax;
                    let netSalaryFinal = salaryAfterTax - advanceSalary + tadaAmount -loanAmount - absentDeduction + overtime - undertime;

                    $('#taxable_salary').text(taxableSalary.toFixed(2) + ' (Monthly)');
                    $('#salaryAfterTax').text(salaryAfterTax.toFixed(2));
                    $('#actual_salary').text(actualSalary.toFixed(2));
                    $('#netSalary').val(netSalaryFinal.toFixed(2));
                    $('#net_salary').text(netSalaryFinal.toFixed(2));
                }).catch(error => {
                    console.error('Tax calculation error:', error);
                    $('#net_salary').text('Error');
                    alert('Error calculating tax. Please try again.');
                });
            }

            function calculateTaxPromise(salary, maritalStatus, isBonus = false) {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: '{{ route('admin.get-tax') }}',
                        type: 'GET',
                        data: {
                            salary: salary,
                            marital_status: maritalStatus,
                            is_bonus: isBonus
                        },
                        success: function (response) {
                            if (response.success) {
                                resolve(response.data);
                            } else {
                                reject('Failed to calculate tax');
                            }
                        },
                        error: function (error) {
                            reject(error);
                        }
                    });
                });
            }

            if ($('#payroll_status').val() !== '{{ $paidStatus }}') {
                $('#monthly_basic_salary, .income_amount, .additional_income_amount, .deduction_amount, .additional_deduction_amount, #ssf_deduction,#pf_deduction, #bonus, #tada,#loan_amount, #advanceSalary, #absentDeduction, #overtime, #undertime').on('input', debounce(function () {
                    let value = parseFloat($(this).val()) || 0;
                    if (value < 0) {
                        $(this).val(0);
                    }
                    if ($(this).is('#monthly_basic_salary') || $(this).hasClass('income_amount')) {
                        calculateAllowance();
                    } else {
                        changeNetSalary();
                    }
                }, 300));
            }

            $('#payroll_status').on('change', function () {
                let status = $(this).val();
                if (status === '{{ $paidStatus }}') {
                    $('.paidPayslip').removeClass('d-none');
                } else {
                    $('.paidPayslip').addClass('d-none');
                }
            });


            $('#loan_amount').on('blur', function() {
                let originalValue = parseFloat($('#original_loan_amount').val()) || 0;
                let currentValue = parseFloat($(this).val()) || 0;

                if (currentValue < originalValue) {
                    $(this).val(originalValue.toFixed(2));
                    alert('Loan amount cannot be less than its original value (' + originalValue.toFixed(2) + ').');
                }
            });



        });
    </script>
@endsection

