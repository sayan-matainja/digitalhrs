<script>
    $(document).ready(function() {
        // Initialize Select2
        $("#salaryComponent").select2({
            placeholder: "{{__('index.choose_salary_component')}}"
        });

        $("#branch_id").select2({
            placeholder: "{{ __("index.select_branch") }}"
        });

        $("#department_id").select2({
            placeholder: "{{ __("index.select_department") }}"
        });

        $("#employee_id").select2({
            placeholder: "{{ __("index.select_employee") }}"
        });

        const modal = document.getElementById('salaryGroupModal');
        if (!modal) {
            return;
        }

        const modalTitle = modal.querySelector('.modal-title');
        if (!modalTitle) {
            return;
        }

        const form = modal.querySelector('form');
        if (!form) {
            return;
        }

        const submitButton = modal.querySelector('.modal-footer button[type="submit"]');
        if (!submitButton) {
            return;
        }

        const submitIcon = submitButton.querySelector('#submit-icon');
        if (!submitIcon) {
            return;
        }

        const submitText = submitButton.querySelector('#submit-text');
        if (!submitText) {
            return;
        }

        // Handle modal show event
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const url = button.getAttribute('data-url');

            // Clear previous error messages
            form.querySelectorAll('.text-danger').forEach(el => el.remove());

            if (action === 'create') {
                modalTitle.textContent = '{{ __('index.add_salary_group') }}';
                form.action = url;
                form.querySelector('[name="_method"]').value = 'POST';
                form.reset();
                $('#salaryComponent').val(null).trigger('change');
                $('#department_id').val(null).trigger('change');
                $('#employee_id').val(null).trigger('change');
                submitIcon.setAttribute('data-feather', 'plus');
                submitText.textContent = '{{ __('index.create') }} {{ __('index.salary_group') }}';

                // Load departments for create mode
                const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
                if (isAdmin) {
                    $('#branch_id').val(null).trigger('change');
                    $('#department_id').html('<option disabled>{{ __("index.select_department") }}</option>').trigger('change');
                    $('#employee_id').html('<option disabled>{{ __("index.select_employee") }}</option>').trigger('change');
                } else {
                    loadDepartments();
                }
            } else if (action === 'edit') {
                modalTitle.textContent = '{{ __('index.edit_salary_group') }}';
                form.action = url;
                form.querySelector('[name="_method"]').value = 'PUT';

                // Populate form fields
                form.querySelector('#name').value = button.getAttribute('data-name') || '';
                const componentIds = button.getAttribute('data-salary-component-ids') ? JSON.parse(button.getAttribute('data-salary-component-ids')).map(id => id.toString()) : [];
                const employeeIds = button.getAttribute('data-employee-ids') ? JSON.parse(button.getAttribute('data-employee-ids')).map(id => id.toString()) : [];
                const departmentIds = button.getAttribute('data-department-ids') ? JSON.parse(button.getAttribute('data-department-ids')).map(id => id.toString()) : [];

                $('#salaryComponent').val(componentIds).trigger('change');
                $('#department_id').val(departmentIds).trigger('change');

                // Load departments and employees for edit mode
                const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
                if (isAdmin) {
                    const branchId = button.getAttribute('data-branch-id') || null;
                    $('#branch_id').val(branchId).trigger('change');
                    loadDepartments(branchId).then(() => {
                        $('#department_id').val(departmentIds).trigger('change');
                        loadEmployees(departmentIds).then(() => {
                            $('#employee_id').val(employeeIds).trigger('change');
                        });
                    });
                } else {
                    loadDepartments().then(() => {
                        $('#department_id').val(departmentIds).trigger('change');
                        loadEmployees(departmentIds).then(() => {
                            $('#employee_id').val(employeeIds).trigger('change');
                        });
                    });
                }

                submitIcon.setAttribute('data-feather', 'edit-2');
                submitText.textContent = '{{ __('index.update') }} {{ __('index.salary_group') }}';
            }

            // Re-render feather icons
            feather.replace();
        });

        // Handle form submission via AJAX
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            const method = form.querySelector('[name="_method"]').value;

            // Clear previous error messages
            form.querySelectorAll('.text-danger').forEach(el => el.remove());

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        if (data.errors) {
                            for (const field in data.errors) {
                                const input = form.querySelector(`[name="${field}"], [name="${field}[]"]`);
                                if (input) {
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'text-danger';
                                    errorDiv.textContent = data.errors[field].join(', ');
                                    input.parentElement.appendChild(errorDiv);
                                }
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'An error occurred',
                                showConfirmButton: true
                            });
                        }
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while submitting the form',
                        showConfirmButton: true
                    });
                });
        });

        // Clear Select2 when modal is hidden
        modal.addEventListener('hidden.bs.modal', function () {
            form.reset();
            $('#salaryComponent').val(null).trigger('change');
            $('#department_id').val(null).trigger('change');
            $('#employee_id').val(null).trigger('change');
            form.querySelectorAll('.text-danger').forEach(el => el.remove());
        });

        // Load departments based on branch_id
        const loadDepartments = async (branchId) => {
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const selectedBranchId = branchId || (isAdmin ? $('#branch_id').val() : defaultBranchId);

            if (!selectedBranchId) {
                $('#department_id').html('<option disabled>{{ __("index.select_department") }}</option>').trigger('change');
                $('#employee_id').html('<option disabled>{{ __("index.select_employee") }}</option>').trigger('change');
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
                        $('#department_id').append(
                            `<option value="${department.id}">${department.dept_name}</option>`
                        );
                    });
                } else {
                    $('#department_id').append('<option disabled>{{ __("index.no_department_found") }}</option>');
                }
                $('#department_id').trigger('change');
            } catch (error) {
                $('#department_id').html('<option disabled>{{ __("index.error_loading_department") }}</option>').trigger('change');
                $('#employee_id').html('<option disabled>{{ __("index.select_employee") }}</option>').trigger('change');
            }
        };

        // Load employees based on selected department_ids
        const loadEmployees = async (departmentIds) => {
            const selectedDepartmentIds = departmentIds || ($('#department_id').val() || []);
            if (!selectedDepartmentIds.length) {
                $('#employee_id').html('<option disabled>{{ __("index.select_employee") }}</option>').trigger('change');
                return;
            }

            // Preserve currently selected employee IDs
            const currentEmployeeIds = $('#employee_id').val() || [];

            try {
                const response = await fetch(`{{ url('admin/employees/fetch-employees-by-departments') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ department_ids: selectedDepartmentIds }),
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();
                $('#employee_id').empty();


                // Check if data is an array and has length > 0
                if (Array.isArray(data) && data.length > 0) {
                    const uniqueEmployees = new Map();
                    const validEmployeeIds = new Set();

                    data.forEach(user => {
                        if (!uniqueEmployees.has(user.id)) {
                            uniqueEmployees.set(user.id, user);
                            validEmployeeIds.add(String(user.id));
                            $('#employee_id').append(`<option value="${user.id}">${user.name}</option>`);
                        }
                    });

                    // Restore only valid employee selections
                    const validSelections = currentEmployeeIds.filter(id => validEmployeeIds.has(String(id)));
                    $('#employee_id').val(validSelections).trigger('change');
                } else {
                    $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>').trigger('change');
                }
            } catch (error) {
                $('#employee_id').html('<option disabled>{{ __("index.error_loading_employees") }}</option>').trigger('change');
            }
        };

        // Event listeners
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        if (isAdmin) {
            $('#branch_id').on('change', function() {
                loadDepartments($(this).val());
            });
        } else {
            loadDepartments();
        }

        $('#department_id').on('change', function() {
            loadEmployees();
        });





        // Handle toggle status
        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: `{{ __('index.change_status_confirm') }}`,
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                } else if (result.isDenied) {
                    (status === 0) ? $(this).prop('checked', true) : $(this).prop('checked', false);
                }
            });
        });

        // Handle delete
        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{ __('index.delete_confirm_salary_group') }}`,
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
</script>
