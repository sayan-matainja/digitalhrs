@extends('layouts.master')

@section('title',__('index.leave_request'))

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
                          action="{{route('admin.leave-request.save')}}" method="post">
                        @csrf

                        <div class="row">
                            @if(!isset(auth()->user()->branch_id))
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                    <select class="form-select" id="branch_id" name="branch_id" required>
                                        <option selected disabled>{{ __('index.select_branch') }}
                                        </option>
                                        @if(isset($companyDetail))
                                            @foreach($companyDetail->branches()->get() as $key => $branch)
                                                <option value="{{$branch->id}}">
                                                    {{ucfirst($branch->name)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif
                            <!-- Departments Field -->
                            <div class="col-lg-4 col-md-6 mb-4">
                                <label for="department_id" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option selected disabled>{{ __('index.select_department') }}</option>

                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <label for="requestedBy" class="form-label">{{ __('index.requested_for') }}<span style="color: red">*</span></label>
                                <select class="form-select" id="requestedBy" name="requested_by" required>
                                    <option selected disabled>{{ __('index.select_employee') }}</option>

                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <label for="leaveType" class="form-label">{{ __('index.leave_type') }}<span style="color: red">*</span></label>
                                <select class="form-select" id="leaveType" name="leave_type_id" required>
                                    <option selected disabled>{{ __('index.select_leave_type') }} </option>

                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <label for="leave_for" class="form-label">{{ __('index.leave_for') }}<span style="color: red">*</span></label>
                                <select class="form-select" id="leave_for" name="leave_for" required>
                                    <option selected disabled>{{ __('index.select_leave_for') }} </option>
                                    <option value="full_day">Full Day</option>
                                    <option value="half_day">Half Day</option>
                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-4">
                                <label for="leave_from" class="form-label">{{ __('index.from_date') }}<span style="color: red">*</span></label>
                                @if($bsEnabled)
                                    <input type="text" class="form-control leave_from" id="leave_from" value="{{old('leave_from')}}" name="leave_from" autocomplete="off">
                                @else
                                    <input class="form-control" type="date" name="leave_from" value="{{old('leave_from')}}" required  />
                                @endif
                            </div>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <label for="leave_to" class="form-label">{{ __('index.to_date') }}</label>
                                @if($bsEnabled)
                                    <input type="text" class="form-control leave_to" id="leave_to" value="{{old('leave_to')}}" name="leave_to" autocomplete="off">
                                @else
                                    <input class="form-control" type="date" name="leave_to" value="{{old('leave_to')}}"  />

                                @endif
                            </div>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <label for="leave_in" class="form-label">{{ __('index.leave_in') }}<span style="color: red">*</span></label>
                                <select class="form-select" id="leave_in" name="leave_in" required>
                                    <option selected disabled>{{ __('index.select_leave_in') }} </option>
                                    <option value="first_half">First Half</option>
                                    <option value="second_half">Second Half</option>
                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-4">
                                <label for="info" class="form-label">{{ __('index.total_duration') }} </label>
                                <input class="form-control bg-light" type="text" readonly id="no_of_days" name="no_of_days" value="{{old('end_time')}}"  />
                            </div>
                            <div class="col-lg-4 mb-4">
                                <label for="note" class="form-label">{{ __('index.reason') }}<span style="color: red">*</span></label>
                                <textarea class="form-control" name="reasons" rows="6" >{{  old('reasons') }}</textarea>
                            </div>

                            <div class="col-lg-12 mb-4 text-start">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('index.submit') }}
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

            @if($bsEnabled)
            $('.leave_from').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
                onChange: function () {
                    calculateDays();
                }
            });

            $('.leave_to').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
                onChange: function () {
                    calculateDays();
                }
            });
            @else
            $('input[name="leave_from"], input[name="leave_to"]').on('change', function () {
                calculateDays();
            });
            @endif

            function calculateDays() {
                let from = $('input[name="leave_from"]').val();
                let to   = $('input[name="leave_to"]').val();
                let noOfDaysField = $('#no_of_days');

                if (!from || !to) {
                    noOfDaysField.val('');
                    return;
                }

                try {
                    @if($bsEnabled)
                    if (typeof NepaliFunctions !== "undefined") {
                        // Convert BS → AD using library
                        let fromAd = NepaliFunctions.BS2AD(from);
                        let toAd   = NepaliFunctions.BS2AD(to);
                        processDates(fromAd, toAd);
                    } else {
                        console.warn("NepaliFunctions not available. Duration will be empty.");
                        noOfDaysField.val('');
                    }
                    @else
                    processDates(from, to);
                    @endif
                } catch (e) {
                    console.error("Error calculating duration:", e.message);
                    noOfDaysField.val('');
                }
            }

            function processDates(from, to) {
                let noOfDaysField = $('#no_of_days');

                let start = new Date(from);
                let end   = new Date(to);

                if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                    noOfDaysField.val('');
                    return;
                }

                let diff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                noOfDaysField.val(diff > 0 ? diff : 0);
            }
        });
        $(document).ready(function () {

            $("#department_id").select2();
            $("#branch_id").select2();
            $("#requestedBy").select2();
            $("#leaveType").select2();


            const loadDepartments = async () => {
                const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
                const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
                const selectedBranchId = isAdmin ? $('#branch_id').val() : defaultBranchId;

                if (!selectedBranchId) return;

                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                    });

                    // Clear existing options
                    $('#department_id').empty();

                    $('#department_id').append('<option selected disabled>{{ __("index.select_department") }}</option>');
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(department => {
                            $('#department_id').append(
                                `<option value="${department.id}">${department.dept_name}</option>`
                            );
                        });
                    } else {
                        $('#department_id').append('<option disabled>{{ __("index.no_department_found") }}</option>');
                    }

                    loadEmployees();

                } catch (error) {
                    $('#department_id').append('<option disabled>{{ __("index.error_loading_department") }}</option>');
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
                    $('#requestedBy').empty();
                    $('#requestedBy').append('<option selected disabled>{{ __("index.select_employee") }}</option>');

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
            const loadLeaveTypes = async () => {
                const selectedEmployee = $('#requestedBy').val();
                if (!selectedEmployee) return;
                try {
                    $('#leaveType').empty().append('<option selected disabled>{{ __("index.select_leave_type") }}</option>');

                    const response = await fetch(`{{ url('admin/leaves/get-employee-leave-types') }}/${selectedEmployee}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    });

                    const data = await response.json();
                    $('#leaveType').empty();
                    $('#leaveType').append('<option selected disabled>{{ __("index.select_leave_type") }}</option>');

                    if (data.leveTypes && data.leveTypes.length > 0) {
                        data.leveTypes.forEach(type => {
                            $('#leaveType').append(
                                `<option value="${type.id}">${type.name}</option>`
                            );
                        });
                    } else {
                        $('#leaveType').append('<option disabled>{{ __("index.leave_type_not_found") }}</option>');
                    }

                } catch (error) {
                    $('#leaveType').append('<option disabled>{{ __("index.error_loading_leave_types") }}</option>');
                }
            };
            // Load data when branch is selected
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            if (isAdmin) {
                $('#branch_id').change(loadDepartments).trigger('change');
                $('#requestedBy').empty();
                $('#leaveType').empty();
            } else {
                loadDepartments(); // Load directly for regular users
                $('#requestedBy').empty();
                $('#leaveType').empty();
            }
            // Corrected selector and trigger
            $('#department_id').change(loadEmployees).trigger('change'); // Corrected selector and trigger
            $('#requestedBy').change(loadLeaveTypes).trigger('change'); // Corrected selector and trigger

            // Handle leave_for change for showing/hiding fields
            $('#leave_for').change(function() {
                var value = $(this).val();
                var leaveToDiv = $('#leave_to').closest('.col-lg-3.col-md-6.mb-4');
                var leaveInDiv = $('#leave_in').closest('.col-lg-3.col-md-6.mb-4');
                var infoDiv = $('#no_of_days').closest('.col-lg-3.col-md-6.mb-4');

                if (value === 'full_day') {
                    leaveToDiv.show();
                    $('#leave_to').prop('required', true);
                    infoDiv.show();
                    leaveInDiv.hide();
                    $('#leave_in').prop('required', false);
                    calculateDays(); // Recalculate if dates are set
                } else if (value === 'half_day') {
                    leaveToDiv.hide();
                    $('#leave_to').prop('required', false).val('');
                    infoDiv.hide();
                    $('#no_of_days').val('');
                    leaveInDiv.show();
                    $('#leave_in').prop('required', true);
                }
            }).trigger('change'); // Trigger initially to set default state (though default is disabled, it will do nothing until selected)
        });

    </script>


@endsection
