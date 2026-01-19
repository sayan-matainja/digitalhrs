<script src="{{ asset('assets/vendors/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/js/tinymce.js') }}"></script>

<script>
    $(document).ready(function() {
        $("#branch_id").select2({
            'placeholder': '{{ __("index.select_branch") }}'
        });
        $("#department_id").select2({
            'placeholder': '{{ __("index.select_department") }}'
        });
        $("#employee_id").select2({
            'placeholder': '{{ __("index.select_employee") }}'
        });
        $("#loan_id").select2({
            'placeholder': '{{ __("index.select_loan") }}'
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const loadDepartments = async (branchId) => {
            if (!branchId) return;

            let departmentId = "{{ $requestDetail->department_id ?? old('department_id') ?? '' }}";

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${branchId}`,
                });

                // Clear departments select
                $('#department_id').empty();
                let deptPlaceholderSelected = !departmentId;
                var deptPlaceholderOpt = new Option('{{ __('index.select_department') }}', '', deptPlaceholderSelected, deptPlaceholderSelected);
                $('#department_id').append(deptPlaceholderOpt);

                if (response.data && response.data.length > 0) {
                    response.data.forEach(dept => {
                        var deptOpt = new Option(dept.dept_name, dept.id, false, dept.id == departmentId);
                        $('#department_id').append(deptOpt);
                    });
                } else {
                    var noDeptOpt = new Option('{{ __("index.department_not_found") }}', '', false, false);
                    $('#department_id').append(noDeptOpt);
                }
                $('#department_id').trigger('change');

            } catch (error) {
                console.error('Error loading types and departments:', error);
                // Clear and set error for loan types
                $('#loan_type_id').empty();
                var errorOpt = new Option('{{ __("index.error_loading_loan_types") }}', '', true, true);
                $('#loan_type_id').append(errorOpt).trigger('change');
                // Clear and set error for departments
                $('#department_id').empty();
                var deptErrorOpt = new Option('{{ __("index.error_loading_departments") }}', '', true, true);
                $('#department_id').append(deptErrorOpt).trigger('change');
            }
        };

        const loadEmployees = async (departmentId) => {
            if (!departmentId) return;

            let employeeId = "{{ $requestDetail->employee_id ?? old('employee_id') ?? '' }}";

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/employees/get-all-employees') }}/${departmentId}`,
                });

                // Clear employees select
                $('#employee_id').empty();
                let empPlaceholderSelected = !employeeId;
                var empPlaceholderOpt = new Option('{{ __('index.select_employee') }}', '', empPlaceholderSelected, empPlaceholderSelected);
                $('#employee_id').append(empPlaceholderOpt);

                if (response.data && response.data.length > 0) {
                    response.data.forEach(employee => {
                        var empOpt = new Option(employee.name, employee.id, false, employee.id == employeeId);
                        $('#employee_id').append(empOpt);
                    });
                } else {
                    var noEmpOpt = new Option('{{ __("index.no_employees_found") }}', '', false, false);
                    $('#employee_id').append(noEmpOpt);
                }
                $('#employee_id').trigger('change');

            } catch (error) {
                console.error('Error loading employees:', error);
                // Clear and set error
                $('#employee_id').empty();
                var empErrorOpt = new Option('{{ __("index.error_loading_employees") }}', '', true, true);
                $('#employee_id').append(empErrorOpt).trigger('change');
            }
        };

        const loadLoans = async (employeeId) => {
            if (!employeeId) return;

            let loanId = "{{ $requestDetail->loan_id ?? old('loan_id') ?? '' }}";

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/loan/get-employee-loan') }}/${employeeId}`,
                });

                // Clear loan select
                $('#loan_id').empty();
                let loanPlaceholderSelected = !loanId;
                var loanPlaceholderOpt = new Option('{{ __('index.select_loan') }}', '', loanPlaceholderSelected, loanPlaceholderSelected);
                $('#loan_id').append(loanPlaceholderOpt);

                if (response.loan) {
                    var loanOpt = new Option(response.loan.loan_id, response.loan.id, false, true);
                    $('#loan_id').append(loanOpt);

                    // 🧠 Store all necessary info for later use
                    $('#loan_id')
                        .attr('data-manualAmount', response.manualAmount || 0)
                        .attr('data-salaryAmount', response.salaryAmount || 0)
                        .attr('data-salary', response.monthly_salary || 0)
                        .attr('data-interest-rate', response.interest_rate || 0)
                        .attr('data-interest-type', response.interest_type || '')
                        .attr('data-interest-amount', response.interest_amount || 0)
                        .attr('data-loan-amount', response.loan_amount || 0);
                } else {
                    var noLoanOpt = new Option('{{ __("index.no_employee_loan_found") }}', '', false, false);
                    $('#loan_id').append(noLoanOpt);
                }
                $('#loan_id').trigger('change');

            } catch (error) {
                console.error('Error loading employee loans:', error);
                // Clear and set error
                $('#loan_id').empty();
                var loanErrorOpt = new Option('{{ __("index.error_loading_employee_loans") }}', '', true, true);
                $('#loan_id').append(loanErrorOpt).trigger('change');
            }
        };

        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};

        if (isAdmin) {
            $('#branch_id').on('change', function() {
                const branchId = $(this).val();
                loadDepartments(branchId);
                $('#department_id').val('').trigger('change');
                $('#employee_id').val('').trigger('change');
                $('#loan_id').val('').trigger('change');
            });
            if ($('#branch_id').val()) {
                loadDepartments($('#branch_id').val());
            }
        } else if (defaultBranchId) {
            loadDepartments(defaultBranchId);
        }

        $('#department_id').on('change', function() {
            const departmentId = $(this).val();
            loadEmployees(departmentId);
            $('#employee_id').val('').trigger('change');
            $('#loan_id').val('').trigger('change'); // Clear loan on dept change
        });

        if ("{{ $requestDetail->department_id ?? '' }}") {
            loadEmployees("{{ $requestDetail->department_id ?? '' }}");
        }

        $('#employee_id').on('change', function() {
            const employeeId = $(this).val();
            loadLoans(employeeId);
            $('#loan_id').val('').trigger('change');
        });

        if ("{{ $requestDetail->employee_id ?? '' }}") {
            loadLoans("{{ $requestDetail->employee_id ?? '' }}");
        }

        $(document).on('change', '#settlement_type, #settlement_method', function () {
            var type = $('#settlement_type').val();
            var method = $('#settlement_method').val();
            var loanId = $('#loan_id').val();
            var $amountDiv = $('#amount').closest('.col-lg-4');
            var $amountInput = $('#amount');

            $amountInput.val('');
            $amountDiv.addClass('d-none');

            if (!loanId || !type || !method) return;

            // Get loan data from data attributes
            var salary = parseFloat($('#loan_id').attr('data-salary')) || 0;
            var manualAmount = parseFloat($('#loan_id').attr('data-manualAmount')) || 0;
            var salaryAmount = parseFloat($('#loan_id').attr('data-salaryAmount')) || 0;
            // var interestRate = parseFloat($('#loan_id').attr('data-interest-rate')) || 0;
            // var interestType = $('#loan_id').attr('data-interest-type');
            // var interestAmount = parseFloat($('#loan_id').attr('data-interest-amount')) || 0;
            // var loanAmount = parseFloat($('#loan_id').attr('data-loan-amount')) || 0;


            // Always show the amount field once both type & method are selected
            $amountDiv.removeClass('d-none');

            if (salary == 0) {
                alert('This settlement by salary cannot be processed. Employee salary is not set.');
                return;
            }
            if (type == 'partial') {
                // Partial → user enters manually
                $amountInput.removeAttr('readonly').val('');
            } else if (type == 'full') {
                let amount = 0;

                if (method == 'salary') {
                    amount = salaryAmount;
                } else if (method == 'manual') {
                    amount = manualAmount;
                }

                amount = parseFloat(amount.toFixed(2));
                $amountInput.val(amount).attr('readonly', true);
            }
        });


        tinymce.init({
            selector: '#tinymceDescription',
            height: 200,
            menubar: false,
            plugins: ['advlist autolink lists link image charmap print preview anchor', 'searchreplace visualblocks code fullscreen', 'insertdatetime media table paste code help wordcount'],
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | removeformat | help',
        });

    });
</script>
