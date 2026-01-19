{{-- index blade --}}
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.loan_settlement_request'))

@section('action', __('index.lists'))

@section('button')
    @can('create_loans')
        <a href="{{ route('admin.request-settlement.create') }}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_loan_settlement_request') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.loanManagement.settlementRequest.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.loan_settlement_request_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.request-settlement.index') }}" method="get">
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
                        <select class="form-select form-select-lg" name="department_id" id="department">
                            <option value="" {{ !isset($filterParameters['department_id']) ? 'selected' : '' }}>{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="employee_id" id="employee">
                            <option value="" {{ !isset($filterParameters['employee_id']) ? 'selected' : '' }}>{{ __('index.select_employee') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="status" id="status">
                            <option value="" {{ !isset($filterParameters['status']) ? 'selected' : '' }}>{{ __('index.all') }}</option>
                            @foreach (\App\Enum\LoanStatusEnum::cases() as $case)
                                <option value="{{ $case->value }}"
                                    {{ (isset($filterParameters['status']) && $filterParameters['status'] == $case->value) ? 'selected' : '' }}>
                                    {{ ucfirst($case->name) }}
                                </option>

                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">{{ __('index.filter') }}</button>
                            <a href="{{ route('admin.request-settlement.index') }}" class="btn btn-block btn-primary">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.loan_settlement_request_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ __('index.loan_id') }}</th>
                            <th>{{ __('index.employee') }}</th>
                            <th>{{ __('index.amount') }}</th>
                            <th class="text-center">{{ __('index.settlement_type') }}</th>
                            <th class="text-center">{{ __('index.settlement_method') }}</th>
                            <th class="text-center">{{ __('index.settlement_date') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['show_loan', 'edit_loan', 'delete_loan'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            $status = [
                                \App\Enum\LoanStatusEnum::pending->value => 'info',
                                \App\Enum\LoanStatusEnum::approve->value => 'success',
                                \App\Enum\LoanStatusEnum::reject->value => 'danger',
                            ];
                            ?>
                        @forelse($requestLists as $key => $value)
                            <tr>
                                <td class="text-center">{{ ++$key }}</td>
                                <td>{{ $value->loan->loan_id }}</td>

                                <td>{{ ucfirst($value->employee->name ?? '') }}</td>
                                <td>{{ isset($value->amount) ? bcdiv($value->amount,1,2) : '' }}</td>
                                <td class="text-center">{{ ucfirst($value->settlement_type ?? '') }}</td>
                                <td class="text-center">{{ ucfirst($value->settlement_method ?? '') }}</td>
                                <td class="text-center">{{ AppHelper::formatDateForView($value->created_at)  }}</td>
                                <td class="text-center">

                                    @if($value->status == \App\Enum\LoanStatusEnum::approve->value)
                                        <span class="badge p-2 bg-{{ $status[$value->status] ?? 'secondary' }}">{{ ucfirst($value->status) }}</span>
                                    @else
                                        <span
                                            class="btn btn-{{ $status[$value->status] ?? 'secondary' }} btn-xs"
                                            id="updateStatus"
                                            data-id="{{ $value->id }}"
                                            data-status="{{ $value->status }}"
                                            data-reason="{{ $value->remarks ?? '' }}"
                                            data-action="{{ route('admin.request-settlement.update-status', $value->id) }}">
                                                {{ ucfirst($value->status) }}
                                            </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('show_loan')
                                            <li class="me-2">
                                                <a class="showLoanDetail"
                                                data-href="{{ route('admin.request-settlement.show', $value->id) }}"
                                                data-id="{{ $value->id }}"
                                                title="{{ __('index.loan_detail') }}"
                                                style="cursor: pointer;">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>

                                            </li>
                                        @endcan
                                        @if(\App\Enum\LoanStatusEnum::approve->value != $value->status)
                                            @can('edit_loan')
                                                <li class="me-2">
                                                    <a href="{{ route('admin.request-settlement.edit', $value->id) }}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan


                                            @can('delete_loan')
                                                <li>
                                                    <a class="delete" data-title="{{ $value->loan_id }} Loan Detail"
                                                       data-href="{{ route('admin.request-settlement.delete', $value->id) }}"
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
        </div>
    </section>

    @include('admin.loanManagement.settlementRequest.common.status')
    @include('admin.loanManagement.settlementRequest.show')


@endsection

@section('scripts')
    @include('admin.loanManagement.settlementRequest.common.scripts')

    <script>
        $('body').on('click', '.showLoanDetail', function (event) {
            event.preventDefault();

            let url = $(this).data('href');
            let $modal = $('#loanDetail');

            $.get(url, function (data) {
                if (data.success) {
                    // Set modal title
                    $('.loanTitle').html(`Loan Settlement Request Details`);

                    // Basic Info
                    $('.loan_id').text(data.data.loan_id);
                    $('.amount').text(data.data.amount);
                    $('.status').text(data.data.status);
                    $('.settlement_type').text(data.data.settlement_type);
                    $('.settlement_method').text(data.data.settlement_method);

                    // Dates
                    $('.requested_date').text(data.data.requested_date);

                    // Employee & Purpose
                    $('.employee_name').text(data.data.employee_name);
                    $('.department_name').text(data.data.department_name);
                    $('.branch_name').text(data.data.branch_name);

                    // Description & Remarks
                    $('.description').text(data.data.reason);
                    $('.remarks').text(data.data.remarks);

                    // Updated By
                    $('.updated_by').text(data.data.approved_by);

                    // Show modal
                    $modal.modal('show');
                } else {
                    alert('Error loading loan details: ' + (data.message || 'Unknown error'));
                }
            }).fail(function() {
                alert('Failed to load loan details. Please try again.');
            });
        });
    </script>
@endsection
