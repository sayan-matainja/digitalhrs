@php use App\Enum\LoanStatusEnum;use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.loan'))

@section('action', __('index.lists'))

@section('button')
    @can('create_loans')
        <a href="{{ route('admin.loan.create') }}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_loan') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.loanManagement.loan.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.loans_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.loan.index') }}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-2 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option selected disabled>{{ __('index.select_branch') }}</option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected' : '' }}>
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
                                value="" {{ !isset($filterParameters['type_id']) ? 'selected' : '' }}>{{ __('index.select_loan_type') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="department_id" id="department">
                            <option
                                value="" {{ !isset($filterParameters['department_id']) ? 'selected' : '' }}>{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="employee_id" id="employee">
                            <option
                                value="" {{ !isset($filterParameters['employee_id']) ? 'selected' : '' }}>{{ __('index.select_employee') }}</option>
                        </select>
                    </div>
{{--                    <div class="col-lg-2 col-md-6 mb-4">--}}
{{--                        <select class="form-select form-select-lg" name="status" id="status">--}}
{{--                            <option--}}
{{--                                value="" {{ !isset($filterParameters['status']) ? 'selected' : '' }}>{{ __('index.all') }}</option>--}}
{{--                            @foreach (LoanStatusEnum::cases() as $case)--}}
{{--                                <option value="{{ $case->value }}"--}}
{{--                                    {{ (isset($filterParameters['status']) && $filterParameters['status'] == $case->value) ? 'selected' : '' }}>--}}
{{--                                    {{ ucfirst($case->name) }}--}}
{{--                                </option>--}}

{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}

                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-success me-2">{{ __('index.filter') }}</button>
                            <a href="{{ route('admin.loan.index') }}"
                               class="btn btn-block btn-primary">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card support-main">

            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs gap-1" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="all-loan-tab" data-bs-toggle="tab" href="#all-loan" role="tab"
                           aria-controls="all-loan" aria-selected="true">{{ __('index.loan_list') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="settled-loan-tab" data-bs-toggle="tab" href="#settled-loan" role="tab"
                           aria-controls="settled-loan" aria-selected="false">{{ __('index.settled_loan') }}</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- repair Tab -->
                    <div class="tab-pane fade show active mb-4" id="all-loan" role="tabpanel"
                         aria-labelledby="all-loan-tab">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>{{ __('index.loan_id') }}</th>
                                    <th>{{ __('index.employee') }}</th>
                                    <th>{{ __('index.loan_type') }}</th>
                                    <th>{{ __('index.department') }}</th>
                                    <th class="text-center">{{ __('index.loan_amount') }}</th>
                                    <th class="text-center">{{ __('index.application_date') }}</th>
                                    <th class="text-center">{{ __('index.repayment_date') }}</th>
                                    <th class="text-center">{{ __('index.status') }}</th>
                                    @canany(['show_loan', 'edit_loan', 'delete_loan'])
                                        <th class="text-center">{{ __('index.action') }}</th>
                                    @endcanany
                                </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $status = [
                                        LoanStatusEnum::pending->value => 'info',
                                        LoanStatusEnum::approve->value => 'success',
                                        LoanStatusEnum::reject->value => 'danger',
                                        LoanStatusEnum::settled->value => 'primary',
                                    ];
                                    ?>
                                @forelse($loanLists as $key => $value)
                                    <tr>
                                        <td class="text-center">{{ ++$key }}</td>
                                        <td>{{ ucfirst($value->loan_id) }}</td>
                                        <td>{{ ucfirst($value->employee->name ?? '') }}</td>
                                        <td>
                                            {{ ucfirst($value->loanType->name ?? '') }}
                                        </td>
                                        <td>{{ ucfirst($value->department->dept_name ?? '') }}</td>
                                        <td class="text-center">{{ isset($value->loan_amount) ? AppHelper::formatCurrencyAmount($value->loan_amount) : '' }}</td>
                                        <td class="text-center">{{ isset($value->application_date) ? AppHelper::formatDateForView($value->application_date) : '' }}</td>
                                        <td class="text-center">{{ isset($value->repayment_from) ? AppHelper::formatDateForView($value->repayment_from) : '' }}</td>
                                        <td class="text-center">

                                            @if($value->status === LoanStatusEnum::approve->value || $value->status === LoanStatusEnum::settled->value)
                                                <span
                                                    class="badge p-2 bg-{{ $status[$value->status] ?? 'secondary' }}">{{ ucfirst($value->status) }}</span>
                                            @else
                                                <span
                                                    class="btn btn-{{ $status[$value->status] ?? 'secondary' }} btn-xs"
                                                    id="updateStatus"
                                                    data-id="{{ $value->id }}"
                                                    data-status="{{ $value->status }}"
                                                    data-reason="{{ $value->remarks ?? '' }}"
                                                    data-action="{{ route('admin.loan.update-status', $value->id) }}">
                                                {{ ucfirst($value->status) }}
                                            </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                @can('show_loan')
                                                    <li class="me-2">
                                                        @if($value->status === LoanStatusEnum::settled->value)
                                                            <a href="{{ route('admin.loan.history', $value->id) }}"
                                                               title="Loan History">
                                                                <i class="link-icon" data-feather="eye"></i>
                                                            </a>
                                                        @else
                                                            <a class="showLoanDetail"
                                                               data-href="{{ route('admin.loan.show', $value->id) }}"
                                                               data-id="{{ $value->id }}"
                                                               title="{{ __('index.loan_detail') }}"
                                                               style="cursor: pointer;">
                                                                <i class="link-icon" data-feather="eye"></i>
                                                            </a>
                                                        @endif
                                                    </li>
                                                @endcan
                                                @if($value->status === LoanStatusEnum::pending->value)
                                                    @can('edit_loan')
                                                        <li class="me-2">
                                                            <a href="{{ route('admin.loan.edit', $value->id) }}"
                                                               title="{{ __('index.edit') }}">
                                                                <i class="link-icon" data-feather="edit"></i>
                                                            </a>
                                                        </li>
                                                    @endcan
                                                @endif
                                                @if( $value->status === LoanStatusEnum::pending->value || $value->status === LoanStatusEnum::reject->value)
                                                    @can('delete_loan')
                                                        <li>
                                                            <a class="delete"
                                                               data-title="{{ $value->loan_id }} Loan Detail"
                                                               data-href="{{ route('admin.loan.delete', $value->id) }}"
                                                               title="{{ __('index.delete') }}">
                                                                <i class="link-icon" data-feather="delete"></i>
                                                            </a>
                                                        </li>
                                                    @endcan
                                                @endif
                                            </ul>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- App settled-loan Tab -->
                    <div class="tab-pane fade mb-4" id="settled-loan" role="tabpanel"
                         aria-labelledby="settled-loan-tab">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>{{ __('index.loan_id') }}</th>
                                    <th>{{ __('index.employee') }}</th>
                                    <th>{{ __('index.loan_type') }}</th>
                                    <th class="text-center">{{ __('index.application_date') }}</th>
                                    <th class="text-center">{{ __('index.loan_amount') }}</th>
                                    <th class="text-center">{{ __('index.settlement_date') }}</th>
                                    <th class="text-center">{{ __('index.settlement_amount') }}</th>
                                    <th class="text-center">{{ __('index.status') }}</th>
                                    @canany(['show_loan', 'edit_loan', 'delete_loan'])
                                        <th class="text-center">{{ __('index.action') }}</th>
                                    @endcanany
                                </tr>
                                </thead>
                                <tbody>

                                @forelse($settledLoanLists as $key => $value)
                                    @php
                                        $totalSettlement = $value->loanRepayment->sum(function ($repayment) {
                                                return ($repayment->principal_amount ?? 0) + ($repayment->settlement_amount ?? 0) + ($repayment->interest_amount ?? 0);
                                            });

                                        $settlementDate = $value->loanRepayment->last()->paid_date;

                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ ++$key }}</td>
                                        <td>{{ ucfirst($value->loan_id) }}</td>
                                        <td>{{ ucfirst($value->employee->name ?? '') }}</td>
                                        <td>
                                            {{ ucfirst($value->loanType->name ?? '') }}
                                        </td>
                                        <td class="text-center">{{ isset($value->application_date) ? AppHelper::formatDateForView($value->application_date) : '' }}</td>
                                        <td class="text-center">{{ isset($value->loan_amount) ? AppHelper::formatCurrencyAmount($value->loan_amount) : '' }}</td>
                                        <td class="text-center">{{ isset($settlementDate) ? AppHelper::formatDateForView($settlementDate) : '' }}</td>
                                        <td class="text-center">{{ AppHelper::formatCurrencyAmount($totalSettlement) }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge p-2 bg-{{ $status[$value->status] ?? 'secondary' }}">{{ ucfirst($value->status) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">

                                                <li class="me-2">
                                                    <a href="{{ route('admin.loan.history', $value->id) }}"
                                                       title="Loan History">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>

                                            </ul>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    @include('admin.loanManagement.loan.common.status')
    @include('admin.loanManagement.loan.show')

@endsection

@section('scripts')
    @include('admin.loanManagement.loan.common.scripts')

    <script>
        $('body').on('click', '.showLoanDetail', function (event) {
            event.preventDefault();

            let url = $(this).data('href');
            let $modal = $('#loanDetail');

            $.get(url, function (data) {
                if (data.success) {
                    // Set modal title
                    $('.loanTitle').html(`Loan Details`);

                    // Basic Info
                    $('.loan_id').text(data.data.loan_id);
                    $('.loan_title').text(data.data.loan_title);
                    $('.loan_amount').text(data.data.loan_amount);
                    $('.monthly_installment').text(data.data.monthly_installment);
                    $('.repayment_amount').text(data.data.repayment_amount);
                    $('.status').text(data.data.status);
                    $('.interest_rate').text(data.data.interest_rate);
                    $('.interest_type').text(data.data.interest_type);
                    $('.next_interest_amount').text(data.data.next_interest_amount);
                    $('.payment_method').text(data.data.payment_method);

                    // Dates
                    $('.application_date').text(data.data.application_date);
                    $('.issue_date').text(data.data.issue_date);
                    $('.repayment_from').text(data.data.repayment_from);
                    $('.loan_due_at').text(data.data.loan_due_at);

                    // Employee & Purpose
                    $('.employee_name').text(data.data.employee_name);
                    $('.department_name').text(data.data.department_name);
                    $('.branch_name').text(data.data.branch_name);
                    $('.loan_purpose').text(data.data.loan_purpose);

                    // Description & Remarks
                    $('.description').text(data.data.description);
                    $('.remarks').text(data.data.remarks);

                    // Updated By
                    $('.updated_by').text(data.data.updated_by);

                    // Attachment
                    const $attachmentLink = $('.attachment a');
                    const $attachmentImg = $('.attachment img');
                    if (data.data.attachment) {
                        // Check if it's likely an image (simple extension check)
                        if (data.data.attachment.toLowerCase().match(/\.(jpg|jpeg|png|gif|bmp|webp)$/)) {
                            $attachmentImg.attr('src', data.data.attachment).removeClass('d-none');
                            $attachmentLink.hide();
                        } else {
                            $attachmentLink.attr('href', data.data.attachment).show();
                            $attachmentImg.addClass('d-none');
                        }
                    } else {
                        $attachmentLink.hide();
                        $attachmentImg.addClass('d-none');
                    }

                    // Show modal
                    $modal.modal('show');
                } else {
                    alert('Error loading loan details: ' + (data.message || 'Unknown error'));
                }
            }).fail(function () {
                alert('Failed to load loan details. Please try again.');
            });
        });
    </script>
@endsection
