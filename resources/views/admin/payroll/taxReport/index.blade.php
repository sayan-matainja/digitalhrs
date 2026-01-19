@extends('layouts.master')

@section('title',__('index.employee_tax_report'))

@section('action',__('index.tax_report_generate'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.taxReport.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.tax_report') }}</h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.payroll.tax-report.index') }}" method="get">
                    <div class="payroll-fil border-bottom">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">

                                <select class="form-select form-select" name="year" id="year">
                                    <option disabled selected>{{ __('index.select_fiscal_year') }}</option>
                                    @foreach ($fiscalYears as $year)
                                        <option {{ ($filterData['year'] ?? old('year')) == $year->id ? 'selected': '' }} value="{{ $year->id }}">{{ $year->year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!isset(auth()->user()->branch_id))
                            <div class="col-lg-3 col-md-6 mb-3">
                                <select class="form-select" id="branch_id" name="branch_id" >
                                    <option value="">{{ __('index.select_branch') }}</option>
                                    @if(isset($companyDetail))
                                        @foreach($companyDetail->branches()->get() as $key => $branch)
                                            <option value="{{$branch->id}}"
                                                {{ (isset($filterData['branch_id']) && ($filterData['branch_id']) == $branch->id)  ? 'selected': '' }}>
                                                {{ucfirst($branch->name)}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @endif
                            <div class="col-lg-3 col-md-6 mb-3">
                                <select class="form-select" id="department_id" name="department_id" >
                                    <option value="">{{ __('index.select_department') }}</option>

                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <select class="form-select form-select" name="employee_id" id="employee_id">
                                    <option disabled selected>{{ __('index.select_employee') }}</option>

                                </select>
                            </div>

                        </div>

                        <div class=" row payroll-check d-flex justify-content-between align-items-center">

                            <div class="col-lg-3 col-md-6 mb-3 form-check">
                                <input type="checkbox" {{ isset($filterData['include_ssf']) && $filterData['include_ssf'] == 0 ? '' : 'checked' }} name="include_ssf" value="1" id="include_tada">
                                <label class="form-check-label" for="includeTada">
                                    {{ __('index.include_ssf') }}
                                </label>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3 d-md-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success me-md-2">{{ __('index.generate') }}</button>
                                <a href="{{ route('admin.payroll.tax-report.index') }}" class="btn btn-warning">{{ __('index.clear') }}</a>
                            </div>
                        </div>


                    </div>

                </form>
            </div>
        </div>
    </section>
    <section>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">{{ __('index.fiscal_year') }}</th>
                            <th class="text-center">{{ __('index.employee_name') }} </th>
                            <th class="text-center">{{ __('index.tax_payable') }}</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>


{{--                        @if(isset($filterData['employee_id']) && isset($filterData['year']) && !empty($reportData))--}}
{{--                            <tr>--}}
{{--                                <td>#</td>--}}
{{--                                <td class="text-center">{{ $reportData['year'] ?? '' }}</td>--}}
{{--                                <td class="text-center">{{ $reportData['name'] ?? '' }}</td>--}}
{{--                                <td class="text-center">{{ $reportData['total_payable_tds'] ?? '' }}</td>--}}
{{--                                <td class="text-center">--}}
{{--                                    <a class="nav-link dropdown-toggle p-0" href="#" id="actionDropdown"--}}
{{--                                       role="button"--}}
{{--                                       data-bs-toggle="dropdown"--}}
{{--                                       aria-haspopup="true"--}}
{{--                                       aria-expanded="false"--}}
{{--                                       title="More Action"--}}
{{--                                    >--}}
{{--                                    </a>--}}

{{--                                    <div class="dropdown-menu p-0" aria-labelledby="actionDropdown">--}}
{{--                                        <ul class="list-unstyled mb-0">--}}
{{--                                            <li class="dropdown-item p-2 border-bottom">--}}
{{--                                                <a href="{{ route('admin.payroll.tax-report.detail',$reportData['id']) }}">--}}
{{--                                                    <button class="btn btn-primary btn-xs">{{ __('index.view') }}--}}
{{--                                                    </button>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li class="dropdown-item p-2 border-bottom">--}}
{{--                                                <a href="{{ route('admin.payroll.tax-report.print',$reportData['id']) }}" target="_blank">--}}
{{--                                                    <button class="btn btn-primary btn-xs">{{ __('index.print') }}--}}
{{--                                                    </button>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li class="dropdown-item p-2>--}}
{{--                                                <a href="{{ route('admin.payroll.tax-report.edit',$reportData['id']) }}">--}}
{{--                                                    <button class="btn btn-primary btn-xs">{{ __('index.edit') }}</button>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @else--}}
                            @forelse($reportData as $report)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ $report['year'] }}</td>
                                    <td class="text-center">{{ $report['name'] }}</td>
                                    <td class="text-center">{{ $report['total_payable_tds'] }}</td>
                                    <td class="text-center">
                                        <a class="nav-link dropdown-toggle p-0" href="#" id="actionDropdown"
                                           role="button"
                                           data-bs-toggle="dropdown"
                                           aria-haspopup="true"
                                           aria-expanded="false"
                                           title="More Action"
                                        >
                                        </a>

                                        <div class="dropdown-menu p-0" aria-labelledby="actionDropdown">
                                            <ul class="list-unstyled p-1 mb-0">
                                                <li class="dropdown-item p-2 border-bottom">
                                                    <a href="{{ route('admin.payroll.tax-report.detail',$report['id']) }}">
                                                        <button class="btn btn-primary btn-xs">{{ __('index.view') }}
                                                        </button>
                                                    </a>
                                                </li>
                                                <li class="dropdown-item p-2 border-bottom">
                                                    <a href="{{ route('admin.payroll.tax-report.print',$report['id']) }}">
                                                        <button class="btn btn-primary btn-xs">{{ __('index.print') }}
                                                        </button>
                                                    </a>
                                                </li>
                                                <li class="dropdown-item p-2">
                                                    <a href="{{ route('admin.payroll.tax-report.edit',$report['id']) }}">
                                                        <button class="btn btn-primary btn-xs">{{ __('index.edit') }}</button>
                                                    </a>
                                                </li>
                                                <li class="dropdown-item p-2">
                                                    <a class="deleteReport"
                                                       data-href="{{ route('admin.payroll.tax-report.delete', $report['id']) }}">
                                                        <button
                                                            class="btn btn-primary btn-xs">{{ __('index.delete') }}</button>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%">
                                        <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                    </td>
                                </tr>
                            @endforelse
{{--                        @endif--}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('scripts')
    <script>
        $('document').ready(function () {
            $("#year").select2();
            $("#department_id").select2();
            $("#branch_id").select2();
            $("#employee_id").select2();



            const loadDepartments = async () => {
                const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
                const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
                const selectedBranchId = isAdmin ? $('#branch_id').val() : defaultBranchId;

                if (!selectedBranchId) return;

                let departmentId = "{{ $filterData['department_id'] ?? old('department_id') ?? '' }}";

                try {
                    $('#department_id').empty().append('<option selected disabled>{{ __("index.select_department") }}</option>');
                    $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');

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
                    console.error('Error loading departments:', error);
                    $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                }
            };

            const loadEmployees = async () => {
                const selectedDepartmentId = $('#department_id').val();
                let employeeId = "{{ $filterData['employee_id'] ?? old('employee_id') ?? '' }}";
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

                    const data = await response.json(); // Missing in original code


                    if (data.data && data.data.length > 0) {
                        // Populate dropdown with employee options
                        data.data.forEach(user => {
                            $('#employee_id').append(`<option value="${user.id}" ${data.id == employeeId ? 'selected' : ''}>${user.name}</option>`);
                        });
                    } else {
                        $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    }

                } catch (error) {
                    $('#employee_id').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                }
            };


            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            if (isAdmin) {
                $('#branch_id').on('change', loadDepartments);
                $('#branch_id').trigger('change');
            } else {
                loadDepartments(); // Load directly for regular users
            }
            // Load employees and posts when department is selected
            $('#department_id').change(loadEmployees);
        });


        $('.deleteReport').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.confirm_delete_report') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });
    </script>
@endsection
