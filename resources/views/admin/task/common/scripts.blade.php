<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>
<script src="{{asset('assets/js/imageuploadify.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

<script>
    $(document).ready(function (e) {




        $("#taskMember").select2({
            placeholder: "@lang('index.task_member_placeholder')"
        });
        $("#assignedMember").select2({
            placeholder: "@lang('index.task_member_placeholder')"
        });

        $("#project").select2({
            placeholder: "@lang('index.project_placeholder')"
        });

        $("#filter").select2({
            placeholder: "@lang('index.search_by_member')"
        });

        $("#projectFilter").select2({
            placeholder: "@lang('index.project_filter_placeholder')"
        });

        $("#taskName").select2({
            placeholder: "@lang('index.task_name_placeholder')"
        });

        $("#image-uploadify").imageuploadify();

        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.formChecklist').hide();

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.change_task_status_confirm')',
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

        $('body').on('click', '#checklistToggle', function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') ? 1 : 0;
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.change_status_confirm')',
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

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.delete_task_detail_confirm')',
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

        $('body').on('click', '#delete', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.delete_confirm')'+title+'?',
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

        $('#createChecklist').click(function(e){
            $('.formChecklist').removeClass('d-none');
            let text = $(this).text();
            (text == '@lang('index.create_checklist_text')') ? $(this).text('@lang('index.close_checklist_text')') : $(this).text('@lang('index.create_checklist_text')');
            $('.formChecklist').toggle(500);
        })

        $('#addChecklist').on('click',function(event){
            event.preventDefault();
            let removeButton = '<div class="col-lg-2 col-md-2 removeButton mb-4">'
                +'<button type="button" class="btn btn-sm btn-danger remove" title="@lang('index.remove_checklist_title')" id="removeChecklist">@lang('index.remove_checklist_text')</button>'+
                '</div>';
            $(".checklist").first().clone().find("input").val("").end().append(removeButton).appendTo("#addTaskCheckList");
            $(".addButtonSection:last").remove();
        })

        $("#addTaskCheckList").on('click', '.remove', function(){
            $(this).closest(".checklist").remove();
        });

        $(".checklistAdd").click(function(e) {
            e.preventDefault();
            $('.formChecklist').removeClass('d-none');
            $('.formChecklist').show();
            $('html,body').animate({
                scrollTop: $('#taskAdd').offset().top - 100
            }, 600);
        });

        $('.reset').click(function(event){
            event.preventDefault();
            $('#taskName').val('');
            $('#status').val('');
            $('#priority').val('');
            $('#projectFilter').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#projectFilter").select2({
                placeholder: "@lang('index.project_filter_placeholder')"
            });
            $('#filter').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#filter").select2({
                placeholder: "@lang('index.filter_placeholder')"
            });
            $('#taskName').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#taskName").select2({
                placeholder: "@lang('index.task_name_placeholder')"
            });
        });




        // ajax for branch data
        // Variables for filter form
        const filterTaskId = "{{ $filterParameters['task_id'] ?? '' }}";
        const filterProjectId = "{{ $filterParameters['project_id'] ?? '' }}";
        const filterMemberIds = {!! isset($filterParameters['assigned_member']) ? json_encode($filterParameters['assigned_member']) : '[]' !!};

        // Variables for main form
        const mainProjectId = "{{ isset($taskDetail) ? $taskDetail->project_id : '' }}";
        const mainMemberIds = {!! isset($memberId) ? json_encode($memberId) : '[]' !!};

        // Use presence of #taskName to determine if it's the filter form
        const projectId = $('#taskName').length > 0 ? filterProjectId : mainProjectId;
        const memberIds = $('#taskName').length > 0 ? filterMemberIds : mainMemberIds;
        const taskId = $('#taskName').length > 0 ? filterTaskId : '';

        // Ensure memberIds are treated as strings
        const normalizedMemberIds = Array.isArray(memberIds) ? memberIds.map(String) : [];

        // Function to reset dependent select boxes
        const resetDependentSelects = () => {
            $('#project').empty().append('<option value="" disabled selected>{{ __("index.select_project") }}</option>');
            $('#taskMember').empty().select2({ data: [] }); // Reset Select2
            if ($('#taskName').length > 0) {
                $('#taskName').empty().append('<option value="" selected>@lang("index.search_by_task_name")</option>');
            }
        };

        const preloadProjects = async () => {
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const selectedBranchId = isAdmin ? $('#branch_id').val() : defaultBranchId;

            if (!selectedBranchId) {
                resetDependentSelects();
                return;
            }

            $('#project').prop('disabled', true).empty().append('<option disabled>Loading...</option>');
            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-task-data') }}/${selectedBranchId}`,
                });

                $('#project').empty().append('<option value="" disabled selected>{{ __("index.select_project") }}</option>');
                if (!response?.projects?.length) {
                    $('#project').append('<option disabled>{{ __("index.projects_not_found") }}</option>');
                } else {
                    response.projects.forEach(data => {
                        $('#project').append(`
                        <option value="${data.id}" ${projectId == data.id ? "selected" : ""}>
                            ${data.name}
                        </option>
                    `);
                    });
                    $('#project').trigger('change');
                }
                $('#project').prop('disabled', false);
            } catch (error) {
                console.error('Error loading projects:', error);
                $('#project').empty().append('<option disabled>{{ __("index.error_loading_project") }}</option>');
                $('#project').prop('disabled', false);
            }
        };

        const preloadMembers = async () => {
            const selectedProject = $('#project').val();
            if (!selectedProject) {
                $('#taskMember').empty().select2({ data: [] }); // Reset taskMember
                if ($('#taskName').length > 0) {
                    $('#taskName').empty().append('<option value="" selected>@lang("index.search_by_task_name")</option>');
                }
                if ($('#taskName').length === 0) $('.taskMemberAssignDiv').hide(); // Only for main form
                return;
            }
            if ($('#taskName').length === 0) $('.taskMemberAssignDiv').show(); // Only for main form

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-project-member-data') }}/${selectedProject}`,
                });

                // Populate members
                $('#taskMember').empty();
                if (!response?.members?.length) {
                    $('#taskMember').append('<option disabled>{{ __("index.employees_not_found") }}</option>');
                } else {
                    response.members.forEach(data => {
                        $('#taskMember').append(`
                        <option value="${data.id}" ${normalizedMemberIds.includes(String(data.id)) ? 'selected' : ''}>
                            ${data.name}
                        </option>
                    `);
                    });
                }

                // Explicitly set pre-selected values for Select2
                $('#taskMember').val(normalizedMemberIds).trigger('change');

                // Populate tasks (only for filter form)
                if ($('#taskName').length > 0) {
                    $('#taskName').empty().append('<option value="" selected>@lang("index.search_by_task_name")</option>');
                    if (!response?.tasks?.length) {
                        $('#taskName').append('<option disabled>{{ __("index.tasks_not_found") }}</option>');
                    } else {
                        response.tasks.forEach(data => {
                            $('#taskName').append(`
                            <option value="${data.id}" ${taskId == data.id ? 'selected' : ''}>
                                ${data.name}
                            </option>
                        `);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading data:', error);
                $('#taskMember').empty().append('<option disabled>{{ __("index.error_loading") }}</option>');
                if ($('#taskName').length > 0) {
                    $('#taskName').empty().append('<option disabled>{{ __("index.error_loading") }}</option>');
                }
            }
        };

        // Initialize multi-select for taskMember (using Select2)
        $('#taskMember').select2({
            placeholder: "{{ __('index.select_employee') }}",
            allowClear: true
        });

        // Initial load
        preloadProjects().then(preloadMembers);

        // Event handlers
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        if (isAdmin) {
            $('#branch_id').change(() => {
                resetDependentSelects(); // Reset dependent selects on branch change
                preloadProjects().then(preloadMembers);
            });
        }
        $('#project').change(() => {
            $('#taskMember').empty().select2({ data: [] }); // Reset taskMember on project change
            if ($('#taskName').length > 0) {
                $('#taskName').empty().append('<option value="" selected>@lang("index.search_by_task_name")</option>');
            }
            preloadMembers();
        });

    });

    $('.startNpDate').nepaliDatePicker({
        language: "english",
        dateFormat: "YYYY-MM-DD",
        ndpYear: true,
        ndpMonth: true,
        ndpYearCount: 20,
        disableAfter: "2089-12-30",
    });

    $('.npDeadline').nepaliDatePicker({
        language: "english",
        dateFormat: "YYYY-MM-DD",
        ndpYear: true,
        ndpMonth: true,
        ndpYearCount: 20,
        disableAfter: "2089-12-30",
    });

    document.getElementById('withTaskNotification').addEventListener('click', function (event) {

        document.getElementById('taskNotification').value = 1;
    });



</script>
