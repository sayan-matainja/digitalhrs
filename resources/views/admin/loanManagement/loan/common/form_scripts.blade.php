<script src="{{ asset('assets/vendors/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/js/tinymce.js') }}"></script>

<script>
    $(document).ready(function() {
        $("#branch_id").select2({
            'placeholder': '{{ __("index.select_branch") }}'
        });
        $("#loan_type_id").select2({
            'placeholder': '{{ __("index.select_loan_type") }}'
        });
        $("#department_id").select2({
            'placeholder': '{{ __("index.select_department") }}'
        });
        $("#employee_id").select2({
            'placeholder': '{{ __("index.select_employee") }}'
        });

        $('#attachment').change(function() {
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let types = []; // Global to store loan types data

        const loadLoanTypesAndDepartments = async (branchId) => {
            if (!branchId) return;

            let loanTypeId = "{{ $loanDetail->loan_type_id ?? old('loan_type_id') ?? '' }}";
            let departmentId = "{{ $loanDetail->department_id ?? old('department_id') ?? '' }}";

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/loan/get-branch-loan-data') }}/${branchId}`,
                });

                types = response.types || []; // Store types globally

                // Clear loan types select
                $('#loan_type_id').empty();
                let placeholderSelected = !loanTypeId;
                var placeholderOpt = new Option('{{ __('index.select_loan_type') }}', '', placeholderSelected, placeholderSelected);
                $('#loan_type_id').append(placeholderOpt);

                if (response.types && response.types.length > 0) {
                    response.types.forEach(type => {
                        var opt = new Option(type.name, type.id, false, type.id == loanTypeId);
                        $('#loan_type_id').append(opt);
                    });
                } else {
                    var noTypeOpt = new Option('{{ __("index.loan_type_not_found") }}', '', false, false);
                    $('#loan_type_id').append(noTypeOpt);
                }
                $('#loan_type_id').trigger('change');

                // Clear departments select
                $('#department_id').empty();
                let deptPlaceholderSelected = !departmentId;
                var deptPlaceholderOpt = new Option('{{ __('index.select_department') }}', '', deptPlaceholderSelected, deptPlaceholderSelected);
                $('#department_id').append(deptPlaceholderOpt);

                if (response.departments && response.departments.length > 0) {
                    response.departments.forEach(dept => {
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

            let employeeId = "{{ $loanDetail->employee_id ?? old('employee_id') ?? '' }}";

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

        // Function to populate readonly fields based on selected loan type
        const populateLoanTypeDetails = (loanTypeId) => {
            const selectedType = types.find(t => t.id == loanTypeId);
            if (selectedType) {
                $('#interest_rate').val(selectedType.interest_rate);
                $('#interest_type').val(selectedType.interest_type);
                $('#term').val(selectedType.term);
            } else {
                $('#interest_rate').val('');
                $('#interest_type').val('');
                $('#term').val('');
            }
            calculateInstallments();
        };

        // Function to calculate monthly installment and total repayment
        const calculateInstallments = () => {
            const loanAmount = parseFloat($('#loan_amount').val()) || 0;
            const loanTypeId = $('#loan_type_id').val();
            if (!loanAmount || !loanTypeId || loanAmount <= 0) {
                $('#monthly_installment').val('');
                $('#repayment_amount').val('');
                return;
            }
            const selectedType = types.find(t => t.id == loanTypeId);
            if (!selectedType) {
                $('#monthly_installment').val('');
                $('#repayment_amount').val('');
                return;
            }
            const principal = loanAmount;
            const interestRate = parseFloat(selectedType.interest_rate) || 0;
            const tenureMonths = parseInt(selectedType.term) || 0;
            const interestType = selectedType.interest_type || 'fixed';
            if (tenureMonths <= 0) {
                $('#monthly_installment').val('');
                $('#repayment_amount').val('');
                return;
            }
            let emi = 0;
            let totalRepayment = 0;
            const monthlyRate = interestRate / 12 / 100;
            const tenureYears = tenureMonths / 12;

            if (interestType === 'fixed') {
                // Flat rate: Total interest upfront, divide equally
                const totalInterest = principal * interestRate / 100 * tenureYears;
                const monthlyInterest = totalInterest / tenureMonths;
                const monthlyPrincipal = principal / tenureMonths;
                emi = ((monthlyPrincipal + monthlyInterest) * 100) / 100; // Round to 2 decimals
                totalRepayment = principal + totalInterest;
            } else { // 'declining' - Reducing balance with fixed EMI
                // EMI formula
                const emiNumerator = principal * monthlyRate * Math.pow(1 + monthlyRate, tenureMonths);
                const emiDenominator = Math.pow(1 + monthlyRate, tenureMonths) - 1;
                emi = ((emiNumerator / emiDenominator) * 100) / 100; // Round to 2 decimals
                totalRepayment = (emi * tenureMonths * 100) / 100;
            }

            $('#monthly_installment').val(emi.toFixed(2));
            $('#repayment_amount').val(totalRepayment.toFixed(2));
        };

        $('#loan_type_id').on('change', function() {
            const loanTypeId = $(this).val();
            populateLoanTypeDetails(loanTypeId);
        });

        $('#loan_amount').on('input', calculateInstallments);

        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};

        if (isAdmin) {
            $('#branch_id').on('change', function() {
                const branchId = $(this).val();
                loadLoanTypesAndDepartments(branchId);
                $('#department_id').val('').trigger('change');
                $('#employee_id').val('').trigger('change');
            });
            if ($('#branch_id').val()) {
                loadLoanTypesAndDepartments($('#branch_id').val());
            }
        } else if (defaultBranchId) {
            loadLoanTypesAndDepartments(defaultBranchId);
        }

        $('#department_id').on('change', function() {
            const departmentId = $(this).val();
            loadEmployees(departmentId);
            $('#employee_id').val('').trigger('change');
        });

        if ("{{ $loanDetail->department_id ?? '' }}") {
            loadEmployees("{{ $loanDetail->department_id ?? '' }}");
        }

        // Initial calculation for editing mode
        if ("{{ isset($loanDetail) && $loanDetail->loan_type_id }}") {
            setTimeout(calculateInstallments, 500); // Delay to ensure selects are populated
        }

        tinymce.init({
            selector: '#tinymceDescription',
            height: 200,
            menubar: false,
            plugins: ['advlist autolink lists link image charmap print preview anchor', 'searchreplace visualblocks code fullscreen', 'insertdatetime media table paste code help wordcount'],
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | removeformat | help',
        });
    });


    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
        $('.repayment_from').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            disableAfter: "2089-12-30",
        });
    @endif
</script>
