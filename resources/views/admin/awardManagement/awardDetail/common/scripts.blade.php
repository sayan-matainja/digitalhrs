<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $("#department_id").select2();
        $("#branch_id").select2();
        $("#employee_id").select2();
        $("#award_type_id").select2();

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
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })


        $('.awarded_date').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });



        $('#attachment').change(function(){
            const input = document.getElementById('image');
            const preview = document.getElementById('image-preview');
            const file = input.files[0];
            const reader = new FileReader();
            reader.addEventListener('load', function() {
                preview.src = reader.result;
            });
            reader.readAsDataURL(file);
            $('#image-preview').removeClass('d-none')

        })

    });
    tinymce.init({
        selector: '#tinymceExample',
        height: 200,
    });

    $('document').ready(function () {
        // Define variables
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";
        const awardTypeId = "{{ $filterParameters['award_type_id'] ?? ($awardDetail->award_type_id ?? '') }}";
        const departmentId = "{{ $filterParameters['department_id'] ?? ($awardDetail->department_id ?? '') }}";
        const employeeId = "{{ $filterParameters['employee_id'] ?? ($awardDetail->employee_id ?? '') }}";

        const loadTypeAndDepartments = async (selectedBranchId) => {
            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-award-data') }}/${selectedBranchId}`,
                });

                // Clear existing options
                $('#award_type_id').empty();
                $('#department_id').empty();

                // Populate award types
                $('#award_type_id').append('<option disabled selected>{{ __("index.select_award_type") }}</option>');
                if (response.types && response.types.length > 0) {
                    response.types.forEach(type => {
                        $('#award_type_id').append(
                            `<option value="${type.id}" ${type.id == awardTypeId ? 'selected' : ''}>${type.title}</option>`
                        );
                    });
                } else {
                    $('#award_type_id').append('<option disabled>{{ __("index.award_type_not_found") }}</option>');
                }

                // Populate departments
                $('#department_id').append('<option disabled selected>{{ __("index.select_department") }}</option>');
                if (response.departments && response.departments.length > 0) {
                    response.departments.forEach(department => {
                        $('#department_id').append(
                            `<option value="${department.id}" ${department.id == departmentId ? 'selected' : ''}>${department.dept_name}</option>`
                        );
                    });
                } else {
                    $('#department_id').append('<option disabled>{{ __("index.no_department_found") }}</option>');
                }
            } catch (error) {
                $('#award_type_id').empty().append('<option disabled>{{ __("index.error_loading_award_types") }}</option>');
                $('#department_id').empty().append('<option disabled>{{ __("index.error_loading_department") }}</option>');
            }
        };

        const loadEmployees = async () => {
            const selectedDepartmentId = $('#department_id').val();
            if (!selectedDepartmentId) return;

            try {
                $('#employee_id').empty();
                $('#employee_id').append('<option disabled selected>{{ __("index.select_employee") }}</option>');

                const response = await fetch(`{{ url('admin/employees/get-all-employees') }}/${selectedDepartmentId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                });

                const data = await response.json();

                if (data.data && data.data.length > 0) {
                    data.data.forEach(user => {
                        $('#employee_id').append(
                            `<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''}>${user.name}</option>`
                        );
                    });
                } else {
                    $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                }
            } catch (error) {
                $('#employee_id').empty().append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
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
