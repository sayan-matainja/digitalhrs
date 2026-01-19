@php
    use App\Helpers\AppHelper;
    use Illuminate\Support\Facades\DB;
@endphp
@extends('layouts.master')

@section('title', __('index.loan_repayment'))

@section('action', __('index.repayment_list'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.loanManagement.loan.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.loan_repayment_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.loan-repayment.list') }}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-2 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option
                                    value="" {{ ($filterParameters['branch_id'] ?? '') == '' ? 'selected' : '' }}>{{ __('index.select_branch') }}</option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ $filterParameters['branch_id'] ?? '' == $branch->id ? 'selected' : '' }}>
                                            {{ ucfirst($branch->name) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="type_id" id="type">
                            <option
                                value="" {{ ($filterParameters['type_id'] ?? '') == '' ? 'selected' : '' }}>{{ __('index.select_loan_type') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="department_id" id="department">
                            <option
                                value="" {{ ($filterParameters['department_id'] ?? '') == '' ? 'selected' : '' }}>{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="employee_id" id="employee">
                            <option
                                value="" {{ ($filterParameters['employee_id'] ?? '') == '' ? 'selected' : '' }}>{{ __('index.select_employee') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-success me-2">{{ __('index.filter') }}</button>
                            <a href="{{ route('admin.loan-repayment.list') }}"
                               class="btn btn-block btn-primary">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.loan_repayment_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ __('index.payment_date') }}</th>
                            <th>{{ __('index.loan_id') }}</th>
                            <th>{{ __('index.employee') }}</th>
                            <th>{{ __('index.loan_type') }}</th>
                            <th class="text-center">{{ __('index.loan_amount') }}</th>
                            <th class="text-center">{{ __('index.remaining_balance') }}</th>
                            <th class="text-center">{{ __('index.interest_rate') }}</th>
                            <th class="text-center">{{ __('index.monthly_installment') }}</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($loanLists as $key => $value)
                            @php

                                $currentDate = \Carbon\Carbon::now();
                                $currentYear = $currentDate->year;
                                $currentMonth = $currentDate->month;

                                $currentPayment = $value->loanRepayment
                                        ->filter(function ($repayment) use ($currentYear, $currentMonth) {
                                            $paymentDate =  \Carbon\Carbon::parse($repayment->payment_date);
                                            return
                                                   $paymentDate->year === $currentYear
                                                   && $paymentDate->month === $currentMonth;
                                        })
                                        ->sortBy('payment_date')
                                        ->first();

                            @endphp
                            @if(isset($currentPayment))
                                <tr>
                                    @php
                                        $totalPaid = $value->loanRepayment
                                            ->where('status', \App\Enum\LoanRepaymentStatusEnum::paid->value)
                                            ->sum(function ($repayment) {
                                                return ($repayment->principal_amount ?? 0) + ($repayment->settlement_amount ?? 0);
                                            });


                                        $balance = 0;
                                        if(isset($value->loan_amount)){
                                            $balance = $value->loan_amount - $totalPaid;
                                        }
                                        $installment = 0;

                                       if(isset($currentPayment->principal_amount) && isset($currentPayment->interest_amount)){
                                           $installment = $currentPayment->principal_amount + $currentPayment->interest_amount ;
                                       }


                                    @endphp
                                    <td class="text-center">{{ ++$key }}</td>
                                    <td>{{ $currentPayment->payment_date ? AppHelper::formatDateForView($currentPayment->payment_date) : '' }}</td>
                                    <td>{{ ucfirst($value->loan_id ?? '') }}</td>
                                    <td>{{ ucfirst($value->employee->name ?? '') }}</td>
                                    <td>{{ ucfirst($value->loanType->name ?? '') }}</td>
                                    <td class="text-center">{{ AppHelper::formatCurrencyAmount($value->loan_amount) }}</td>
                                    <td class="text-center">{{ AppHelper::formatCurrencyAmount($balance) }}</td>
                                    <td class="text-center">{{ ($value->loanType->interest_rate ?? 0) }}%</td>
                                    <td class="text-center">{{ AppHelper::formatCurrencyAmount($installment) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.loan-repayment.detail', $value->id) }}"
                                           title="{{ __('index.loan_detail') }}">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="10">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $loanLists->links() }}
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.loanManagement.loan.common.scripts')
@endsection
