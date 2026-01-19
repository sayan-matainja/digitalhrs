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

        const loadTypesAndDepartments = async (branchId) => {
            if (!branchId) return;

            let typeId = "{{ $filterParameters['type_id'] ?? '' }}";
            let departmentId = "{{ $filterParameters['department_id'] ?? '' }}";

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/loan/get-branch-loan-data') }}/${branchId}`,
                });

                // Populate types
                $('#type').empty();
                $('#type').append('<option value="" selected>{{ __('index.select_loan_type') }}</option>');
                if (response.types && response.types.length > 0) {
                    response.types.forEach(type => {
                        $('#type').append(
                            `<option value="${type.id}" ${type.id == typeId ? 'selected' : ''}>${type.name}</option>`
                        );
                    });
                }

                $('#department').empty();
                $('#department').append('<option value="" selected>{{ __('index.select_department') }}</option>');
                if (response.departments && response.departments.length > 0) {
                    response.departments.forEach(dept => {
                        $('#department').append(
                            `<option value="${dept.id}" ${dept.id == departmentId ? 'selected' : ''}>${dept.dept_name}</option>`
                        );
                    });
                }

            } catch (error) {
                console.error('Error loading types and departments:', error);
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
                loadTypesAndDepartments(branchId);
                $('#department').val('').trigger('change');
                $('#employee').val('').trigger('change');
            });
            if ($('#branch_id').val()) {
                loadTypesAndDepartments($('#branch_id').val());
            }
        } else if (defaultBranchId) {
            loadTypesAndDepartments(defaultBranchId);
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


    $(document).ready(function() {
        // When clicking the "Update Status" button
        $(document).on('click', '#updateStatus', function(e) {
            e.preventDefault();
            const currentStatus = $(this).data('status');
            const reason = $(this).data('reason');
            const actionUrl = $(this).data('action');

            $('#currentStatus').val(currentStatus);
            $('#updateLoanStatusForm').attr('action', actionUrl);
            $('#remark').val(reason || '');

            const defaultStatus = currentStatus === '{{ \App\Enum\LoanStatusEnum::reject->value }}'
                ? '{{ \App\Enum\LoanStatusEnum::reject->value }}'
                : '{{ \App\Enum\LoanStatusEnum::approve->value }}';

            $('#loan_status').val(defaultStatus).trigger('change');

            const modalElement = document.getElementById('updateLoanStatus');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error('Modal element #updateLoanStatus not found.');
                alert('Modal not found. Please refresh the page.');
            }
        });

        $('#updateLoanStatus').on('shown.bs.modal', function () {
            @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
            if (!$('#repayment_date').hasClass('ndp-initialized')) {
                $('#repayment_date').nepaliDatePicker({
                    dateFormat: 'MM/DD/YYYY',
                    language: 'english',
                    ndpYear: true,
                    ndpMonth: true,
                    ndpYearCount: 20,
                    disableAfter: '2089-12-30',
                    container: '#updateLoanStatus'
                });
            }
            @endif
        });

        // Handle status change (show/hide fields and clear messages)
        $('#loan_status').on('change', function() {
            const selectedStatus = $(this).val();
            const remarkGroup = $('#remarkGroup');
            const approveGroup = $('#approveGroup');

            // Clear all error messages
            $('.validation-message').text('');

            if (selectedStatus === '{{ \App\Enum\LoanStatusEnum::reject->value }}') {
                remarkGroup.show();
                approveGroup.hide();
                $('#remark').prop('required', true);
            } else if (selectedStatus === '{{ \App\Enum\LoanStatusEnum::approve->value }}') {
                remarkGroup.hide();
                approveGroup.show();
                $('#repayment_date').prop('required', true);
                $('#payment_method').prop('required', true);
                $('#remark').prop('required', false);
            } else {
                remarkGroup.hide();
                approveGroup.hide();
            }

            validateForm();
        });

        // Real-time validation on input/change
        $('#remark, #repayment_date, #payment_method').on('input change', function() {
            validateForm();
        });

        // Main form validation function - now uses text messages only
        function validateForm() {
            const status = $('#loan_status').val();
            let isValid = true;

            // Clear previous messages
            $('.validation-message').text('');

            if (!status) {
                isValid = false;
            } else if (status === '{{ \App\Enum\LoanStatusEnum::reject->value }}') {
                const remark = $('#remark').val().trim();
                if (remark.length === 0) {
                    $('#remarkError').text('Remark is required when rejecting the loan.');
                    isValid = false;
                }
            } else if (status === '{{ \App\Enum\LoanStatusEnum::approve->value }}') {
                const date = $('#repayment_date').val().trim();
                const method = $('#payment_method').val();

                if (!date) {
                    $('#repaymentDateError').text('Repayment date is required.');
                    isValid = false;
                }
                if (!method) {
                    $('#paymentMethodError').text('Payment method is required.');
                    isValid = false;
                }
            }

            $('#updateBtn').prop('disabled', !isValid);
        }

        // Final submit validation (extra safety)
        $('#updateLoanStatusForm').on('submit', function(e) {
            validateForm(); // Re-run validation

            if ($('#updateBtn').prop('disabled')) {
                e.preventDefault();
                alert('Please fix the errors before submitting.');
                return false;
            }
        });

        // Reset form when modal is hidden
        $('#updateLoanStatus').on('hidden.bs.modal', function () {
            $('#updateLoanStatusForm')[0].reset();
            $('#approveGroup, #remarkGroup').hide();
            $('.validation-message').text(''); // Clear all messages
            $('#updateBtn').prop('disabled', true);
            $('#loan_status').val('').trigger('change');
        });

        // Initial button state
        $('#updateBtn').prop('disabled', true);
    });

    // Updated LoanStatusEnum to match enum values
    const LoanStatusEnum = {
        approve: { value: '{{ \App\Enum\LoanStatusEnum::approve->value }}' },
        cancel: { value: '{{ \App\Enum\LoanStatusEnum::reject->value }}' }
    };


</script>
