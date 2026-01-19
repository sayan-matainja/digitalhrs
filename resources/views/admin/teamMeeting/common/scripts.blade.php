<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

    $(document).ready(function () {


        $("#team_meeting").select2({
            placeholder: "{{ __('index.select_meeting_participants') }}"
        });

        $("#department_id").select2({
                placeholder: "{{ __('index.select_department') }}"
            });


        $('.meetingDate').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            disableAfter: "2089-12-30",
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{__('index.delete_team_meeting_confirmation')}}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('.removeImage').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{ __('index.image_delete_confirmation') }}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        $('body').on('click', '.showMeetingDescription', function (event) {
            event.preventDefault();

            let url = $(this).data('href');

            $.get(url, function (data) {
                $('.meetingTitle').html('Meeting Detail');
                $('.title').text(data.data.title);
                $('.date').text(data.data.meeting_date);
                $('.time').text(data.data.time);
                $('.venue').text(data.data.venue);
                $('.publish_date').text(data.data.meeting_published_at);
                $('.description').text(data.data.description);
                $('.creator').text(data.data.creator);
                $('.image').attr('src', data.data.image);

                $('#meetingDetail').modal('show');
            })
        });

        $('.reset').click(function (event) {
            event.preventDefault();
            $('#participator').val('');
            $('.fromDate').val('');
            $('.toDate').val('');
        });

    });

    $(document).ready(function () {

        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";

        // Ensure filter parameters are arrays and normalize to strings
        const filterDepartmentIds = Array.isArray(JSON.parse('{!! json_encode($filterParameters['department_id'] ?? []) !!}'))
            ? JSON.parse('{!! json_encode($filterParameters['department_id'] ?? []) !!}').map(String)
            : [String(JSON.parse('{!! json_encode($filterParameters['department_id'] ?? []) !!}'))].filter(Boolean);
        const filterEmployeeIds = Array.isArray(JSON.parse('{!! json_encode($filterParameters['employee_id'] ?? []) !!}'))
            ? JSON.parse('{!! json_encode($filterParameters['participator'] ?? []) !!}').map(String)
            : [String(JSON.parse('{!! json_encode($filterParameters['participator'] ?? []) !!}'))].filter(Boolean);

        const formDepartmentIds = {!! isset($departmentIds) ? json_encode($departmentIds) : '[]' !!}.map(String);
        const formEmployeeIds = {!! isset($participatorIds) ? json_encode($participatorIds) : '[]' !!}.map(String);

        const departmentIds = filterDepartmentIds.length > 0 ? filterDepartmentIds : formDepartmentIds;
        let employeeIds = filterEmployeeIds.length > 0 ? filterEmployeeIds : formEmployeeIds; // Make this mutable
        // Common function to load departments
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

        // Common function to load and merge employees
        const preloadEmployees = async () => {
            const selectedDepartments = $('#department_id').val() || [];
            if (selectedDepartments.length === 0) {
                $('#team_meeting').empty().append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                return;
            }

            // Store current employee selections before clearing
            const currentEmployeeSelections = $('#team_meeting').val() || [];

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
                $('#team_meeting').empty(); // Clear options every time

                if (!data || data.length === 0) {
                    $('#team_meeting').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    return;
                }

                // Repopulate with employees, preserving valid selections
                data.forEach(employee => {
                    const employeeIdStr = String(employee.id);
                    const isSelected = currentEmployeeSelections.includes(employeeIdStr) || employeeIds.includes(employeeIdStr);
                    $('#team_meeting').append(`
                    <option value="${employee.id}" ${isSelected ? "selected" : ""}>
                        ${employee.name}
                    </option>
                `);
                });

                // Update employeeIds to reflect current selections
                employeeIds = $('#team_meeting').val() || [];
            } catch (error) {
                $('#team_meeting').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
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

    document.getElementById('withTeamNotification').addEventListener('click', function (event) {

        document.getElementById('teamNotification').value = 1;
    });
</script>
