@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', 'Attendance Log')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.attendance.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.log_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.attendance.log') }}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option selected disabled>{{ __('index.select_branch') }}</option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterData['branch_id']) && $filterData['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option selected disabled>{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option selected disabled>{{ __('index.select_employee') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 d-md-flex">
                        <button type="submit" class="btn btn-block btn-success me-md-2 me-0 mb-md-4 mb-2">{{ __('index.filter') }}</button>
                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4"
                           href="{{ route('admin.attendance.log') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_logs') }}</h6>
            </div>
            <div class="card-body">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="attendanceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual-logs" type="button" role="tab" aria-controls="manual-logs" aria-selected="true">{{ __('index.manual_logs') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="biometric-tab" data-bs-toggle="tab" data-bs-target="#biometric-logs" type="button" role="tab" aria-controls="biometric-logs" aria-selected="false">{{ __('index.biometric_logs') }}</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="attendanceTabsContent">
                    <!-- Manual Attendance Logs -->
                    <div class="tab-pane fade show active" id="manual-logs" role="tabpanel" aria-labelledby="manual-tab">
                        <div class="table-responsive">
                            <table id="manualDataTable" class="table">
                                <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>{{ __('index.employee_name') }}</th>
                                    <th class="text-center">{{ __('index.attendance_type') }}</th>
                                    <th class="text-center">{{ __('index.identifier') }}</th>
                                    <th class="text-center">{{ __('index.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($logData as $log)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $log->user?->name }}</td>
                                        <td class="text-center">{{ $log->attendance_type ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $log->identifier ?? 'N/A' }}</td>
                                        <td class="text-center">{{ \App\Helpers\AttendanceHelper::formattedAttendanceDateTime(\App\Helpers\AppHelper::ifDateInBsEnabled(), $log->updated_at) }}</td>
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
                        <div class="dataTables_paginate mt-3">
                            {{ $logData->appends($_GET)->links() }}
                        </div>
                    </div>

                    <!-- Biometric Attendance Logs -->
                    <div class="tab-pane fade" id="biometric-logs" role="tabpanel" aria-labelledby="biometric-tab">
                        <div class="table-responsive">
                            <table id="biometricDataTable" class="table">
                                <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>{{ __('index.employee_name') }}</th>
                                    <th>{{ __('index.device_serial_number') }}</th>
                                    <th class="text-center">{{ __('index.attendance_status') }}</th>
                                    <th class="text-center">{{ __('index.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($biometricLogData as $log)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $log->user?->name }}</td>
                                        <td class="text-center">{{ $log->sn ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $log->attendance_status == 0 ? 'CheckIn' : 'CheckOut' }}</td>
                                        <td class="text-center">{{ \App\Helpers\AttendanceHelper::formattedAttendanceDateTime(\App\Helpers\AppHelper::ifDateInBsEnabled(), $log->timestamp) }}</td>
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
                        <div class="dataTables_paginate mt-3">
                            {{ $biometricLogData->appends($_GET)->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Existing filter script
            $("#department_id").select2();
            $("#branch_id").select2();
            $("#employee_id").select2();

            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const branchId = "{{ $filterData['branch_id'] ?? null }}";
            const departmentId = "{{ $filterData['department_id'] ?? '' }}";
            const employeeId = "{{ $filterData['employee_id'] ?? '' }}";

            const loadDepartments = async (selectedBranchId) => {
                if (!selectedBranchId) return;
                try {
                    $('#department_id').empty().append('<option selected disabled>{{ __("index.select_department") }}</option>');
                    const response = await $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                    });
                    if (!response || !response.data || response.data.length === 0) {
                        $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                        return;
                    }
                    response.data.forEach(data => {
                        $('#department_id').append(`<option value="${data.id}" ${data.id == departmentId ? 'selected' : ''}>${data.dept_name}</option>`);
                    });
                } catch (error) {
                    $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                }
            };

            const loadEmployees = async () => {
                const selectedDepartmentId = $('#department_id').val();
                if (!selectedDepartmentId) return;
                try {
                    $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                    const response = await fetch(`{{ url('admin/employees/get-all-employees') }}/${selectedDepartmentId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    });
                    const data = await response.json();
                    if (data.data && data.data.length > 0) {
                        data.data.forEach(user => {
                            $('#employee_id').append(`<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''} >${user.name}</option>`);
                        });
                    } else {
                        $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    }
                } catch (error) {
                    $('#employee_id').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                }
            };

            const initializeDropdowns = async () => {
                let selectedBranchId;
                if (isAdmin) {
                    selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;
                    $('#branch_id').on('change', async () => {
                        const newBranchId = $('#branch_id').val();
                        await loadDepartments(newBranchId);
                        $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                        await loadEmployees();
                    });
                    if (selectedBranchId) {
                        $('#branch_id').trigger('change');
                    }
                } else {
                    selectedBranchId = defaultBranchId;
                    if (selectedBranchId) {
                        await loadDepartments(selectedBranchId);
                        await loadEmployees();
                    }
                }
                $('#department_id').on('change', loadEmployees);
                if (departmentId) {
                    $('#department_id').trigger('change');
                }
            };

            initializeDropdowns();
        });
    </script>
@endsection
