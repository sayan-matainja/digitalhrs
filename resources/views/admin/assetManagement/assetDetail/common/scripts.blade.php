<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $("#branch_id").select2({});
        $("#type").select2({});
        $("#assigned_to").select2({});


        $('#image').change(function(){
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
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.change_availability_status') ',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
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


    });

    $(document).ready(function () {
        // Define variables
        const branchId = "{{ $filterParameters['branch_id'] ?? '' }}";
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const filterTypeId = "{{ $filterParameters['type_id'] ?? '' }}";

        const loadClientAndUsers = async (selectedBranchId, typeIdToSelect = filterTypeId) => {
            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-asset-data') }}/${selectedBranchId}`,
                });

                // Debugging: Log the response and typeIdToSelect
                console.log('Branch ID:', selectedBranchId);
                console.log('Type ID to Select:', typeIdToSelect);
                console.log('Response Types:', response.types);

                // Clear existing options
                $('#type').empty();

                // Populate types
                if (response.types && response.types.length > 0) {
                    // Add default "Select" option only if no type is pre-selected
                    if (!typeIdToSelect) {
                        $('#type').append('<option value="" disabled selected>{{ __('index.select_asset_type') }}</option>');
                    }

                    response.types.forEach(type => {
                        // Ensure type.id and typeIdToSelect are compared as strings
                        const isSelected = String(type.id) === String(typeIdToSelect) ? 'selected' : '';
                        $('#type').append(
                            `<option value="${type.id}" ${isSelected}>${type.name}</option>`
                        );
                    });
                } else {
                    $('#type').append('<option disabled>{{ __("index.asset_type_not_found") }}</option>');
                }

                // Double-check selection after population
                if (typeIdToSelect) {
                    $('#type').val(typeIdToSelect); // Force selection
                }
            } catch (error) {
                console.error('Error loading types:', error);
                $('#type').empty().append('<option disabled>{{ __("index.error_loading_asset_types") }}</option>');
            }
        };

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId;
                $('#branch_id').change(async () => {
                    const newBranchId = $('#branch_id').val();
                    await loadClientAndUsers(newBranchId, filterTypeId);
                    $('#assigned_to').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                });
            } else {
                selectedBranchId = defaultBranchId;
            }

            if (selectedBranchId) {
                await loadClientAndUsers(selectedBranchId, filterTypeId);
            }
        };

        // Ensure initialization happens after DOM is ready
        initializeDropdowns();
    });


    $(document).ready(function () {
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};

        // Function to load departments
        const loadDepartments = async (branchId, targetSelectId = 'assignment_department_id') => {
            if (!branchId) return;

            const $departmentSelect = $(`#${targetSelectId}`);
            $departmentSelect.prop('disabled', true).empty()
                .append('<option value="">{{ __("index.select_department") }}</option>');

            try {
                const response = await $.ajax({
                    url: `{{ url('admin/departments/get-All-Departments') }}/${branchId}`,
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (response?.data?.length) {
                    response.data.forEach(dept => {
                        $departmentSelect.append(
                            `<option value="${dept.id}">${dept.dept_name}</option>`
                        );
                    });
                } else {
                    $departmentSelect.append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                }
            } catch (error) {
                console.error('Error loading departments:', error);
                $departmentSelect.append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            } finally {
                $departmentSelect.prop('disabled', false);
            }
        };

        // Function to load employees
        const loadEmployees = async (departmentId, targetSelectId = 'assignment_user_id') => {
            if (!departmentId) return;

            const $employeeSelect = $(`#${targetSelectId}`);
            $employeeSelect.prop('disabled', true).empty()
                .append('<option value="">{{ __("index.select_employee") }}</option>');

            try {
                const response = await $.ajax({
                    url: `{{ url('admin/employees/get-all-employees') }}/${departmentId}`,
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (response?.data?.length) {
                    response.data.forEach(user => {
                        $employeeSelect.append(
                            `<option value="${user.id}">${user.name}</option>`
                        );
                    });
                } else {
                    $employeeSelect.append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                }
            } catch (error) {
                console.error('Error loading employees:', error);
                $employeeSelect.append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
            } finally {
                $employeeSelect.prop('disabled', false);
            }
        };

        // Initialize modal
        $('.assignAsset').on('click', async function (e) {
            e.preventDefault();

            const $button = $(this);
            const assetId = $button.data('id');
            const branchId = $button.data('branch-id');
            const saveUrl = $button.data('href');

            // Reset form
            $('#assetAssignmentForm')[0].reset();
            $('#assignment_asset_id').val(assetId);
            $('#assignment_branch_id').val(branchId);
            $('#assetAssignmentForm').attr('action', saveUrl);
            $('#assigned_date').val('{{ date('Y-m-d') }}');

            // Clear and disable selects
            $('#assignment_department_id').empty()
                .append('<option value="">{{ __("index.select_department") }}</option>')
                .prop('disabled', true);
            $('#assignment_user_id').empty()
                .append('<option value="">{{ __("index.select_employee") }}</option>')
                .prop('disabled', true);

            // Load departments
            await loadDepartments(isAdmin ? branchId : defaultBranchId);

            $('#assetAssignmentModal').modal('show');
        });

        // Department change handler
        $('#assignment_department_id').on('change', async function() {
            const departmentId = $(this).val();
            $('#assignment_user_id').empty()
                .append('<option value="">{{ __("index.select_employee") }}</option>')
                .prop('disabled', true);
            await loadEmployees(departmentId);
        });

        // Form submission
        $('#assetAssignmentForm').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $submitButton = $form.find('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#assetAssignmentModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __("index.success") }}',
                        text: response.message || '{{ __("index.asset_assigned_success") }}',
                        confirmButtonText: '{{ __("index.ok") }}'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function (xhr) {
                    let errorMessage = '{{ __("index.asset_assignment_error") }}';
                    if (xhr.responseJSON?.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON?.message && xhr.responseJSON?.errors) {
                        // Handle validation errors
                        const errors = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        errorMessage = errors || xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("index.error") }}',
                        text: errorMessage,
                        confirmButtonText: '{{ __("index.ok") }}'
                    });
                },
                complete: function() {
                    $submitButton.prop('disabled', false);
                }
            });
        });
    });
</script>
