<script>
    $(document).ready(function () {

        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
        $('#department_id').select2({
            placeholder: @json(__('index.select_department')),
            allowClear: true,
            width: '100%',
        });

        $('#employee_id').select2({
            placeholder: @json(__('index.select_employee')),
            allowClear: true,
            width: '100%'
        });

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: `{{ __('index.change_status_confirm') }}`,
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }else if (result.isDenied) {
                    (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                }
            })
        })

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{ __('index.delete_confirmation') }}`,
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('#pay_type').on('change',function () {
            let pay = $(this).val();

            if(pay === '1'){
                $('.pay_percent').addClass('d-none');
                $('.pay_rate').removeClass('d-none');

            }else{
                $('.pay_percent').removeClass('d-none');
                $('.pay_rate').addClass('d-none');

            }
        })

        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};

        const departmentIds = {!! isset($departmentIds) ? json_encode($departmentIds) : '[]' !!}.map(String);
        const employeeIds = {!! isset($employeeIds) ? json_encode($employeeIds) : '[]' !!}.map(String);

        // Load departments based on branch_id
        const loadDepartments = async (branchId) => {
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const selectedBranchId = branchId || (isAdmin ? $('#branch_id').val() : defaultBranchId);

            if (!selectedBranchId) {
                $('#department_id').html('<option disabled>{{ __("index.select_department") }}</option>').trigger('change');
                $('#employee_id').html('<option disabled>{{ __("index.assign_employee") }}</option>').trigger('change');
                return;
            }

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                $('#department_id').empty();
                if (response.data && response.data.length > 0) {
                    response.data.forEach(department => {
                        const isSelected = departmentIds.includes(String(department.id)) ? 'selected' : '';
                        $('#department_id').append(
                            `<option value="${department.id}" ${isSelected}>${department.dept_name}</option>`
                        );
                    });
                } else {
                    $('#department_id').append('<option disabled>{{ __("index.no_department_found") }}</option>');
                }

                // If using Select2, reinitialize it
                if ($('#department_id').data('select2')) {
                    $('#department_id').select2();
                }

                $('#department_id').trigger('change');

            } catch (error) {
                console.error('Error loading departments:', error);
                $('#department_id').html('<option disabled>{{ __("index.error_loading_department") }}</option>').trigger('change');
                $('#employee_id').html('<option disabled>{{ __("index.assign_employee") }}</option>').trigger('change');
            }
        };

        // Load employees based on selected department_ids and payroll_type
        const loadEmployees = async (departmentIds, payrollType) => {
            const selectedDepartmentIds = departmentIds || ($('#department_id').val() || []);
            const selectedPayrollType = payrollType || $('#payroll_type').val();

            if (!selectedDepartmentIds.length || !selectedPayrollType) {
                return;
            }

            // Preserve already selected employee IDs
            const alreadySelected = $('#employee_id').val() || [];

            try {
                const response = await fetch(`{{ url('admin/overtime/get-user-department-data') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        department_ids: selectedDepartmentIds,
                        payroll_type: selectedPayrollType
                    }),
                });

                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

                const data = await response.json();
                $('#employee_id').empty();

                if (data.users && data.users.length > 0) {
                    const uniqueEmployees = new Map();
                    data.users.forEach(user => {
                        if (!uniqueEmployees.has(user.id)) {
                            uniqueEmployees.set(user.id, user);
                            // Check if either old selection OR employeeIds from PHP should stay selected
                            const isSelected = alreadySelected.includes(String(user.id)) || employeeIds.includes(String(user.id)) ? 'selected' : '';
                            $('#employee_id').append(
                                `<option value="${user.id}" ${isSelected}>${user.name}</option>`
                            );
                        }
                    });

                    if ($('#employee_id').data('select2')) {
                        $('#employee_id').select2();
                    }
                    $('#employee_id').trigger('change');
                } else {
                    $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>').trigger('change');
                }

            } catch (error) {
                console.error('Error loading employees:', error);
                $('#employee_id').html('<option disabled>{{ __("index.error_loading_employees") }}</option>').trigger('change');
            }
        };



        // Always attach department and payroll_type change handler
        $('#department_id, #payroll_type').on('change', function() {
            loadEmployees();
        });

        // Branch handling: only conditional
        if (isAdmin) {
            $('#branch_id').on('change', function() {
                loadDepartments($(this).val());
                $('#employee_id').empty();
            });

            const presetBranchId = $('#branch_id').val() || null;
            if (presetBranchId) {
                loadDepartments(presetBranchId);
            } else {
                $('#branch_id').trigger('change');
            }
        } else {
            loadDepartments();
        }

// For edit mode, load employees initially
        if ($('#payroll_type').val() && departmentIds.length > 0) {
            loadEmployees(departmentIds, $('#payroll_type').val());
        }

    });

</script>
