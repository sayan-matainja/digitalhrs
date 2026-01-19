<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>
<script src="{{asset('assets/js/imageuploadify.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>


<script>
    $(document).ready(function () {


        $('.errorClient').hide();

        $("#branch_id").select2({
            placeholder: "@lang('index.search_by_member')"
        });

        $("#member").select2({
            placeholder: "@lang('index.search_by_member')"
        });

        $("#projectLead").select2({
            placeholder: "@lang('index.search_by_project')"
        });

        $("#employeeAdd").select2({
            placeholder: "@lang('index.add_employee_to_project')"
        });

        $("#filter").select2({
            placeholder: "@lang('index.search_by_member')"
        });

        $("#project_name").select2({
            placeholder: "@lang('index.search_by_project')"
        });

        $("#image-uploadify").imageuploadify();

        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });


        $('#client_form').on('submit', function (e) {
            e.preventDefault()
            let url = $(this).attr('action');
            let formData = new FormData(this);
            $.ajax({
                url: url,
                type: 'post',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
            }).done(function (response) {
                if (response.status_code == 200) {
                    $('#email').val('');
                    $('#clientName').val('');
                    $('#contact_no').val('');
                    $('#address').val('');
                    $('#country').val('');
                    $('#avatar').val('');
                    $('#client_id').append('<option value="' + response.data.id + '" selected>' + response.data.name + '</option>');
                    setTimeout(function () {
                            $('#addslider').modal('hide');
                        }, 600
                    );
                }
            }).error(function (error) {
                let errorMessage = error.responseJSON.message;
                $('#showErrorMessageResponse').removeClass('d-none');
                $('.errorClient').show();
                $('.errorClientMessage').text(errorMessage);
                $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
            });
        });

        $('.startDate').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            disableAfter: "2089-12-30",
        });

        $('.deadline').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            disableAfter: "2089-12-30",
        });
    });

    document.getElementById('withProjectNotification').addEventListener('click', function (event) {

        document.getElementById('projectNotification').value = 1;
    });

    $(document).ready(function () {
        const loadClientAndDepartments = async () => {
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const selectedBranchId = isAdmin ? $('#branch_id').val() : defaultBranchId;

            if (!selectedBranchId) {
                $('#department_ids').empty().append('<option disabled>{{ __("index.select_branch_first") }}</option>');
                $('#projectLead').empty().append('<option disabled>{{ __("index.select_branch_first") }}</option>');
                $('#member').empty().append('<option disabled>{{ __("index.select_branch_first") }}</option>');
                return;
            }

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-project-data') }}/${selectedBranchId}`,
                });

                // Clear existing options
                $('#client_id').empty().append('<option value="" disabled selected>{{ __("index.select_client") }}</option>');
                $('#department_ids').empty();

                // Populate clients
                let clientId = "{{ $projectDetail->client_id ?? old('client_id') ?? '' }}";
                if (response.clients && response.clients.length > 0) {
                    response.clients.forEach(client => {
                        $('#client_id').append(
                            `<option value="${client.id}" ${client.id == clientId ? 'selected' : ''}>${client.name}</option>`
                        );
                    });
                } else {
                    $('#client_id').append('<option disabled>{{ __("index.no_clients_found") }}</option>');
                }

                // Populate departments
                let departmentIds = @json($projectDetail->department_ids ?? old('department_ids', []));

                if (response.departments && response.departments.length > 0) {
                    response.departments.forEach(data => {
                        const isSelected = departmentIds.includes(String(data.id));
                        $('#department_ids').append(
                            `<option value="${data.id}" ${isSelected ? "selected" : ""}>${data.dept_name}</option>`
                        );
                    });
                } else {
                    $('#department_ids').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                }

                // Trigger employee loading after departments are populated
                await preloadEmployees();

            } catch (error) {
                console.error('Error loading data:', error);
                $('#client_id').append('<option disabled>{{ __("index.error_loading_clients") }}</option>');
                $('#department_ids').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                $('#projectLead').append('<option disabled>{{ __("index.error_loading_users") }}</option>');
                $('#member').append('<option disabled>{{ __("index.error_loading_users") }}</option>');
            }
        };

        const preloadEmployees = async () => {
            const selectedDepartments = $('#department_ids').val() || [];

            console.log('Selected Departments:', selectedDepartments);

            if (!selectedDepartments.length) {
                $('#projectLead').empty().append('<option disabled>{{ __("index.select_department_first") }}</option>');
                $('#member').empty().append('<option disabled>{{ __("index.select_department_first") }}</option>');
                return;
            }

            // Store current selections before clearing
            const currentLeaderIds = $('#projectLead').val() || [];
            const currentMemberIds = $('#member').val() || [];

            // Get pre-existing values from server (for edit mode)
            let initialLeaderIds = @json(isset($projectDetail) && !empty($leaderId) ? (array) $leaderId : []);
            let initialMemberIds = @json(isset($projectDetail) && !empty($memberId) ? (array) $memberId : []);

            // Normalize IDs to strings and filter out invalid values
            initialLeaderIds = initialLeaderIds
                .filter(id => id !== null && id !== undefined && id !== '')
                .map(String);
            initialMemberIds = initialMemberIds
                .filter(id => id !== null && id !== undefined && id !== '')
                .map(String);


            try {
                const response = await fetch('{{ route('admin.employees.fetchByDepartment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ department_ids: selectedDepartments }),
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Fetched Employees:', data);

                // Clear existing employee options
                $('#projectLead').empty();
                $('#member').empty();

                // Populate project leaders and members
                if (data && data.length > 0) {
                    data.forEach(user => {
                        const userId = String(user.id);
                        const isLeaderSelected = currentLeaderIds.includes(userId) || initialLeaderIds.includes(userId);
                        const isMemberSelected = currentMemberIds.includes(userId) || initialMemberIds.includes(userId);

                        $('#projectLead').append(
                            `<option value="${userId}" ${isLeaderSelected ? 'selected' : ''}>${user.name}</option>`
                        );
                        $('#member').append(
                            `<option value="${userId}" ${isMemberSelected ? 'selected' : ''}>${user.name}</option>`
                        );
                    });
                } else {
                    $('#projectLead').append('<option disabled>{{ __("index.no_users_found") }}</option>');
                    $('#member').append('<option disabled>{{ __("index.no_users_found") }}</option>');
                }

                // Trigger select2 to reflect changes
                $('#projectLead, #member').trigger('change');

            } catch (error) {
                console.error('Error loading employees:', error);
                $('#projectLead').append('<option disabled>{{ __("index.error_loading_users") }}</option>');
                $('#member').append('<option disabled>{{ __("index.error_loading_users") }}</option>');
            }
        };

        // Initialize select2 for better UX
        $('#department_ids, #projectLead, #member').select2({
            placeholder: "{{ __('index.select_option') }}",
            allowClear: true,
            width: '100%'
        });

        // Event listeners
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};

        if (isAdmin) {
            $('#branch_id').on('change', loadClientAndDepartments);
        }

        // Handle department changes
        $('#department_ids').on('change', preloadEmployees);

        // Initial load
        loadClientAndDepartments();
    });

</script>
