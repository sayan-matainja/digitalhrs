<script src="{{asset('assets/js/imageuploadify.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

<script>
    $(document).ready(function (e) {
        $('#branch_id').select2();
        $('#department_id').select2();
        $('#employee_id').select2();
        $('#status').select2();
        $('#month').select2();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });

        $("#image-uploadify").imageuploadify();



        $('body').on('click', '.delete', function (event) {
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


        $('#status').change(function(e) {
            let status = $(this).val();
            handleStatusChange(status);
        });

        // Initial state setup based on current status
        handleStatusChange($('#status').val());




        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";
        const departmentId = "{{ $filterParameters['department_id']  ?? '' }}";
        const employeeId = "{{ $filterParameters['employee_id']  ?? '' }}";


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
    function handleStatusChange(status) {
        const releaseAmount = $('.releaseAmount');
        const reason = $('.reason');
        const amountReleased = $('.amountReleased');
        const remark = $('.remark');

        // Reset all fields first
        releaseAmount.hide();
        reason.hide();

        // Remove required attributes
        amountReleased.prop('required', false);
        remark.prop('required', false);

        // Clear values when hiding
        if (status !== 'approved') {
            amountReleased.val('');
        }
        if (status === 'processing') {
            remark.val('');
        }

        // Set visibility and requirements based on status
        switch(status) {
            case 'approved':
                releaseAmount.show();
                reason.show();
                amountReleased.prop('required', true);
                remark.prop('required', true);
                break;

            case 'rejected':
                reason.show();
                remark.prop('required', true);
                break;

            case 'processing':
                // All fields are hidden and not required by default
                break;
        }
    }
</script>
