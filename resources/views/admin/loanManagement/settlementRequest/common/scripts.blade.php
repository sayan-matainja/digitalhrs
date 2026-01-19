<script>
    $(document).ready(function() {
        $("#branch_id").select2({
            'placeholder': '{{ __("index.select_branch") }}'
        });
        $("#type").select2({
            'placeholder': '{{ __("index.select_loan_type") }}'
        });
        $("#department").select2({
            'placeholder': '{{ __("index.select_department") }}'
        });
        $("#employee").select2({
            'placeholder': '{{ __("index.select_employee") }}'
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $('body').on('click', '.delete', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_tada_confirm', ['title' => ':name']) }}'.replace(':name', title),
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });

        const loadDepartments = async (branchId) => {
            if (!branchId) return;

            let departmentId = "{{ $filterParameters['department_id'] ?? '' }}";

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${branchId}`,
                });



                $('#department').empty();
                $('#department').append('<option value="" selected>{{ __('index.select_department') }}</option>');
                if (response.data && response.data.length > 0) {
                    response.data.forEach(dept => {
                        $('#department').append(
                            `<option value="${dept.id}" ${dept.id == departmentId ? 'selected' : ''}>${dept.dept_name}</option>`
                        );
                    });
                }

            } catch (error) {
                console.error('Error loading departments:', error);
            }
        };

        const loadEmployees = async (departmentId) => {
            if (!departmentId) return;

            let employeeId = "{{ $filterParameters['employee_id'] ?? '' }}";

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/employees/get-all-employees') }}/${departmentId}`,
                });

                // Populate employees
                $('#employee').empty();
                $('#employee').append('<option value="" selected>{{ __('index.select_employee') }}</option>');
                if (response.data && response.data.length > 0) {
                    response.data.forEach(emp => {
                        $('#employee').append(
                            `<option value="${emp.id}" ${emp.id == employeeId ? 'selected' : ''}>${emp.name}</option>`
                        );
                    });
                }

            } catch (error) {
                console.error('Error loading employees:', error);
            }
        };


        // Event Listeners
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? $filterParameters['branch_id'] ??  'null' }};

        if (isAdmin) {
            $('#branch_id').on('change', function() {
                const branchId = $(this).val();
                loadDepartments(branchId);
                $('#department').val('').trigger('change');
                $('#employee').val('').trigger('change');
            });
            if ($('#branch_id').val()) {
                loadDepartments($('#branch_id').val());
            }
        } else if (defaultBranchId) {
            loadDepartments(defaultBranchId);
        }

        $('#department').on('change', function() {
            const departmentId = $(this).val();
            loadEmployees(departmentId);
            $('#employee').val('').trigger('change');
        });

        // Initial load for department if set
        if ("{{ $filterParameters['department_id'] ?? '' }}") {
            loadEmployees("{{ $filterParameters['department_id'] ?? '' }}");
        }
    });


    // script
    $(document).ready(function() {
        $(document).on('click', '#updateStatus', function(e) {
            e.preventDefault();
            const currentStatus = $(this).data('status');
            const reason = $(this).data('reason');
            const actionUrl = $(this).data('action');

            // Populate modal
            $('#currentStatus').val(currentStatus);
            $('#updateLoanStatusForm').attr('action', actionUrl);
            $('#remark').val(reason || '');

            // Set default to approve if current != cancel
            const defaultStatus = currentStatus === LoanStatusEnum.cancel.value ? LoanStatusEnum.cancel.value : LoanStatusEnum.approve.value;
            $('#loan_status').val(defaultStatus).trigger('change');

            // Show modal with null check
            const modalElement = document.getElementById('updateLoanStatus');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error('Modal element #updateLoanStatus not found.');
                alert('Modal not found. Please refresh the page.');
            }
        });


        // Listen for input on remark to re-validate
        $('#remark').on('input', function() {
            validateForm();
            // Update validity class based on current state
            const requiresRemark = $('#loan_status option:selected').data('requires-remark');
            const remarkValue = $(this).val().trim();
            if (requiresRemark && remarkValue === '') {
                $(this).addClass('is-invalid').removeClass('is-valid');
            } else if (requiresRemark && remarkValue !== '') {
                $(this).removeClass('is-invalid').addClass('is-valid');
            }
        });


        // Form validation before submit
        $('#updateLoanStatusForm').on('submit', function(e) {
            const status = $('#loan_status').val();
            const remarks = $('#remark').val().trim();
            const requiresRemark = $('#loan_status option:selected').data('requires-remark');

            if (status === '{{ \App\Enum\LoanStatusEnum::reject->value }}' && !remarks) {
                e.preventDefault();
                $('#remark').addClass('is-invalid');
                return false;
            }


            // Client-side enum check (optional)
            if (![LoanStatusEnum.approve.value, LoanStatusEnum.cancel.value].includes(status)) {
                e.preventDefault();
                alert('Invalid status selected.');
                return false;
            }
        });

        function validateForm() {
            const status = $('#loan_status').val();
            const statusValid = status !== '';
            let formValid = statusValid;

            if (status === '{{ \App\Enum\LoanStatusEnum::reject->value }}') {
                const requiresRemark = true;
                const remarkValid = $('#remark').val().trim() !== '';
                formValid = statusValid && remarkValid;
            }

            $('#updateBtn').prop('disabled', !formValid);
        }

        // Initial validation
        validateForm();
    });

    // Updated LoanStatusEnum to match enum values
    const LoanStatusEnum = {
        approve: { value: '{{ \App\Enum\LoanStatusEnum::approve->value }}' },
        cancel: { value: '{{ \App\Enum\LoanStatusEnum::reject->value }}' }
    };
</script>
