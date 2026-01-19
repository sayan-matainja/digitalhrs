<script>

    $(document).ready(function () {



        $("#team_meeting").select2({
            placeholder: "Select Meeting Participants"
        });

        $("#department_id").select2();





        $('#meetingDate').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            disableAfter: "2089-12-30",
        });
    });
    document.getElementById('withTeamNotification').addEventListener('click', function () {

        document.getElementById('teamNotification').value = "1";
    });


    $(document).ready(function () {
        const departmentIds = {!! isset($departmentIds) ? json_encode($departmentIds) : '[]' !!};
        const employeeIds = {!! isset($participatorIds) ? json_encode($participatorIds) : '[]' !!};

        const preloadDepartments = async () => {
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const selectedBranchId = isAdmin ? $('#branch_id').val() : defaultBranchId;

            if (!selectedBranchId) return;

            try {
                // Only clear options if not preloaded
                if ($('#department_id option').length === 0) {
                    $('#department_id').empty();
                }

                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                if (!response || !response.data || response.data.length == 0) {
                    $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                    return;
                }

                response.data.forEach(data => {
                    const isSelected = departmentIds.includes(data.id);
                    if (!$('#department_id option[value="' + data.id + '"]').length) {
                        $('#department_id').append(`
                        <option value="${data.id}" ${isSelected ? "selected" : ""}>
                            ${data.dept_name}
                        </option>
                    `);
                    }
                });

                $('#department_id').trigger('change');
            } catch (error) {
                console.error('Error loading departments:', error);
                $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            }
        };

        const preloadEmployees = async () => {
            const selectedDepartments = $('#department_id').val() || [];
            const previouslySelectedEmployees = $('#team_meeting').val() || []; // Keep track of currently selected employees

            if (selectedDepartments.length === 0) {
                if ($('#team_meeting option').length === 0) {
                    $('#team_meeting').empty().append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                }
                return;
            }

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

                if (!data || data.length === 0) {
                    $('#team_meeting').empty().append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    return;
                }

                const currentOptions = Array.from($('#team_meeting option')).map(option => option.value);

                // Append new employees from fetched data
                data.forEach(employee => {
                    const isSelected = employeeIds.includes(employee.id) || previouslySelectedEmployees.includes(employee.id.toString());
                    if (!currentOptions.includes(employee.id.toString())) {
                        $('#team_meeting').append(`
                        <option value="${employee.id}" ${isSelected ? "selected" : ""}>
                            ${employee.name}
                        </option>
                    `);
                    }
                });

                // Remove employees that are not in the fetched data
                $('#team_meeting option').each(function () {
                    const employeeId = $(this).val();
                    if (!data.find(employee => employee.id.toString() === employeeId)) {
                        $(this).remove();
                    }
                });
            } catch (error) {
                console.error('Error fetching employees:', error);
                $('#team_meeting').empty().append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
            }
        };

        // Ensure data is preloaded on page load
        preloadDepartments().then(preloadEmployees);

        // Update departments and employees when branch changes


        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        if (isAdmin) {
            $('#branch_id').change(function () {
                preloadDepartments().then(preloadEmployees);
            });
        } else {
            preloadDepartments().then(preloadEmployees); // Load directly for regular users
        }

        // Update employees when departments change
        $('#department_id').change(preloadEmployees);


    });


    document.getElementById('withTeamNotification').addEventListener('click', function (event) {

        document.getElementById('teamNotification').value = 1;
    });
</script>
