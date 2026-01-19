<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function () {

        $("#department_id").select2();
        $("#branch_id").select2();
        $("#employee_id").select2();
        $("#termination_type_id").select2();


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
                title: '{{ __('index.delete_confirmation') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        tinymce.init({
            selector: '#tinymceExample',
            height: 200,
        });


        $('body').on('click', '.terminationStatusUpdate', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            let status = $(this).data('status');
            let reason = $(this).data('reason');

            $('.modal-title').html('Leave Status Update');
            $('#updateTerminationStatus').attr('action', url)
            $('#status').val(status)
            $('#admin_remark').val(reason)

            $('#statusUpdate').modal('show');
        });


        $('.nepaliDate').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            disableAfter: "2089-12-30",
        });

    });


    // Enhanced JavaScript
    $(document).ready(function () {

        // Define variables
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";
        const terminationTypeId = "{{ $filterParameters['termination_type_id'] ?? ($terminationDetail->termination_type_id ?? '') }}";
        const departmentId = "{{ $filterParameters['department_id'] ?? ($terminationDetail->department_id ?? '') }}";
        const employeeId = "{{ $filterParameters['employee_id'] ?? ($terminationDetail->employee_id ?? '') }}";


        const loadTypeAndDepartments =  async (selectedBranchId) => {

            if (!selectedBranchId) return;


            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-termination-data') }}/${selectedBranchId}`,
                    timeout: 10000
                });

                // Termination Types
                $('#termination_type_id').empty().append(
                    `<option value="" disabled ${!terminationTypeId ? 'selected' : ''}>
                    {{ __('index.select_termination_type') }}
                    </option>`
                );
                if (response.types?.length) {
                    response.types.forEach(type => {
                        $('#termination_type_id').append(
                            `<option value="${type.id}" ${type.id == terminationTypeId ? 'selected' : ''}>
                            ${type.title}
                        </option>`
                        );
                    });
                }

                // Departments
                $('#department_id').empty().append(
                    `<option value="" disabled ${!departmentId ? 'selected' : ''}>
                    {{ __('index.select_department') }}
                    </option>`
                );
                if (response.departments?.length) {
                    response.departments.forEach(department => {
                        $('#department_id').append(
                            `<option value="${department.id}" ${department.id == departmentId ? 'selected' : ''}>
                            ${department.dept_name}
                        </option>`
                        );
                    });
                }

            } catch (error) {
                $('#termination_type_id').empty().append(
                    '<option disabled>{{ __("index.error_loading_termination_types") }}</option>'
                );
                $('#department_id').empty().append(
                    '<option disabled>{{ __("index.error_loading_department") }}</option>'
                );
            }
        };

        const loadEmployees = async () => {
            const selectedDepartmentId = $('#department_id').val();
            if (!selectedDepartmentId) return;

            try {
                $('#employee_id').empty().append(
                    `<option value="" disabled ${!employeeId ? 'selected' : ''}>
                    {{ __('index.select_employee') }}
                    </option>`
                );

                const response = await fetch(
                    `{{ url('admin/employees/get-all-employees') }}/${selectedDepartmentId}`,
                    {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }
                );

                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();

                if (data.data?.length) {
                    data.data.forEach(user => {
                        $('#employee_id').append(
                            `<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''}>
                            ${user.name}
                        </option>`
                        );
                    });
                }
            } catch (error) {
                console.error('Error loading employees:', error);
                $('#employee_id').empty().append(
                    '<option disabled>{{ __("index.error_loading_employees") }}</option>'
                );
            }
        };

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;

                $('#branch_id').on('change', async () => {
                    const newBranchId = $('#branch_id').val();
                    await loadTypeAndDepartments(newBranchId);
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
                    await loadTypeAndDepartments(selectedBranchId);
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
</script>
