<script>
    $(document).ready(function () {
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
        $('#department_id').select2({
            placeholder: @json(__('index.select_department')),
            allowClear: true,
            width: '100%',
        });

        $('#employee_id').select2({
            placeholder: @json(__('index.select_employee')),
            allowClear: true,
            width: '100%'
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });



        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            var status = $(this).prop('checked') == true ? 1 : 0;
            var href = $(this).attr('href');
            Swal.fire({
                title: @json(__('index.change_status_confirm')),
                showDenyButton: true,
                confirmButtonText: @json(__('index.yes')),
                denyButtonText: @json(__('index.no')),
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                } else if (result.isDenied) {
                    (status == 0) ? $(this).prop('checked', true) : $(this).prop('checked', false);
                }
            });
        });

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: @json(__('index.delete_confirmation')),
                showDenyButton: true,
                confirmButtonText: @json(__('index.yes')),
                denyButtonText: @json(__('index.no')),
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });



        const departmentCache = new Map();
        const employeeCache = new Map();

        const loadDepartments = async (branchId) => {
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const selectedBranchId = branchId || (isAdmin ? $('#branch_id').val() : defaultBranchId);

            if (!selectedBranchId) {
                $('#department_id').html('<option disabled>{{ __("index.select_department") }}</option>').trigger('change');
                $('#employee_id').html('<option disabled>{{ __("index.select_employee") }}</option>').trigger('change');
                return;
            }

            // Check cache first
            if (departmentCache.has(selectedBranchId)) {
                const cachedData = departmentCache.get(selectedBranchId);
                let options = cachedData.map(department =>
                    `<option value="${department.id}">${department.dept_name}</option>`
                ).join('');
                $('#department_id').html(options).trigger('change');
                return;
            }

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                let options = '';
                if (response.data && response.data.length > 0) {
                    departmentCache.set(selectedBranchId, response.data); // Cache the data
                    options = response.data.map(department =>
                        `<option value="${department.id}">${department.dept_name}</option>`
                    ).join('');
                } else {
                    options = '<option disabled>{{ __("index.no_department_found") }}</option>';
                }
                $('#department_id').html(options).trigger('change');
            } catch (error) {
                console.error('Error loading departments:', error);
                $('#department_id').html('<option disabled>{{ __("index.error_loading_department") }}</option>').trigger('change');
                $('#employee_id').html('<option disabled>{{ __("index.select_employee") }}</option>').trigger('change');
            }
        };

        const loadEmployees = async (departmentIds) => {
            const selectedDepartmentIds = departmentIds || ($('#department_id').val() || []);
            if (!selectedDepartmentIds.length) {
                $('#employee_id').html('<option disabled>{{ __("index.select_employee") }}</option>').trigger('change');
                return;
            }

            const cacheKey = selectedDepartmentIds.sort().join('-'); // Create a unique key for department IDs
            if (employeeCache.has(cacheKey)) {
                const cachedData = employeeCache.get(cacheKey);
                let options = cachedData.map(user =>
                    `<option value="${user.id}">${user.name}</option>`
                ).join('');
                const validSelections = ($('#employee_id').val() || []).filter(id =>
                    cachedData.some(user => String(user.id) === String(id))
                );
                $('#employee_id').html(options).val(validSelections).trigger('change');
                return;
            }

            const currentEmployeeIds = $('#employee_id').val() || [];

            try {
                const response = await fetch(`{{ url('admin/bonus/get-user-department-data') }}`, {
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
                let options = '';
                const validEmployeeIds = new Set();

                if (data.status === 200 && data.employees && data.employees.length > 0) {
                    const uniqueEmployees = [];
                    const uniqueMap = new Map();
                    data.employees.forEach(user => {
                        if (!uniqueMap.has(user.id)) {
                            uniqueMap.set(user.id, user);
                            uniqueEmployees.push(user);
                            validEmployeeIds.add(String(user.id));
                            options += `<option value="${user.id}">${user.name}</option>`;
                        }
                    });

                    employeeCache.set(cacheKey, uniqueEmployees); // Cache the data
                    const validSelections = currentEmployeeIds.filter(id => validEmployeeIds.has(String(id)));
                    $('#employee_id').html(options).val(validSelections).trigger('change');
                } else {
                    options = '<option disabled>{{ __("index.no_employees_found") }}</option>';
                    $('#employee_id').html(options).trigger('change');
                }
            } catch (error) {
                console.error('Error loading employees:', error);
                $('#employee_id').html('<option disabled>{{ __("index.error_loading_employees") }}</option>').trigger('change');
            }
        };

        // Event listeners
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        if (isAdmin) {
            $('#branch_id').on('change', function() {
                loadDepartments($(this).val());
            }).trigger('change');
        } else {
            loadDepartments();
        }

        $('#department_id').on('change', function() {
            loadEmployees();
        });

        // Handle modal show to prefill data for edit mode
        $('#bonusModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var mode = button.data('mode');
            var modal = $(this);
            var form = $('#bonusForm');
            var submitButton = $('#submitBonus');

            // Clear previous validation errors
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();

            // Reset form and select2
            form[0].reset();
            $('#department_id').val(null).trigger('change');
            $('#employee_id').val(null).trigger('change');

            if (mode === 'create') {
                modal.find('.modal-title').text(@json(__('index.add_bonus')));
                form.attr('action', @json(route('admin.bonus.store')));
                $('#formMethod').val('POST');
                submitButton.find('i').attr('data-feather', 'plus');
                submitButton.find('span').text(@json(__('index.add')));
                feather.replace();
                if (isAdmin) {
                    loadDepartments($('#branch_id').val());
                } else {
                    loadDepartments();
                }
            } else if (mode === 'edit') {
                var bonusId = button.data('id');
                modal.find('.modal-title').text(@json(__('index.edit_bonus')));
                form.attr('action', @json(route('admin.bonus.update', ':id')).replace(':id', bonusId));
                $('#formMethod').val('PUT');
                submitButton.find('i').attr('data-feather', 'edit-2');
                submitButton.find('span').text(@json(__('index.update')));
                feather.replace();

                // Fetch bonus data via AJAX
                $.ajax({
                    url: @json(route('admin.bonus.edit', ':id')).replace(':id', bonusId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success === false) {
                            Swal.fire({
                                icon: 'error',
                                title: @json(__('index.error')),
                                text: response.message || @json(__('index.failed_to_load_bonus')),
                            });
                            modal.modal('hide');
                            return;
                        }

                        // Prefill form fields
                        $('#name').val(response.bonusDetail.title);
                        $('#value_type').val(response.bonusDetail.value_type).trigger('change');
                        $('#value').val(response.bonusDetail.value);
                        $('#applicable_month').val(response.bonusDetail.applicable_month).trigger('change');
                        $('#apply_for_all').prop('checked', response.bonusDetail.apply_for_all == 1);
                        if ($('#branch_id').length) {
                            $('#branch_id').val(response.bonusDetail.branch_id);
                        }
                        // Prefill departments & employees
                        const departmentIds = (response.departmentIds || []).map(String);
                        const employeeIds = (response.employeeIds || []).map(String);

                        // Populate departments
                        let deptOptions = response.departments.map(d =>
                            `<option value="${d.id}" ${departmentIds.includes(String(d.id)) ? 'selected' : ''}>${d.dept_name}</option>`
                        ).join('');
                        $('#department_id').html(deptOptions);

                        // Populate employees
                        let empOptions = response.employees.map(e =>
                            `<option value="${e.id}" ${employeeIds.includes(String(e.id)) ? 'selected' : ''}>${e.name}</option>`
                        ).join('');
                        $('#employee_id').html(empOptions);

                        // Handle value type max limit
                        if (response.bonusDetail.value_type !== 'fixed') {
                            setMaxLimitForComponentValue();
                        } else {
                            removeMaxLimitForComponentValue();
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: @json(__('index.error')),
                            text: xhr.responseJSON.message || @json(__('index.failed_to_load_bonus')),
                        });
                        modal.modal('hide');
                    }
                });
            }
        });

        // Form submission
        $('#bonusForm').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var action = form.attr('action');
            var method = $('#formMethod').val();
            var formData = form.serializeArray();

            // Ensure employee_id and department_id arrays are properly formatted
            var employeeIds = $('#employee_id').val() || [];
            var departmentIds = $('#department_id').val() || [];
            formData = formData.filter(item => item.name !== 'employee_id[]' && item.name !== 'department_id[]');
            employeeIds.forEach(id => formData.push({ name: 'employee_id[]', value: id }));
            departmentIds.forEach(id => formData.push({ name: 'department_id[]', value: id }));
            // force apply_for_all to always be included
            formData = formData.filter(item => item.name !== 'apply_for_all');
            formData.push({ name: 'apply_for_all', value: $('#apply_for_all').is(':checked') ? 1 : 0 });

            $.ajax({
                url: action,
                method: method === 'POST' ? 'POST' : 'PUT',
                data: formData,
                success: function (response) {
                    $('#bonusModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || (method === 'POST' ? @json(__('message.add_bonus')) : @json(__('message.update_bonus'))),
                        timer: 1500,
                        didClose: () => {
                            window.location.reload();
                        }
                    });
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        form.find('.is-invalid').removeClass('is-invalid');
                        form.find('.invalid-feedback').remove();
                        $.each(xhr.responseJSON.errors, function (key, value) {
                            let fieldName = key.includes('employee_id') ? 'employee_id' : key.includes('department_id') ? 'department_id' : key;
                            $(`[name="${fieldName}"]`).addClass('is-invalid');
                            $(`[name="${fieldName}"]`).after(`<div class="invalid-feedback">${value[0]}</div>`);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: @json(__('index.error')),
                            text: xhr.responseJSON.message || @json(__('index.error_occurred')),
                        });
                    }
                }
            });
        });

        // Value type change handler
        $('#value_type').change(function () {
            let valueType = $(this).val();
            if (valueType !== 'fixed') {
                setMaxLimitForComponentValue();
            } else {
                removeMaxLimitForComponentValue();
            }
        });

        function setMaxLimitForComponentValue() {
            let maxLimit = 100;
            $('#value').attr('max', maxLimit);
        }

        function removeMaxLimitForComponentValue() {
            $('#value').removeAttr('max');
        }

    });
</script>

