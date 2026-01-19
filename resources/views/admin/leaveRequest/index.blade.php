@php use App\Models\LeaveRequestMaster; @endphp
@php use App\Enum\LeaveStatusEnum; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.leave_requests'))

@section('action', __('index.lists'))

@section('button')
@endsection

@section('main-content')
    <?php
    if (AppHelper::ifDateInBsEnabled()) {
        $filterData['min_year'] = '2076';
        $filterData['max_year'] = '2089';
        $filterData['month'] = 'np';
    } else {
        $filterData['min_year'] = '2020';
        $filterData['max_year'] = '2033';
        $filterData['month'] = 'en';
    }
    ?>

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.leaveRequest.common.breadcrumb')

        {{-- Shared Filter Form --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.leave_request_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.leave-request.index') }}" method="get">
                <input type="hidden" name="type" id="form-type" value="{{ $activeTab }}">

                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-xxl col-xl-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option selected disabled>{{ __('index.select_branch') }}</option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option {{ $filterParameters['branch_id'] == $branch->id ? 'selected' : '' }} value="{{ $branch->id }}">{{ ucfirst($branch->name) }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <!-- Departments Field -->
                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="" {{ !isset($filterParameters['department_id']) ? 'selected' : '' }}>{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select" id="requestedBy" name="requested_by">
                            <option value="" {{ !isset($filterParameters['requested_by']) ? 'selected' : '' }}>{{ __('index.select_employee') }}</option>
                        </select>
                    </div>

                    <div id="leave-type-filter" class="col-xxl col-xl-3 col-md-6 mb-4" style="display: {{ $activeTab == 'time' ? 'none' : 'block' }};">
                        <select class="form-select form-select-lg" name="leave_type" id="leaveType">
                            <option value="" {{ !isset($filterParameters['leave_type']) ? 'selected' : '' }}>{{ __('index.all_leave_type') }}</option>
                        </select>
                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <input type="number" min="{{ $filterData['min_year'] }}"
                               max="{{ $filterData['max_year'] }}" step="1"
                               placeholder="{{ __('index.leave_requested_year') }} : {{ $filterData['min_year'] }}"
                               id="year"
                               name="year" value="{{ $filterParameters['year'] }}"
                               class="form-control">
                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="month" id="month">
                            <option value="" {{ !isset($filterParameters['month']) ? 'selected' : '' }}>{{ __('index.all_month') }}</option>
                            @foreach($months as $key => $value)
                                <option value="{{ $key }}" {{ (isset($filterParameters['month']) && $key == $filterParameters['month']) ? 'selected' : '' }}>
                                    {{ $value[$filterData['month']] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="status" id="status">
                            <option value="" {{ !isset($filterParameters['status']) ? 'selected' : '' }}>{{ __('index.all_status') }}</option>
                            @foreach(LeaveRequestMaster::STATUS as $value)
                                <option value="{{ $value }}" {{ (isset($filterParameters['status']) && $value == $filterParameters['status']) ? 'selected' : '' }}>{{ ucfirst($value) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xxl col-xl-3 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-secondary me-2">{{ __('index.filter') }}</button>
                            <a class="btn btn-block btn-primary reset" href="{{ route('admin.leave-request.index') }}">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabs and Button in one row --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ul class="nav nav-tabs flex-grow-1" id="leaveTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab == 'leave' ? 'active' : '' }}" id="leave-tab" data-bs-toggle="tab" href="#leave" role="tab" aria-controls="leave" aria-selected="{{ $activeTab == 'leave' ? 'true' : 'false' }}">Leave Requests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab == 'time' ? 'active' : '' }}" id="time-tab" data-bs-toggle="tab" href="#time" role="tab" aria-controls="time" aria-selected="{{ $activeTab == 'time' ? 'true' : 'false' }}">Time Leave Requests</a>
                </li>
            </ul>

            {{-- Add Button at right end --}}
            <div id="tab-buttons" class="ms-3">
                <div id="leave-button" style="display: {{ $activeTab == 'leave' ? 'block' : 'none' }};">
                    @canany(['create_leave_request','access_admin_leave'])
                        <a href="{{ route('admin.leave-request.add') }}">
                            <button class="btn btn-sm btn-primary">
                                <i class="link-icon" data-feather="plus"></i>{{ __('index.create_leave_request') }}
                            </button>
                        </a>
                    @endcanany
                </div>
                <div id="time-button" style="display: {{ $activeTab == 'time' ? 'block' : 'none' }};">
                    @can('create_time_leave_request')
                        <a href="{{ route('admin.time-leave-request.create') }}">
                            <button class="btn btn-sm btn-primary">
                                <i class="link-icon" data-feather="plus"></i>{{ __('index.create_time_leave_request') }}
                            </button>
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="tab-content" id="leaveTabsContent">
            {{-- Leave Tab --}}
            <div class="tab-pane fade {{ $activeTab == 'leave' ? 'show active' : '' }}" id="leave" role="tabpanel" aria-labelledby="leave-tab">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">@lang('index.leave_request_list')</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>{{ __('index.requested_by') }}</th>
                                    <th class="text-center">{{ __('index.type') }}</th>
                                    <th class="text-center">{{ __('index.leave_for') }}</th>
                                    <th class="text-center">{{ __('index.leave_date') }}</th>
                                    <th class="text-center">{{ __('index.requested_date') }}</th>
                                    <th class="text-center">{{ __('index.requested_days') }}</th>
                                    @canany(['show_leave_request_detail','access_admin_leave'])
                                        <th class="text-center">{{ __('index.reason') }}</th>
                                    @endcanany
                                    @canany(['update_leave_request','access_admin_leave'])
                                        <th class="text-center">{{ __('index.status') }}</th>
                                        <th class="text-center">{{ __('index.cancel_request') }}</th>
                                    @endcanany
                                </tr>
                                </thead>

                                    <?php
                                    $color = [
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'pending' => 'secondary',
                                        'cancelled' => 'danger'
                                    ];
                                    ?>
                                <tbody>
                                @forelse($leaveDetails as $key => $value)
                                    @php
                                        $leaveDate = is_null($value->leave_to) || strtotime($value->leave_from) == strtotime($value->leave_to)
                                            ? AppHelper::formatDateForView($value->leave_from)
                                            : AppHelper::formatDateForView($value->leave_from) . ' to ' . AppHelper::formatDateForView($value->leave_to);
                                    @endphp

                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A' }}</td>
                                        <td class="text-center">{{ $value->leaveType?->name ?? '' }}</td>
                                        <td class="text-center">{{ ucfirst(str_replace('_', ' ', $value->leave_for)) }}</td>
                                        <td class="text-center">
                                            {{ $leaveDate }}
                                            @if($value->leave_in ?? false)
                                                <strong>({{ ucfirst(str_replace('_', ' ', $value->leave_in)) }})</strong>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ AppHelper::formatDateForView($value->leave_requested_date) }}</td>
                                        <td class="text-center">{{ $value->no_of_days }}</td>

                                        @canany(['show_leave_request_detail', 'access_admin_leave'])
                                            <td class="text-center">
                                                <a href="#" class="showLeaveReason"
                                                   data-href="{{ route('admin.leave-request.show', $value->id) }}"
                                                   title="{{ __('index.show_leave_reason') }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>
                                            </td>
                                        @endcanany

                                        @canany(['update_leave_request', 'access_admin_leave'])
                                            <td class="text-center">
                                                <a href="" class="leaveRequestUpdate"
                                                   data-href="{{ route('admin.leave-request.update-status', $value->id) }}"
                                                   data-status="{{ $value->status }}"
                                                   data-remark="{{ $value->admin_remark ?? '' }}"
                                                   data-id="{{ $value->id }}">
                                                    <button class="btn btn-{{ ['approved'=>'success','rejected'=>'danger','pending'=>'secondary','cancelled'=>'danger'][$value->status] ?? 'secondary' }} btn-xs">
                                                        {{ ucfirst($value->status) }}
                                                    </button>
                                                </a>
                                            </td>

                                            @if($value->status === LeaveStatusEnum::approved->value && $value->cancel_request == 1)
                                                <td class="text-center">
                                                    <a href="" class="leaveCancelRequestUpdate"
                                                       data-href="{{ route('admin.leave-cancel.update-status', $value->id) }}"
                                                       data-reason="{{ $value->cancellation_reason }}"
                                                       data-id="{{ $value->id }}">
                                                        <i class="link-icon" data-feather="help-circle"></i>
                                                    </a>
                                                </td>
                                            @endif
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center"><b>{{ __('index.no_records_found') }}</b></td>
                                    </tr>
                                @endforelse
                                </tbody>


                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Time Leave Tab --}}
            <div class="tab-pane fade {{ $activeTab == 'time' ? 'show active' : '' }}" id="time" role="tabpanel" aria-labelledby="time-tab">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">@lang('index.time_leave_list')</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTableExampleTime" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.requested_by') }}</th>
                                    <th class="text-center">{{ __('index.leave_date') }}</th>
                                    <th class="text-center">{{ __('index.start_time') }}</th>
                                    <th class="text-center">{{ __('index.end_time') }}</th>

                                    @can('time_leave_list')
                                        <th class="text-center">{{ __('index.reason') }}</th>
                                    @endcan
                                    @can('update_time_leave')
                                        <th class="text-center">{{ __('index.status') }}</th>
                                        <th class="text-center">{{ __('index.cancel_request') }}</th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $color = [
                                        LeaveStatusEnum::approved->value => 'success',
                                        LeaveStatusEnum::rejected->value => 'danger',
                                        LeaveStatusEnum::pending->value => 'secondary',
                                        LeaveStatusEnum::cancelled->value => 'danger'
                                    ];
                                    ?>
                                @forelse($timeLeaves as $key => $value)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A' }}</td>
                                        <td class="text-center">{{ AppHelper::formatDateForView($value->issue_date) }}</td>
                                        <td class="text-center">{{ AppHelper::convertLeaveTimeFormat($value->start_time) }}</td>
                                        <td class="text-center">{{ AppHelper::convertLeaveTimeFormat($value->end_time) }}</td>


                                        @can('time_leave_list')
                                            <td class="text-center">
                                                <a href="#" class="showTimeLeaveReason"
                                                   data-href="{{ route('admin.time-leave-request.show', $value->id) }}"
                                                   title="{{ __('index.show_leave_reason') }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>
                                            </td>
                                        @endcan

                                        @can('update_time_leave')
                                            <td class="text-center">
                                                <a href=""
                                                   class="leaveRequestUpdateTime"
                                                   data-href="{{ route('admin.time-leave-request.update-status', $value->id) }}"
                                                   data-status="{{ $value->status }}"
                                                   data-remark="{{ $value->admin_remark }}">
                                                    <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                                        {{ ucfirst($value->status) }}
                                                    </button>
                                                </a>
                                            </td>
                                        @endcan
                                        @can('update_time_leave')
                                            @if($value->cancel_request ==  1)
                                                <td class="text-center">
                                                    <a href=""
                                                       class="leaveCancelRequestUpdate"
                                                       data-href="{{ route('admin.time-leave-cancel.update-status', $value->id) }}"
                                                       data-reason="{!! $value->cancellation_reason !!}"
                                                       data-id="{{ $value->id }}">
                                                        <i class="link-icon" data-feather="help-circle"></i>
                                                    </a>
                                                </td>
                                            @endif
                                        @endcan
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

    {{-- Pagination --}}
    @if($activeTab == 'leave')
        <div class="dataTables_paginate mt-3">
            {{ $leaveDetails->appends(request()->query())->links() }}
        </div>
    @elseif($activeTab == 'time')
        <div class="dataTables_paginate mt-3">
            {{ $timeLeaves->appends(request()->query())->links() }}
        </div>
    @endif

    {{-- Leave Request Show Modal --}}
    @include('admin.leaveRequest.common.show_remarks')

    {{-- Time Leave Show Modal --}}
    @include('admin.leaveRequest.common.time_leave_show')

    {{-- Leave Request Approval Modal --}}
    @include('admin.leaveRequest.common.approval-info-model')

    {{-- Leave cancel Request Approval Modal --}}
    @include('admin.leaveRequest.common.cancel-request')

    {{-- Status Update Modal (Shared for both, title dynamic) --}}
    @include('admin.leaveRequest.common.status')
@endsection


@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Leave Reason Modal Script
            document.querySelectorAll('.showLeaveReason').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();
                    const url = this.getAttribute('data-href');

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.data) {
                                const leaveRequest = data.data;
                                document.getElementById('referredBy').innerText = leaveRequest.name || 'Admin';
                                document.getElementById('description').innerText = leaveRequest.reasons || 'N/A';
                                document.getElementById('adminRemark').innerText = leaveRequest.admin_remark || 'N/A';

                                const modalElement = document.getElementById('leaveShowModal');
                                if (modalElement) {
                                    const modal = new bootstrap.Modal(modalElement);
                                    modal.show();
                                } else {
                                    console.error('Modal element not found');
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });

            // Time Leave Reason Modal Script
            document.querySelectorAll('.showTimeLeaveReason').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();
                    const url = this.getAttribute('data-href');

                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data && data.data) {
                                const leaveRequest = data.data;
                                document.getElementById('timeLeaveReferral').innerText = leaveRequest.name || 'Admin';
                                document.getElementById('timeLeaveReason').innerText = leaveRequest.reasons || 'N/A';
                                document.getElementById('timeLeaveAdminRemark').innerText = leaveRequest.admin_remark || 'N/A';

                                const modal = new bootstrap.Modal(document.getElementById('timeShowModal'));
                                modal.show();
                            } else {
                                console.error('Data format is incorrect or data is missing:', data);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });

        $(document).ready(function () {
            // Initial setup for time tab
            @if($activeTab == 'time')
            $('#leave-type-filter').hide();
            $('#leaveType').val('');
            $('#leave-button').hide();
            $('#time-button').show();
            @endif

            // Tab switch handler
            $('#leaveTabs a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr('href');
                var activeTab = target.substring(1); // 'leave' or 'time'

                $('#form-type').val(activeTab);

                if (activeTab === 'time') {
                    $('#leave-type-filter').hide();
                    $('#leaveType').val('');
                    $('#leave-button').hide();
                    $('#time-button').show();
                    // Re-init dropdowns for time tab if needed
                    initializeDropdowns();
                } else {
                    $('#leave-type-filter').show();
                    $('#leave-button').show();
                    $('#time-button').hide();
                    // Re-init dropdowns for leave tab
                    initializeDropdowns();
                }
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Leave Update Handler
            $('body').on('click', '.leaveRequestUpdate', function (event) {
                event.preventDefault();
                let url = $(this).data('href');
                let status = $(this).data('status');
                let remark = $(this).data('remark');
                let leaveRequestId = $(this).data('id');

                $('.modal-title').html('Leave Status Update');
                $('#statusUpdateLabel').text('Leave Status Update');
                $('#updateLeaveStatus').attr('action',url)
                $('#status').val(status)
                $('#remark').val(remark)

                $('#previousApprovers').html('');
                $.ajax({
                    url: `/admin/leave-request/get-approvers/${leaveRequestId}`,
                    method: 'GET',
                    success: function (response) {
                        console.log(response.data.admin_data)
                        if (response.success) {
                            let approversData = '';

                            response.data.approval_data.forEach(function (approver) {
                                approversData += `
                        <div class="approver-details">
                            <p><b>Approver:</b> ${approver.approved_by_name}</p>
                            <p><b>Status:</b> ${approver.status}</p>
                            <p><b>Remark:</b> ${approver.reason}</p>
                        </div>
                        <hr>`;
                            });

                            if(response.data.admin_data.status !== 'pending' && response.data.admin_data.remark !== ''){
                                approversData += `
                                <div class="approver-details">
                                    <p><b>Status:</b>  ${response.data.admin_data.status}</p>
                                    <p><b>Admin Remark:</b> ${ response.data.admin_data.remark}</p>`;
                                if(response.data.admin_data.message !== ''){
                                    approversData += ` <p>(${ response.data.admin_data.message})</p>`;
                                }

                                approversData += ` </div>`;
                            }
                            $('#previousApprovers').html(approversData);
                        }
                    }
                });
                $('#statusUpdate').modal('show');
            });

            // Time Leave Update Handler
            $('body').on('click', '.leaveRequestUpdateTime', function (event) {
                event.preventDefault();
                let url = $(this).data('href');
                let status = $(this).data('status');
                let remark = $(this).data('remark');
                $('.modal-title').html(`{{__('index.time_leave_status_update')}}`);
                $('#statusUpdateLabel').text('{{__("index.time_leave_status_update")}}');
                $('#updateLeaveStatus').attr('action', url)
                $('#status').val(status)
                $('#remark').val(remark)
                $('#previousApprovers').html(''); // Clear previous approvers for time leave
                $('#statusUpdate').modal('show');
            });

            // Approval Info Handler (Leave only)
            $('body').on('click','.show-approval-info', function() {
                let leaveRequestId = $(this).data('id');
                $('#approversList').html('');
                $.ajax({
                    url: `/admin/leave-request/get-approvers/${leaveRequestId}`,
                    method: 'GET',
                    success: function (response) {
                        console.log(response.data);
                        if (response.success) {
                            let approversData = '';
                            response.data.approval_data.forEach(function (approver) {
                                approversData += `
                                    <div class="approver-details">
                                        <p><b>Approver:</b> ${approver.approved_by_name}</p>
                                        <p><b>Status:</b> ${approver.status}</p>
                                        <p><b>Remark:</b> ${approver.reason}</p>
                                    </div>
                                    <hr>`;
                            });

                            if(response.data.admin_data.status !== 'pending' && response.data.admin_data.remark !== ''){
                                approversData += `
                                <div class="approver-details">
                                    <p><b>Status:</b>  ${response.data.admin_data.status}</p>
                                    <p><b>Admin Remark:</b> ${ response.data.admin_data.remark}</p>`;
                                if(response.data.admin_data.message !== ''){
                                    approversData += `<p>${ response.data.admin_data.message}</p>`;
                                }

                                approversData += `</div>`;
                            }
                            $('#approversList').html(approversData);
                        }
                    }
                });
                $('#approvalInfoModal').modal('show');
            });

            // Cancel Request Handler (Shared for both leave and time leave)
            $('body').on('click', '.leaveCancelRequestUpdate', function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                let reason = $(this).data('reason'); // Note: Matches the data attribute typo; adjust if corrected to 'reason'
                let id = $(this).data('id');

                // Display the cancellation reason above the form (assuming a div with id 'cancelReasonDisplay' exists in the modal)
                $('#cancelReasonDisplay').html(`<div class="alert alert-info"><strong>Cancellation Reason:</strong><br>${reason || 'N/A'}</div>`);

                // Set form action to the update-status route
                $('#cancelRequestForm').attr('action', href);

                // Show the cancel-request modal (assuming modal id is 'cancelRequestModal' from the
                $('#cancelRequestModal').modal('show');
            });

            // Select2 Initialization
            $("#department_id").select2({});
            $("#branch_id").select2({});
            $("#requestedBy").select2({});
            if ($('#leaveType').length) {
                $("#leaveType").select2({});
            }

            const departmentId = "{{ $filterParameters['department_id'] ?? '' }}";
            const employeeId = "{{ $filterParameters['requested_by'] ?? '' }}";
            const leaveTypeId = "{{ $filterParameters['leave_type'] ?? '' }}";
            const branchId = "{{ $filterParameters['branch_id'] ?? '' }}";
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};

            const loadDepartments = async (selectedBranchId) => {
                if (!selectedBranchId) return;

                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                    });

                    // FIX: Destroy Select2 before modifying
                    $('#department_id').select2('destroy');

                    // Clear existing options
                    $('#department_id').empty();

                    $('#department_id').append('<option selected disabled>{{ __("index.select_department") }}</option>');
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(department => {
                            $('#department_id').append(
                                `<option value="${department.id}" ${parseInt(department.id) == parseInt(departmentId) ? 'selected' : ''}>${department.dept_name}</option>`
                            );
                        });
                        console.log('Departments loaded:', response.data); // DEBUG: Check loaded data
                    } else {
                        $('#department_id').append('<option disabled>{{ __("index.no_department_found") }}</option>');
                    }

                    // FIX: Re-init Select2
                    $("#department_id").select2({});

                    // FIX: Explicitly set selected value if pre-filtered
                    if (departmentId && departmentId !== '') {
                        const isValid = $('#department_id option[value="' + departmentId + '"]').length > 0;
                        console.log('Setting department:', departmentId, 'Valid?', isValid); // DEBUG: Verify option exists
                        if (isValid) {
                            $('#department_id').val(departmentId).trigger('change');
                        } else {
                            console.warn('Pre-selected department ID not found in loaded options:', departmentId);
                        }
                    }

                    loadEmployees();

                } catch (error) {
                    console.error('Error loading departments:', error);
                    // FIX: Re-init with error state
                    $('#department_id').select2('destroy').empty().append('<option disabled>{{ __("index.error_loading_department") }}</option>').select2({});
                }
            };

            const loadEmployees = async () => {
                const selectedDepartmentId = $('#department_id').val();
                if (!selectedDepartmentId) return;
                try {
                    // FIX: Destroy Select2 before modifying
                    $('#requestedBy').select2('destroy');

                    $('#requestedBy').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');

                    const response = await fetch(`{{ url('admin/employees/get-all-employees') }}/${selectedDepartmentId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    });

                    const data = await response.json();
                    $('#requestedBy').empty();
                    $('#requestedBy').append('<option selected disabled>{{ __("index.select_employee") }}</option>');

                    if (data.data && data.data.length > 0) {
                        data.data.forEach(user => {
                            $('#requestedBy').append(
                                `<option value="${user.id}" ${parseInt(user.id) === parseInt(employeeId) ? 'selected' : ''}>${user.name}</option>`
                            );
                        });
                        console.log('Employees loaded:', data.data); // DEBUG: Check loaded data
                    } else {
                        $('#requestedBy').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    }

                    // FIX: Re-init Select2
                    $("#requestedBy").select2({});

                    // FIX: Explicitly set selected value if pre-filtered
                    if (employeeId && employeeId !== '') {
                        const isValid = $('#requestedBy option[value="' + employeeId + '"]').length > 0;
                        console.log('Setting employee:', employeeId, 'Valid?', isValid); // DEBUG: Verify option exists
                        if (isValid) {
                            $('#requestedBy').val(employeeId).trigger('change');
                        } else {
                            console.warn('Pre-selected employee ID not found in loaded options:', employeeId);
                        }
                    }

                    // Trigger change for leave types if on leave tab
                    if ($('#leaveType').length && $('#leaveType').is(':visible')) {
                        loadLeaveTypes();
                    }

                } catch (error) {
                    console.error('Error loading employees:', error);
                    // FIX: Re-init with error state
                    $('#requestedBy').select2('destroy').empty().append('<option disabled>{{ __("index.error_loading_employees") }}</option>').select2({});
                }
            };

            const loadLeaveTypes = async () => {
                const selectedEmployee = $('#requestedBy').val();
                if (!selectedEmployee || !$('#leaveType').length) return;
                try {
                    // FIX: Destroy Select2 before modifying
                    $('#leaveType').select2('destroy');

                    $('#leaveType').empty().append('<option value="" selected disabled>{{ __("index.select_leave_type") }}</option>'); // Note: value="" to match all_leaves option

                    const response = await fetch(`{{ url('admin/leaves/get-employee-leave-types') }}/${selectedEmployee}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    });

                    const data = await response.json();
                    $('#leaveType').empty();
                    $('#leaveType').append('<option value="" selected disabled>{{ __("index.all_leave_type") }}</option>');

                    if (data.leveTypes && data.leveTypes.length > 0) {
                        data.leveTypes.forEach(type => {
                            $('#leaveType').append(
                                `<option value="${type.id}" ${parseInt(type.id) === parseInt(leaveTypeId) ? 'selected' : ''}>${type.name}</option>`
                            );
                        });
                        console.log('Leave types loaded:', data.leveTypes);
                    } else {
                        $('#leaveType').append('<option disabled>{{ __("index.leave_type_not_found") }}</option>');
                    }

                    // FIX: Re-init Select2 (only if visible/on leave tab)
                    if ($('#leaveType').is(':visible')) {
                        $("#leaveType").select2({});
                    }

                    // FIX: Explicitly set selected value if pre-filtered
                    if (leaveTypeId && leaveTypeId !== '') {
                        const isValid = $(`#leaveType option[value="${leaveTypeId}"]`).length > 0;
                        console.log('Setting leave type:', leaveTypeId, 'Valid?', isValid); // DEBUG: Verify option exists
                        if (isValid) {
                            $('#leaveType').val(leaveTypeId).trigger('change');
                        } else {
                            console.warn('Pre-selected leave type ID not found in loaded options:', leaveTypeId);
                        }
                    }

                } catch (error) {
                    console.error('Error loading leave types:', error);
                    // FIX: Re-init with error state
                    $('#leaveType').select2('destroy').empty().append('<option disabled>{{ __("index.error_loading_leave_types") }}</option>').select2({});
                }
            };

            const initializeDropdowns = async () => {
                let selectedBranchId;

                if (isAdmin) {
                    selectedBranchId = $('#branch_id').val() || branchId;
                    $('#branch_id').change(async () => {
                        // FIX: When branch changes, clear downstream dropdowns properly
                        $('#department_id').select2('destroy').empty().append('<option selected disabled>{{ __("index.select_department") }}</option>').select2({}).val('').trigger('change');
                        $('#requestedBy').select2('destroy').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>').select2({}).val('').trigger('change');
                        if ($('#leaveType').length) {
                            $('#leaveType').select2('destroy').empty().append('<option value="" selected disabled>{{ __("index.all_leave_type") }}</option>').select2({}).val('').trigger('change');
                        }
                        await loadDepartments($('#branch_id').val());
                    });
                } else {
                    selectedBranchId = defaultBranchId;
                }

                if (selectedBranchId) {
                    await loadDepartments(selectedBranchId);
                }

                // Bind employee change for leave types if on leave tab
                $('#requestedBy').off('change').on('change', loadLeaveTypes);
                $('#department_id').off('change').on('change', loadEmployees);
            };

            // Call initialization
            initializeDropdowns();

            // Initial load if pre-selected (this is redundant if loadDepartments already calls loadEmployees, but safe)
            if (departmentId) {
                loadEmployees();
            }
        });
    </script>
@endsection

