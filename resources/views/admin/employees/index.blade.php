@php use App\Models\User; @endphp
@extends('layouts.master')

@section('title', __('index.employees_title'))

@section('action', __('index.employees_action'))

@section('button')
    @can('create_employee')
        <div class="float-md-end d-flex align-items-center gap-2 justify-content-center">
            <a href="{{ route('admin.employees.create')}}">
                <button class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="link-icon" data-feather="plus"></i>{{ __('index.add_employee') }}
                </button>
            </a>
        </div>
    @endcan
@endsection

@section('main-content')
<style>
    /* Prevent table headers from wrapping to multiple lines */
    #dataTableExample thead th {
        white-space: nowrap;       /* Prevents text from wrapping */
        vertical-align: middle;     /* Centers text vertically */
        font-size: 0.85rem;        /* Slightly smaller font for better fit */
        padding: 0.5rem 0.4rem;    /* Compact padding */
    }

    /* Make table scrollable horizontally */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Prevent table body cells from wrapping too */
    #dataTableExample tbody td {
        white-space: nowrap;
        font-size: 0.85rem;
    }
</style>

<section class="content">
    @include('admin.section.flash_message')
    @include('admin.employees.common.breadcrumb')

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">{{ __('index.employee_lists') }}</h6>
        </div>
        <form class="forms-sample card-body pb-0" action="{{ route('admin.employees.index') }}" id="employeeFilterForm" method="get">
            <div class="row align-items-center">

                {{-- 3. EMPLOYEE ID --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Employee ID</label>
                    <input type="text" placeholder="Employee ID" name="employee_code"
                           value="{{ $filterParameters['employee_code'] ?? '' }}" class="form-control">
                </div>

                {{-- 4. SURNAME --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Surname</label>
                    <input type="text" placeholder="Surname" name="surname"
                           value="{{ $filterParameters['surname'] ?? '' }}" class="form-control">
                </div>

                {{-- 5. FIRST NAME --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">First Name</label>
                    <input type="text" placeholder="First Name" name="first_name"
                           value="{{ $filterParameters['first_name'] ?? '' }}" class="form-control">
                </div>

                {{-- 6. MIDDLE NAME --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Middle Name</label>
                    <input type="text" placeholder="Middle Name" name="middle_name"
                           value="{{ $filterParameters['middle_name'] ?? '' }}" class="form-control">
                </div>

                {{-- 7. NIN --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">NIN</label>
                    <input type="text" placeholder="NIN" name="nin"
                           value="{{ $filterParameters['nin'] ?? '' }}" class="form-control">
                </div>

                {{-- 8. BVN --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">BVN</label>
                    <input type="text" placeholder="BVN" name="bvn"
                           value="{{ $filterParameters['bvn'] ?? '' }}" class="form-control">
                </div>

                {{-- 9. DATE OF BIRTH --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Date of Birth</label>
                    <input type="date" name="dob"
                           value="{{ $filterParameters['dob'] ?? '' }}" class="form-control">
                </div>

                {{-- 10. PHONE --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Phone No.</label>
                    <input type="text" placeholder="Phone number" name="phone"
                           value="{{ $filterParameters['phone'] ?? '' }}" class="form-control">
                </div>

                {{-- 11. EMAIL --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Email</label>
                    <input type="text" placeholder="Email" name="email"
                           value="{{ $filterParameters['email'] ?? '' }}" class="form-control">
                </div>

                {{-- 12. EMPLOYMENT DATE --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Employment Date</label>
                    <input type="date" name="joining_date"
                           value="{{ $filterParameters['joining_date'] ?? '' }}" class="form-control">
                </div>

                {{-- 13. EMPLOYMENT TYPE --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Employment Type</label>
                    <select class="form-control" name="employment_type">
                        <option value="">All Types</option>
                        @foreach(User::EMPLOYMENT_TYPE as $type)
                            <option value="{{ $type }}"
                                {{ ($filterParameters['employment_type'] ?? '') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 14. DESIGNATION (post) --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Designation</label>
                    <select class="form-control" id="post" name="post_id">
                        <option value="">All Designations</option>
                    </select>
                </div>

                {{-- 15. SUPERVISOR --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Supervisor</label>
                    <select class="form-control" id="supervisor" name="supervisor_id">
                        <option value="">All Supervisors</option>
                    </select>
                </div>

                {{-- 1. BRANCH/COMPANY (first) --}}
                @if(!isset(auth()->user()->branch_id))
                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <label class="form-label text-muted small mb-1">Branch/Company</label>
                        <select class="form-control" id="branch" name="branch_id">
                            <option value="">{{ __('index.select_branch') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ ($filterParameters['branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- 2. DEPARTMENT (second) --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Department</label>
                    <select class="form-control" id="department" name="department_id">
                        <option value="">{{ __('index.select_department') }}</option>
                    </select>
                </div>

                {{-- 16. SHIFT --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Shift</label>
                    <select class="form-control" id="office_time" name="office_time_id">
                        <option value="">All Shifts</option>
                    </select>
                </div>

                {{-- 17. GRADE LEVEL --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Grade Level</label>
                    <input type="text" placeholder="Grade Level" name="grade_level"
                           value="{{ $filterParameters['grade_level'] ?? '' }}" class="form-control">
                </div>

                {{-- 18. TAX ID --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">Tax ID</label>
                    <input type="text" placeholder="Tax ID" name="tax_id"
                           value="{{ $filterParameters['tax_id'] ?? '' }}" class="form-control">
                </div>

                {{-- 19. SBU CODE --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">SBU Code</label>
                    <input type="text" placeholder="SBU Code" name="sbu_code"
                           value="{{ $filterParameters['sbu_code'] ?? '' }}" class="form-control">
                </div>

                {{-- 20. RSA NO --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">RSA No</label>
                    <input type="text" placeholder="RSA No" name="rsa_no"
                           value="{{ $filterParameters['rsa_no'] ?? '' }}" class="form-control">
                </div>

                {{-- 21. HMO ID --}}
                <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                    <label class="form-label text-muted small mb-1">HMO ID</label>
                    <input type="text" placeholder="HMO ID" name="hmo_id"
                           value="{{ $filterParameters['hmo_id'] ?? '' }}" class="form-control">
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="col-12 mb-4">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="submit" name="action" value="filter" class="btn btn-success">
                            {{ __('index.filter') }}
                        </button>
                        @can('create_employee')
                            <button type="button" id="export_employee"
                                    data-href="{{ route('admin.employees.index') }}"
                                    class="btn btn-secondary">
                                {{ __('index.export_csv') }}
                            </button>
                        @endcan
                        <a class="btn btn-primary" href="{{ route('admin.employees.index') }}">
                            {{ __('index.reset') }}
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- EMPLOYEE TABLE --}}
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">{{ __('index.employee_lists') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTableExample" class="table table-sm">
                    <thead>
                        <tr>
                            @can('show_detail_employee')
                                <th>#</th>
                            @endcan
                            <th>EMPLOYEE ID</th>
                            <th>SURNAME</th>
                            <th>FIRST NAME</th>
                            <th>MIDDLE NAME</th>
                            <th>NIN</th>
                            <th>BVN</th>
                            <th>DATE OF BIRTH</th>
                            <th>PHONE NO.</th>
                            <th>EMAIL</th>
                            <th>EMPLOYMENT DATE</th>
                            <th>EMPLOYMENT TYPE</th>
                            <th>SUPERVISOR</th>
                            <th>BRANCH / COMPANY</th>
                            <th>DEPARTMENT</th>
                            <th>DESIGNATION</th>
                            <th>GRADE LEVEL</th>
                            <th>TAX ID</th>
                            <th>SBU CODE</th>
                            <th>RSA NO</th>
                            <th>HMO ID</th>
                            <th>SHIFT</th>
                            <th>BANK NAME</th>
                            <th>ACCOUNT NO.</th>
                            <th>ACCOUNT TYPE</th>
                            <th>ACCOUNT HOLDER</th>
                            <th>HOLIDAY ATTENDANCE</th>
                            <th>WORKPLACE</th>
                            <th>IS ACTIVE</th>
                            @canany(['edit_employee','delete_employee','change_password','force_logout'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        <?php $changeColor = [0 => 'success', 1 => 'primary']; ?>
                        @forelse($users as $value)
                            <tr>
                                @can('show_detail_employee')
                                    <td>
                                        <a href="{{ route('admin.employees.show', $value->id) }}">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                @endcan
                                <td>{{ $value->employee_code ?? 'N/A' }}</td>
                                <td>{{ $value->surname ?? 'N/A' }}</td>
                                <td>{{ $value->first_name ?? 'N/A' }}</td>
                                <td>{{ $value->middle_name ?? 'N/A' }}</td>
                                <td>{{ $value->nin ?? 'N/A' }}</td>
                                <td>{{ $value->accountDetail->bvn ?? 'N/A' }}</td>
                                <td>{{ $value->dob ? \Carbon\Carbon::parse($value->dob)->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $value->phone ?? 'N/A' }}</td>
                                <td>{{ $value->email ?? 'N/A' }}</td>
                                <td>{{ $value->joining_date ? \Carbon\Carbon::parse($value->joining_date)->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $value->employment_type ? ucfirst($value->employment_type) : 'N/A' }}</td>

                                <td>{{ $value->supervisor ? ucfirst($value->supervisor->name) : 'N/A' }}</td>
                                <td>{{ $value->branch ? ucfirst($value->branch->name) : ($value->company ? ucfirst($value->company->name) : 'N/A') }}</td>
                                <td>{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                <td>{{ $value->post ? ucfirst($value->post->post_name) : 'N/A' }}</td>
                                <td>{{ $value->grade_level ?? 'N/A' }}</td>
                                <td>{{ $value->tax_id ?? 'N/A' }}</td>
                                <td>{{ $value->sbu_code ?? 'N/A' }}</td>
                                <td>{{ $value->rsa_no ?? 'N/A' }}</td>
                                <td>{{ $value->hmo_id ?? 'N/A' }}</td>
                                <!--<td>{{ $value->officeTime ? ucfirst($value->officeTime->shift) : 'N/A' }}</td>-->
                                <td>{{ $value->officeTime ? $value->officeTime->opening_time . ' - ' . $value->officeTime->closing_time : 'N/A' }}</td>
                                <td>{{ $value->accountDetail->bank_name ?? 'N/A' }}</td>
                                <td>{{ $value->accountDetail->bank_account_no ?? 'N/A' }}</td>
                                <td>{{ $value->accountDetail->bank_account_type ? ucfirst($value->accountDetail->bank_account_type) : 'N/A' }}</td>
                                <td>{{ $value->accountDetail->account_holder ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleHolidayCheckIn"
                                               href="{{ route('admin.employees.toggle-holiday-checkin', $value->id) }}"
                                               type="checkbox" {{ $value->allow_holiday_check_in == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <a class="changeWorkPlace btn btn-{{ $changeColor[$value->workspace_type] }} btn-xs"
                                       data-href="{{ route('admin.employees.change-workspace', $value->id) }}"
                                       title="Change workspace">
                                        {{ $value->workspace_type == User::FIELD ? 'Field' : 'Office' }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="{{ route('admin.employees.toggle-status', $value->id) }}"
                                               type="checkbox" {{ $value->is_active == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                @canany(['edit_employee','delete_employee','change_password','force_logout'])
                                    <td class="text-center">
                                        <a class="nav-link dropdown-toggle" href="#" role="button"
                                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        </a>
                                        <div class="dropdown-menu p-0">
                                            <ul class="list-unstyled p-1 mb-0">
                                                @can('edit_employee')
                                                    <li class="dropdown-item py-2">
                                                        <a href="{{ route('admin.employees.edit', $value->id) }}">
                                                            <button class="btn btn-primary btn-xs">{{ __('index.edit_detail') }}</button>
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('delete_employee')
                                                        @php $authId = auth('admin')->id() ?? auth()->id(); @endphp
                                                        @if($value->id != $authId && $value->id != 1)                                                        <li class="dropdown-item py-2">
                                                            <a class="deleteEmployee"
                                                               data-href="{{ route('admin.employees.delete', $value->id) }}">
                                                                <button class="btn btn-primary btn-xs">{{ __('index.delete_user') }}</button>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endcan
                                                @can('change_password')
                                                    <li class="dropdown-item py-2">
                                                        <a class="changePassword"
                                                           data-href="{{ route('admin.employees.change-password', $value->id) }}">
                                                            <button class="btn btn-primary btn-xs">{{ __('index.change_password') }}</button>
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('force_logout')
                                                    <li class="dropdown-item py-2">
                                                        <a class="forceLogOut"
                                                           data-href="{{ route('admin.employees.force-logout', $value->id) }}">
                                                            <button class="btn btn-primary btn-xs">{{ __('index.force_logout') }}</button>
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('view_card_pdf')
                                                    <li class="dropdown-item py-2">
                                                        <a href="{{ route('employee.card.view', $value->employee_code) }}" target="_blank">
                                                            <button class="btn btn-primary btn-xs">ID Card</button>
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="31">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dataTables_paginate mt-3">
        {{ $users->appends($_GET)->links() }}
    </div>

</section>

@include('admin.employees.common.password')
@endsection

@section('scripts')
    @include('admin.employees.common.scripts')

    <script>
    // Branch → load Departments + Supervisors + Shifts
    $('#branch').on('change', function () {
        const branchId = $(this).val();
        $('#department').html('<option value="">{{ __('index.select_department') }}</option>');
        $('#post').html('<option value="">All Designations</option>');
        $('#supervisor').html('<option value="">All Supervisors</option>');
        $('#office_time').html('<option value="">All Shifts</option>');

        if (!branchId) return;

        // Load departments
        $.ajax({
            url: '{{ url('admin/departments/get-All-Departments') }}/' + branchId,
            success: function (data) {
                $.each(data.departments ?? data, function (i, dept) {
                    $('#department').append('<option value="' + dept.id + '">' + dept.dept_name + '</option>');
                });
            }
        });

        // Load supervisors
        $.ajax({
            url: '{{ url('admin/employees/get-branch-employee') }}/' + branchId,
            success: function (data) {
                $.each(data.employee ?? [], function (i, emp) {
                    $('#supervisor').append('<option value="' + emp.id + '">' + emp.name + '</option>');
                });
            }
        });

        // Load office times (shifts) - FIXED: Check for duplicates
        $.ajax({
            type: 'GET',
            url: '{{ url('admin/transfer/get-user-transfer-branch-data') }}/' + branchId,
            success: function (response) {
                // Clear existing options first
                $('#office_time').html('<option value="">All Shifts</option>');

                if (response.officeTimes && response.officeTimes.length > 0) {
                    // Use a Set to track unique shift IDs to prevent duplicates
                    const addedShifts = new Set();

                    $.each(response.officeTimes, function (i, shift) {
                        // Only add if we haven't seen this shift ID before
                        if (!addedShifts.has(shift.id)) {
                            addedShifts.add(shift.id);
                            $('#office_time').append('<option value="' + shift.id + '">' + shift.opening_time + ' - ' + shift.closing_time + '</option>');
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading office times:', error);
            }
        });
    });

        // Department → load Designations (posts)
        $('#department').on('change', function () {
            const deptId = $(this).val();
            $('#post').html('<option value="">All Designations</option>');

            if (!deptId) return;

            $.ajax({
                url: '{{ url('admin/posts/get-All-posts') }}/' + deptId,
                success: function (data) {
                    if (data.posts && data.posts.length > 0) {
                        $.each(data.posts ?? data, function (i, post) {
                            $('#post').append('<option value="' + post.id + '">' + post.post_name + '</option>');
                        });
                    }
                }
            });
        });

        // Restore dropdowns on page load if filters were previously applied
        @if(!empty($filterParameters['branch_id']))
            $('#branch').val('{{ $filterParameters['branch_id'] }}').trigger('change');

            $(document).ajaxComplete(function () {
                @if(!empty($filterParameters['department_id']))
                    setTimeout(function() {
                        $('#department').val('{{ $filterParameters['department_id'] }}').trigger('change');
                    }, 300);
                @endif
                @if(!empty($filterParameters['supervisor_id']))
                    setTimeout(function() {
                        $('#supervisor').val('{{ $filterParameters['supervisor_id'] }}');
                    }, 500);
                @endif
                @if(!empty($filterParameters['post_id']))
                    setTimeout(function() {
                        $('#post').val('{{ $filterParameters['post_id'] }}');
                    }, 800);
                @endif
                @if(!empty($filterParameters['office_time_id']))
                    setTimeout(function() {
                        $('#office_time').val('{{ $filterParameters['office_time_id'] }}');
                    }, 500);
                @endif
            });
        @endif

        // // Export CSV button
        // $('#export_employee').on('click', function () {
        //     const form = $('#employeeFilterForm');
        //     const actionInput = $('<input>').attr({ type: 'hidden', name: 'action', value: 'export' });
        //     form.append(actionInput);
        //     form.submit();
        //     actionInput.remove();
        // });

        // Export CSV button
        $('#export_employee').on('click', function () {
            // Remove any previous export input to avoid duplicates
            $('#employeeFilterForm input[name="action"]').remove();
            // Append and submit — do NOT remove, page navigates away anyway
            $('<input>').attr({ type: 'hidden', name: 'action', value: 'export' })
                    .appendTo('#employeeFilterForm');
            $('#employeeFilterForm').submit();
        });
    </script>
@endsection
