@extends('layouts.master')

@section('title',__('index.employee_payslip'))

@section('action',__('index.detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.employee-salaries.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.employeeSalary.common.breadcrumb')

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">{{ __('index.salary_slip') }}</h6>
                <div class="payslip_print">
                    @can('print_payroll')
                        <a class="me-2"
                           href="{{ route('admin.employee-salary.payroll-print',$payrollData['payslipData']->id) }}"
                           target="_blank">
                            <i class="link-icon" data-feather="printer"></i>
                        </a>
                    @endcan

                    {{--                        @if($payrollData['payslipData']->status == \App\Enum\PayslipStatusEnum::generated->value)--}}
                    @can('edit_payroll')
                        <a class="me-2"
                           href="{{ route('admin.employee-salary.payroll-edit',$payrollData['payslipData']->id) }}">
                            <i class="link-icon" data-feather="edit"></i>
                        </a>
                    @endcan
                    {{--                        @endif--}}
                </div>
            </div>
            <div class="card-body">

                <form class="forms-sample" action="" method="POST">
                    @csrf
                    <div class="payroll-personal">
                        <div class="row align-items-center justify-content-between border-bottom mb-4">
                            <div class="col-lg col-md-6 mb-4">
                                <div class="d-flex align-items-center">

                                    <img class="wd-50 ht-50 rounded-circle" style="object-fit: cover"
                                         src="{{ asset($imagePath . $payrollData['payslipData']->employee_avatar) }}" alt="{{$payrollData['payslipData']->employee_name}}">
                                    <div class="text-start ms-3">
                                        <h5 class="mb-1">{{ $payrollData['payslipData']->employee_name }}</h5>
                                        <p class="">{{ $payrollData['payslipData']->employee_email }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <div class="gross-sal p-3 border d-md-inline-block float-md-end text-center">
                                    <span>{{ __('index.employee_gross_salary') }}</span>
                                    <h4>{{ $currency.' '. $payrollData['payslipData']->gross_salary }}</h4>
                                </div>
                            </div>
                        </div>

                        <div class="row border-bottom mb-4">
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.marital_status') }}</h6>
                                <span>{{ $payrollData['payslipData']->marital_status }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.designation') }}</h6>
                                <span>{{ $payrollData['payslipData']->designation }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.joining_date') }}</h6>
                                <span>{{ $payrollData['payslipData']->joining_date }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.salary_cycle') }}</h6>
                                <span>{{ $payrollData['payslipData']->salary_cycle }}</span>
                            </div>
                        </div>
                    </div>

                    @if($payrollData['payslipData']->salary_cycle == 'weekly')
                        <div class="row border-bottom mb-4">
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.total_working_hours') }}</h6>
                                <span>{{ $payrollData['payslipData']->working_hours }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.total_worked_hours') }}</h6>
                                <span>{{ $payrollData['payslipData']->worked_hours }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.total_deficit_hours') }}</h6>
                                <span>{{ $payrollData['payslipData']->working_hours - $payrollData['payslipData']->worked_hours }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.total_overtime_hours') }}</h6>
                                <span>{{ $payrollData['payslipData']->overtime_hours }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.total_undertime_hours') }}</h6>
                                <span>{{ $payrollData['payslipData']->undertime_hours }}</span>
                            </div>

                        </div>
                    @else
                        <div class="row border-bottom mb-4">
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.total_day') }}</h6>
                                <span>{{ $payrollData['payslipData']->total_days }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.present') }}</h6>
                                <span>{{ $payrollData['payslipData']->present_days }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.absent') }}</h6>
                                <span>{{ $payrollData['payslipData']->absent_days }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.paid_leave') }}</h6>
                                <span>{{ $payrollData['payslipData']->paid_leave }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.unpaid_leave') }}</h6>
                                <span>{{ $payrollData['payslipData']->unpaid_leave }}</span>
                            </div>
                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.holidays') }}</h6>
                                <span>{{ $payrollData['payslipData']->holidays }}</span>
                            </div>

                            <div class="col-lg col-md-6 mb-4">
                                <h6>{{ __('index.weekend') }}</h6>
                                <span>{{ $payrollData['payslipData']->weekends }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="payroll-fil border-bottom mb-4">
                        <div class="row">
                            <div class="col-lg-4 col-md-2 mb-4">
                                <h5 class="mb-3">{{ __('index.status') }}</h5>
                                <span class="p-2 alert alert-{{ $payrollData['payslipData']->status == \App\Enum\PayslipStatusEnum::generated->value ? 'success' : ($payrollData['payslipData']->status == \App\Enum\PayslipStatusEnum::review->value ? 'warning' : ($payrollData['payslipData']->status == \App\Enum\PayslipStatusEnum::locked->value ? 'danger' : 'primary')) }}">{{ ucfirst($payrollData['payslipData']->status) }}</span>
                            </div>
                            <div class="col-lg-4 col-md-5 mb-4">
                                <h5 class="mb-2">{{ __('index.salary_from') }}</h5>

                                <input id="salaryTo" readonly name="salary_from" value="{{  \App\Helpers\AttendanceHelper::payslipDate($payrollData['payslipData']->salary_from) }}" class="form-control" type="text">
                            </div>
                            <div class="col-lg-4 col-md-5 mb-4">
                                <h5 class="mb-2">{{ __('index.salary_to') }}</h5>
                                <input id="salaryTo" readonly name="salary_to" value="{{  \App\Helpers\AttendanceHelper::payslipDate($payrollData['payslipData']->salary_to) }}" class="form-control" type="text">
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
                                            <input type="text" readonly class="form-control w-50" value="{{ ($payrollData['payslipData']->salary_cycle == 'weekly') ? $payrollData['payslipData']->weekly_basic_salary :$payrollData['payslipData']->monthly_basic_salary }}">
                                        </td>
                                    </tr>
                                    @php
                                        $totalEarning = 0;
                                        if($payrollData['payslipData']->salary_cycle == 'weekly'){
                                            $totalEarning += $payrollData['payslipData']->weekly_basic_salary+$payrollData['payslipData']->weekly_fixed_allowance ;

                                        }else{
                                            $totalEarning += $payrollData['payslipData']->monthly_basic_salary+$payrollData['payslipData']->monthly_fixed_allowance;
                                        }
                                    @endphp
                                    @forelse($payrollData['earnings'] as $earning)
                                        <tr class="earning">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ $earning['name'] }}</strong>
                                                <input type="number" step="0.1" min="0" readonly class="form-control w-50" value="{{ $earning['amount'] }}" >
                                            </td>
                                        </tr>
                                        @php $totalEarning += $earning['amount'];  @endphp
                                    @empty

                                    @endforelse
                                    <tr class="earning">
                                        <td class="d-flex align-items-center justify-content-between">
                                            <strong>{{ __('index.fixed_allowance') }}</strong>
                                            <input type="text" readonly step="0.1" min="0" class="form-control w-50" value="{{ ($payrollData['payslipData']->salary_cycle == 'weekly') ? $payrollData['payslipData']->weekly_fixed_allowance :$payrollData['payslipData']->monthly_fixed_allowance }}">
                                        </td>
                                    </tr>
                                    @forelse($payrollData['additionalEarnings'] as $earning)
                                        <tr class="earning">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ $earning['name'] }}</strong>
                                                <input type="text" readonly step="0.1" min="0" class="form-control w-50"
                                                       value="{{ $earning['amount'] }}">
                                            </td>
                                        </tr>

                                        @php $totalEarning += $earning['amount']; @endphp
                                    @empty
                                    @endforelse
                                    <tr>
                                        <td><strong>{{ __('index.total_earning') }}</strong> <span class="float-end"><strong>{{ $totalEarning}}</strong></span></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-lg-6 col-md-6 mb-4">
                                <h4 class="mb-2">{{ __('index.deduction') }}</h4>
                                <table class="table table-bordered">
                                    <tbody>
                                    @php
                                        $totalDeduction = $payrollData['payslipData']->ssf_deduction + $payrollData['payslipData']->pf_deduction;
                                    @endphp
                                    @forelse($payrollData['deductions'] as $deduction)
                                        <tr class="deductions">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ $deduction['name'] }}</strong>
                                                <input type="number" step="0.1" min="0" readonly
                                                       class="form-control w-50" value="{{ $deduction['amount'] }}">
                                            </td>
                                        </tr>
                                        @php $totalDeduction += $deduction['amount']; @endphp
                                    @empty
                                    @endforelse
                                    @if($payrollData['payslipData']->ssf_deduction > 0)
                                        <tr class="deductions">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ __('index.ssf_deduction') }}</strong>
                                                <input type="text" readonly step="0.1" min="0" class="form-control w-50"
                                                       value="{{ $payrollData['payslipData']->ssf_deduction }}">
                                            </td>
                                        </tr>
                                    @endif
                                    @if($payrollData['payslipData']->pf_deduction > 0)
                                        <tr class="deductions">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ __('index.pf_deduction') }}</strong>
                                                <input type="text" readonly step="0.1" min="0" class="form-control w-50"
                                                       value="{{ $payrollData['payslipData']->pf_deduction }}">
                                            </td>
                                        </tr>
                                    @endif

                                    @forelse($payrollData['additionalDeductions'] as $deduction)
                                        <tr class="deductions">
                                            <td class="d-flex align-items-center justify-content-between">
                                                <strong>{{ $deduction['name'] }}</strong>
                                                <input type="text" readonly step="0.1" min="0" class="form-control w-50"
                                                       value="{{ $deduction['amount'] }}">
                                            </td>
                                        </tr>

                                        @php $totalDeduction += $deduction['amount']; @endphp
                                    @empty
                                    @endforelse
                                    <tr>
                                        <td>
                                            <strong>{{ __('index.total_deduction') }}</strong> <span class="float-end"><strong>{{ $totalDeduction }}</strong></span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-lg-12 border-top pt-3 mb-1">
                                <div class="row align-items-center">
                                    <div class="col-lg col-md-6 mb-2">
                                        <label class="fw-bold">{{ __('index.actual_salary') }}</label> {{ __('index.actual_salary_formula') }}
                                    </div>
                                    <div class="col-lg col-md-6 mb-2">
                                        <span class="h5" id="actual_salary">{{ $currency.' '. $totalEarning - $totalDeduction  }}</span>
                                        <span class="text-danger">
                                            {{ __('index.taxable_income_in_brackets') }}
                                        </span>
                                    </div>

                                </div>
                            </div>
                            @php
                                $totalExtraEarning = 0;
                                $totalExtraDeduction = 0;
                            @endphp
                            @if($payrollData['payslipData']->bonus > 0)
                                <div class="col-lg-12 border-top pt-3 mb-1">
                                    <div class="row align-items-center">
                                        <div class="col-lg col-md-6 mb-2">
                                            <label class="fw-bold">{{ __('index.bonus') }}</label>
                                        </div>
                                        <div class="col-lg col-md-6 mb-2">
                                            <span class="h5" id="bonus">{{ $currency.' '. $payrollData['payslipData']->bonus }}</span>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-lg-12 border-top pt-3 mb-1">
                                    <div class="row align-items-center">
                                        <div class="col-lg col-md-6 mb-2">
                                            <label class="fw-bold">{{ __('index.taxable_salary') }}</label> {{ __('index.taxable_salary_formula') }}
                                        </div>
                                        <div class="col-lg col-md-6 mb-2">
                                            <span class="h5" id="taxable_salary">{{ $currency.' '. $totalEarning + $payrollData['payslipData']->bonus - $totalDeduction  }}</span>
                                        </div>

                                    </div>
                                </div>
                            @endif

                            <div class="col-lg-12 border-top pt-3 mb-1">
                                <div class="row align-items-center">
                                    <div class="col-lg col-md-6 mb-2">
                                        <label class="fw-bold" style="color:#e82e5f;">{{ __('index.tax') }}</label>
                                    </div>
                                    <div class="col-lg col-md-6 mb-2">
                                        <span class="h5" id="tax">{{ $currency.' '.$payrollData['payslipData']->tds }}</span>
                                    </div>

                                </div>
                            </div>
                            <div class="col-lg-12 border-top pt-3 mb-1">
                                <div class="row align-items-center">
                                    <div class="col-lg col-md-6 mb-2">
                                        <label class="fw-bold">{{ __('index.salary_after_tax') }}</label>
                                    </div>
                                    <div class="col-lg col-md-6 mb-2">
                                        <span class="h5" id="tax">{{ $currency.' '. $totalEarning + $payrollData['payslipData']->bonus - $totalDeduction - $payrollData['payslipData']->tds  }}</span>
                                    </div>

                                </div>
                            </div>


                            @if($payrollData['payslipData']->include_tada ==1)
                                <div class="col-lg-6 col-md-6 border-top pt-3 mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-lg-9">
                                            <small style="color:#2fd21d;">{{ __('index.earning') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.tada') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input id="tada" readonly name="tada" value="{{ $payrollData['payslipData']->tada }}" class="form-control" type="text">
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($payrollData['payslipData']->include_advance_salary ==1)
                                <div class="col-lg-6 col-md-6 border-top pt-3 mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-lg-9">
                                            <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.advance_salary') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input id="advance_salary" readonly name="advance_salary" value="{{ $payrollData['payslipData']->advance_salary  }}" class="form-control" type="text">
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($payrollData['payslipData']->loan_amount > 0)
                                <div class="col-lg-6 col-md-6 border-top pt-3 mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-lg-9">
                                            <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.loan_amount') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input id="loan_amount" readonly name="loan_amount" value="{{ $payrollData['payslipData']->loan_amount  }}" class="form-control" type="text">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-lg-6 col-md-6 border-top pt-3 mb-3">
                                <div class="row align-items-center">

                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 border-top pt-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-lg-9">
                                        <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.absent') }}</label>
                                        @if($payrollData['payslipData']->salary_cycle == 'monthly')
                                            {{ __('index.absent_deduction_formula') }}
                                        @else
                                            {{ __('index.weekly_absent_deduction_formula') }}
                                        @endif
                                    </div>

                                    <div class="col-lg-3">
                                        <input id="absent_deduction" readonly name="absent_deduction" value="{{  $payrollData['payslipData']->absent_deduction }}" class="form-control" type="text">
                                    </div>
                                </div>
                            </div>

                            @if(isset($payrollData['payslipData']->ot_status) && $payrollData['payslipData']->ot_status  == 1)
                                <div class="col-lg-6 col-md-6 border-top  mb-3 pt-3">
                                    <div class="row align-items-center">
                                        <div class="col-lg-9">
                                            <small style="color:#2cc21a;">{{ __('index.earning') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.overtime') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input id="overtime" readonly name="overtime" value="{{ $payrollData['payslipData']->overtime }}" class="form-control" type="text">
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if(isset($underTimeSetting) && $underTimeSetting->is_active  == 1)
                                <div class="col-lg-6 col-md-6 border-top mb-3 pt-3">
                                    <div class="row align-items-center">
                                        <div class="col-lg-9">
                                            <small style="color:#e82e5f;">{{ __('index.deduction') }}*</small><br><label class="mb-0 fw-bold">{{ __('index.undertime') }}</label>
                                        </div>
                                        <div class="col-lg-3">
                                            <input id="undertime" readonly name="undertime" value="{{ $payrollData['payslipData']->undertime  }}" class="form-control" type="text">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </diV>

                </form>
            </div>
            <div class="card-footer">
                <h4 class="mb-1">{{ __('index.net_salary') }} : {{ $currency.' '. $payrollData['payslipData']->net_salary }}</h4>
                {{ __('index.net_salary_formula') }}
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.payroll.employeeSalary.common.scripts')
@endsection

