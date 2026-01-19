
@extends('layouts.master')

@section('title',__('index.nfc'))
@section('styles')
    <style>
        .qr > svg {
            height: 100px;
            width: 100px;
        }
    </style>
@endsection
@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">@lang('index.dashboard')</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.nfc.index')}}">@lang('index.nfc_section')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('index.nfc')</li>
            </ol>
        </nav>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.nfc_filter')  }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.nfc.index') }}" method="get">

                <div class="row align-items-center">

                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-4 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  selected  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterData['branch_id']) && $filterData['branch_id']  == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif


                    <div class="col-lg-4 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option  selected  disabled>{{ __('index.select_department') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option  selected  disabled>{{ __('index.select_employee') }}
                            </option>
                        </select>
                    </div>


                    <div class="col-lg-2 col-md-6 d-md-flex">
                        <button type="submit" class="btn btn-block btn-success me-md-2 me-0 mb-md-4 mb-2">{{ __('index.filter') }}</button>

                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4"
                           href="{{ route('admin.nfc.index') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.nfc_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.title')</th>
                            <th>@lang('index.created_by')</th>
                            <th class="text-center">@lang('index.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        @forelse($nfcData as $nfc)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $nfc->title }}</td>
                                <td>
                                   {{ $nfc->createdBy?->name }}
                                </td>

                                <td class="text-center">
                                    @can('delete_nfc')
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            <li class="me-2">
                                                <a class="deleteNFC"
                                                   data-href="{{route('admin.nfc.destroy',$nfc->id)}}" title="@lang('index.delete')">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>@lang('index.no_records_found')</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            $('.deleteNFC').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '@lang('index.delete_confirmation')',
                    showDenyButton: true,
                    confirmButtonText: `@lang('index.yes')`,
                    denyButtonText: `@lang('index.no')`,
                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })


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

                    const data = await response.json(); // Missing in original code


                    console.log(employeeId);
                    if (data.data && data.data.length > 0) {
                        // Populate dropdown with employee options
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

                    // Trigger initial load if branch is selected
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

                // Attach department change listener
                $('#department_id').on('change', loadEmployees);

                // Trigger initial employee load if department is pre-selected
                if (departmentId) {
                    $('#department_id').trigger('change');
                }
            };

            // Initialize everything
            initializeDropdowns();

        });

    </script>
@endsection






