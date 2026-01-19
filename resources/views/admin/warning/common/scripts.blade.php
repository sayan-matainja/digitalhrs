<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $("#employee_id").select2({
            placeholder: "{{__('index.select_employee')}}"
        });
        $("#department_id").select2({
            placeholder: "{{ __('index.select_department') }}"
        });


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('body').on('click', '.deleteWarning', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_confirmation') }}',
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

        $('.nepali_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });


    });

    tinymce.init({
        selector: '#tinymceExample',
        height: 200,
    });



    $(document).ready(function () {
        // Define variables
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";

        // Ensure filter parameters are arrays and normalize to strings
        const filterDepartmentIds = Array.isArray(JSON.parse('{!! json_encode($filterParameters['department_id'] ?? []) !!}'))
            ? JSON.parse('{!! json_encode($filterParameters['department_id'] ?? []) !!}').map(String)
            : [String(JSON.parse('{!! json_encode($filterParameters['department_id'] ?? []) !!}'))].filter(Boolean);
        const filterEmployeeIds = Array.isArray(JSON.parse('{!! json_encode($filterParameters['employee_id'] ?? []) !!}'))
            ? JSON.parse('{!! json_encode($filterParameters['employee_id'] ?? []) !!}').map(String)
            : [String(JSON.parse('{!! json_encode($filterParameters['employee_id'] ?? []) !!}'))].filter(Boolean);

        const formDepartmentIds = {!! isset($departmentIds) ? json_encode($departmentIds) : '[]' !!}.map(String);
        const formEmployeeIds = {!! isset($employeeIds) ? json_encode($employeeIds) : '[]' !!}.map(String);

        const departmentIds = filterDepartmentIds.length > 0 ? filterDepartmentIds : formDepartmentIds;
        let employeeIds = filterEmployeeIds.length > 0 ? filterEmployeeIds : formEmployeeIds; // Make this mutable

        const preloadDepartments = async (selectedBranchId) => {
            if (!selectedBranchId) return;

            try {
                $('#department_id').empty();
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                if (!response || !response.data || response.data.length === 0) {
                    $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                    return;
                }

                response.data.forEach(data => {
                    const isSelected = departmentIds.includes(String(data.id));
                    $('#department_id').append(`
                    <option value="${data.id}" ${isSelected ? "selected" : ""}>
                        ${data.dept_name}
                    </option>
                `);
                });

                $('#department_id').trigger('change');
            } catch (error) {
                $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            }
        };

        const preloadEmployees = async () => {
            const selectedDepartments = $('#department_id').val() || [];
            if (selectedDepartments.length === 0) {
                $('#employee_id').empty().append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                return;
            }

            // Store current employee selections before clearing
            const currentEmployeeSelections = $('#employee_id').val() || [];

            try {
                const response = await fetch('{{ route('admin.employees.fetchByDepartment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ department_ids: selectedDepartments }),
                });

                const data = await response.json();
                $('#employee_id').empty(); // Clear options every time

                if (!data || data.length === 0) {
                    $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    return;
                }

                // Repopulate with employees, preserving valid selections
                data.forEach(employee => {
                    const employeeIdStr = String(employee.id);
                    const isSelected = currentEmployeeSelections.includes(employeeIdStr) || employeeIds.includes(employeeIdStr);
                    $('#employee_id').append(`
                    <option value="${employee.id}" ${isSelected ? "selected" : ""}>
                        ${employee.name}
                    </option>
                `);
                });

                // Update employeeIds to reflect current selections
                employeeIds = $('#employee_id').val() || [];
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
                    await preloadDepartments(newBranchId);
                    await preloadEmployees();
                });

                if (selectedBranchId) {
                    await preloadDepartments(selectedBranchId);
                    await preloadEmployees();
                }
            } else {
                selectedBranchId = defaultBranchId;
                if (selectedBranchId) {
                    await preloadDepartments(selectedBranchId);
                    await preloadEmployees();
                }
            }

            $('#department_id').on('change', preloadEmployees);
        };

        // Initialize everything
        initializeDropdowns();
    });


    document.getElementById('withNotification').addEventListener('click', function (event) {

        document.getElementById('notification').value = 1;
    });

</script>
