@extends('layouts.master')

@section('title',__('index.time_leave_request'))

@section('action',__('index.create'))

@section('main-content')
    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveRequest.common.breadcrumb')
        <div class="row">
{{--            <div class="col-lg-2">--}}
{{--                @include('admin.leaveRequest.common.leave_menu')--}}
{{--            </div>--}}
{{--            <div class="col-lg-10">--}}
                <div class="card">
                    <div class="card-body pb-0">
                        <form class="forms-sample"
                              action="{{route('admin.time-leave-request.store')}}" method="post">
                            @csrf
                            <div class="row">
                                @if(!isset(auth()->user()->branch_id))
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option selected disabled>{{ __('index.select_branch') }}
                                        </option>
                                        @if(isset($companyDetail))
                                            @foreach($companyDetail->branches()->get() as $key => $branch)
                                                <option value="{{$branch->id}}"
                                                    {{ ((isset($noticeDetail) && ($noticeDetail->branch_id ) == $branch->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $branch->id)) ? 'selected': '' }}>
                                                    {{ucfirst($branch->name)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @endif

                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="department_id" class="form-label">{{ __('index.department') }} <span
                                            style="color: red">*</span></label>
                                    <select class="form-select" id="department_id" name="department_id">
                                        <option selected disabled>{{ __('index.select_department') }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="leave_type" class="form-label">{{__('index.requested_for')}}<span style="color: red">*</span></label>
                                    <select class="form-select" id="requestedBy" name="requested_by" required>
                                        <option selected disabled> {{__('index.select_employee')}}</option>

                                    </select>
                                </div>
                                @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <label for="issue_date" class="form-label">{{__('index.leave_date')}}  <span style="color: red">*</span> </label>
                                        <input type="text" id="nepali_startDate"
                                               name="issue_date"
                                               value="{{ old('issue_date') }}"
                                               placeholder="yyyy-mm-dd"
                                               class="form-control startDate"/>
                                    </div>
                                @else
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <label for="leave_from" class="form-label">{{__('index.leave_date')}}<span style="color: red">*</span></label>
                                        <input class="form-control" type="date" name="issue_date" value="{{old('issue_date')}}" required  />
                                    </div>
                                @endif
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="start_time" class="form-label">{{__('index.from')}} <span style="color: red">*</span></label>
                                    <input class="form-control" type="time" name="leave_from" value="{{old('leave_from')}}" required  />
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="end_time" class="form-label">{{__('index.to')}}</label>
                                    <input class="form-control end_time" type="time" name="leave_to" value="{{old('leave_to')}}"  />
                                </div>

                                <div class="col-lg-4 mb-4">
                                    <label for="note" class="form-label">{{__('index.reason')}}<span style="color: red">*</span></label>
                                    <textarea class="form-control" name="reasons" rows="6" >{{  old('reasons') }}</textarea>
                                </div>

                                <div class="col-lg-12 mb-4 text-start">
                                    <button type="submit" class="btn btn-primary">
                                        {{__('index.submit')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
{{--            </div>--}}
        </div>

    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            $("#branch_id").select2({});
            $("#requestedBy").select2({});
            $("#department_id").select2({});

            $('#nepali_startDate').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
            });
        });

        $(document).ready(function () {
            const loadDepartments = async () => {
                const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
                const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
                const selectedBranchId = isAdmin ? $('#branch_id').val() : defaultBranchId;

                if (!selectedBranchId) return;

                try {
                    $('#department_id').empty().append('<option selected disabled>{{ __("index.select_department") }}</option>');
                    $('#requestedBy').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');

                    const response = await $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                    });

                    if (!response || !response.data || response.data.length === 0) {
                        $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                        return;
                    }

                    response.data.forEach(data => {
                        $('#department_id').append(`<option value="${data.id}">${data.dept_name}</option>`);
                    });
                } catch (error) {
                    console.error('Error loading departments:', error);
                    $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                }
            };

            const loadEmployees = async () => {
                const selectedDepartmentId = $('#department_id').val();
                if (!selectedDepartmentId) return;

                try {
                    $('#requestedBy').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');

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
                            $('#requestedBy').append(`<option value="${user.id}">${user.name}</option>`);
                        });
                    } else {
                        $('#requestedBy').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    }

                } catch (error) {
                    $('#requestedBy').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                }
            };

            // Load departments when branch is selected

            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            if (isAdmin) {
                $('#branch_id').change(loadDepartments);
            } else {
                loadDepartments(); // Load directly for regular users
            }
            // Load employees and posts when department is selected
            $('#department_id').change(loadEmployees);

        });
    </script>

@endsection

