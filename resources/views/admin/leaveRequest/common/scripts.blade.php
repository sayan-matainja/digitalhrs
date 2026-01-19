<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $('body').on('click', '.leaveRequestUpdate', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            let status = $(this).data('status');
            let remark = $(this).data('remark');
            let leaveRequestId = $(this).data('id');

            $('.modal-title').html('Leave Status Update');
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

        $('.reset').click(function(event){
            event.preventDefault();
            $('#requestedBy').val('');
            $('#leaveType').val('');
            $('#month').val('');
            $('#status').val('');
            $('#year').val('');
        })



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



        $("#department_id").select2({});
        $("#branch_id").select2({});
        $("#requestedBy").select2({});
        $("#leaveType").select2({});

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

                // Clear existing options
                $('#department_id').empty();



                $('#department_id').append('<option selected disabled>{{ __("index.select_department") }}</option>');
                if (response.data && response.data.length > 0) {
                    response.data.forEach(department => {
                        $('#department_id').append(
                            `<option value="${department.id}" ${department.id == departmentId ? 'selected' : ''}>${department.dept_name}</option>`
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

                const data = await response.json();
                $('#requestedBy').empty();
                $('#requestedBy').append('<option selected disabled>{{ __("index.select_employee") }}</option>');

                if (data.data && data.data.length > 0) {
                    data.data.forEach(user => {
                        $('#requestedBy').append(
                            `<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''}>${user.name}</option>`
                        );
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
                            `<option value="${type.id}" ${type.id == leaveTypeId ? 'selected' : ''}>${type.name}</option>`
                        );
                    });
                } else {
                    $('#leaveType').append('<option disabled>{{ __("index.leave_type_not_found") }}</option>');
                }

            } catch (error) {
                $('#leaveType').append('<option disabled>{{ __("index.error_loading_leave_types") }}</option>');
            }
        };

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId; // Use DOM value or filter parameter
                $('#branch_id').change(async () => {
                    await loadDepartments($('#branch_id').val());
                    // Clear requestedBy when branch changes
                    $('#requestedBy').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                });
            } else {
                selectedBranchId = defaultBranchId;
            }

            if (selectedBranchId) {
                await loadDepartments(selectedBranchId);
            }
        };

        $(document).ready(() => {
            initializeDropdowns();
            $('#department_id').change(loadEmployees);
            if (departmentId) {
                loadEmployees();

                $('#requestedBy').change(loadLeaveTypes);

            }
        });
    });
</script>
