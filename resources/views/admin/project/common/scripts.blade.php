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

        $("#filter").select2({
            placeholder: "{{ __('index.select_member') }}"
        });
        $("#employeeAdd").select2({
            placeholder: "{{ __('index.select_member') }}"
        });
        $("#projectLead").select2();
        $("#member").select2();



        $('.toggleStatus').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.confirm_status_change')',
                showDenyButton: true,
                confirmButtonText: '@lang('index.yes')',
                denyButtonText: '@lang('index.no')',
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                } else if (result.isDenied) {
                    // Handle denial case
                }
            })
        });

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.delete_project_detail')',
                showDenyButton: true,
                confirmButtonText: '@lang('index.yes')',
                denyButtonText: '@lang('index.no')',
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        $('.documentDelete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.delete_project_document')',
                showDenyButton: true,
                confirmButtonText: '@lang('index.yes')',
                denyButtonText: '@lang('index.no')',
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        $('.reset').click(function (event) {
            event.preventDefault();
            $('#projectName').val('');
            $('#status').val('');
            $('#priority').val('');
            $('#filter').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#filter").select2({
                placeholder: "@lang('index.search_by_member')"
            });
            $('#project_name').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#project_name").select2({
                placeholder: "@lang('index.search_by_project')"
            });
        });


    });

    $(document).ready(function () {
        const branchId = "{{ $filterParameters['branch_id'] ?? '' }}";
        const projectId = "{{ $filterParameters['project_name'] ?? '' }}";
        const memberIds = JSON.parse('{!! json_encode($filterParameters['members'] ?? []) !!}');
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};

        const loadProjects = async (selectedBranchId) => {
            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-task-data') }}/${selectedBranchId}`,
                });

                $('#project_name').empty();
                $('#project_name').append('<option value="" selected>{{ __("index.select_project") }}</option>');

                if (response.projects && response.projects.length > 0) {
                    response.projects.forEach(project => {
                        $('#project_name').append(
                            `<option value="${project.id}" ${project.id == projectId ? 'selected' : ''}>${project.name}</option>`
                        );
                    });
                } else {
                    $('#project_name').append('<option disabled>{{ __("index.project_not_found") }}</option>');
                }
            } catch (error) {
                $('#project_name').append('<option disabled>{{ __("index.error_loading_project") }}</option>');
            }
        };

        const preloadMembers = async () => {
            const selectedProject = $('#project_name').val();
            if (!selectedProject) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-project-member-data') }}/${selectedProject}`,
                });

                $('#filter').empty();

                if (!response?.members?.length) {
                    $('#filter').append('<option disabled>{{ __("index.employees_not_found") }}</option>');
                    return;
                }

                response.members.forEach(data => {
                    $('#filter').append(
                        `<option value="${data.id}" ${memberIds.includes(String(data.id)) ? 'selected' : ''}>
                        ${data.name}
                    </option>`
                    );
                });

                $('#filter').trigger('change');
            } catch (error) {
                console.error('Error loading members:', error);
                $('#filter').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
            }
        };

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId;
                $('#branch_id').change(async () => {
                    await loadProjects($('#branch_id').val());
                    await preloadMembers();
                });
            } else {
                selectedBranchId = defaultBranchId;
            }

            if (selectedBranchId) {
                await loadProjects(selectedBranchId);
                await preloadMembers();
            }
        };

        // Initialize
        initializeDropdowns();

        // Add project change listener
        $('#project_name').change(preloadMembers);
    });

</script>
