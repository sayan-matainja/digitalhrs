<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $("#departments").select2();
        $("#branch_id").select2();
        $("#role").select2();
        $("#related").select2();

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

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: `{{ __('index.change_status_confirm') }}`,
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
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


</script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    $(document).on('select2:select', '.user-dropdown', function (e) {
        let selectedVal = e.params.data.id;
        $(this).val(selectedVal).trigger('change');
    });
    $(function () {

        $("#sortable").sortable({
            handle: '.link-icon',
            placeholder: "ui-state-highlight"
        }).disableSelection();

        $('#add-approver').on('click', function () {
            let index = $('#sortable .approver-row').not('.template').length;

            let $newRow = $('#sortable .approver-row.template').clone()
                .removeClass('template')
                .addClass('approver-row')
                .attr('data-index', index)
                .css('display', '');

            $newRow.find('.approver-select').attr('name', `approver[${index}]`);
            $newRow.find('.staff-select').attr('name', `role_id[${index}]`);
            $newRow.find('.user-dropdown').attr('name', `user_id[${index}]`);

            $newRow.find('select').val('');
            $newRow.find('.employee-wrapper').hide();
            $newRow.find('.staff-select, .user-dropdown').removeAttr('required');

            let $removeBtn = $('<button type="button" class="btn btn-danger btn-sm remove-approver">x</button>');
            $newRow.find('.col-lg-2').html($removeBtn);


            $newRow.find('.col-lg-1 i.link-icon').attr('data-feather', 'move');
            feather.replace();

            $('#sortable').append($newRow);

            toggleEmployeeFields($newRow.find('.approver-select'));

            $("#sortable").sortable("refresh");
        });


        $(document).on('click', '.remove-approver', function () {
            if ($('#sortable .approver-row').not('.template').length <= 1) {
                alert('At least one approver is required.');
                return;
            }
            $(this).closest('li').remove();
        });


        function toggleEmployeeFields($select) {
            let $row = $select.closest('.approver-row');
            let $wrappers = $row.find('.employee-wrapper');
            let $roleSelect = $row.find('.staff-select');
            let $userSelect = $row.find('.user-dropdown');

            if ($select.val() === 'specific_personnel') {
                $wrappers.show();
                $roleSelect.attr('required', true);
                $userSelect.attr('required', true);
            } else {
                $wrappers.hide();
                $roleSelect.removeAttr('required').val('');
                $userSelect.removeAttr('required').val('').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                if ($.fn.select2 && $userSelect.hasClass('select2-hidden-accessible')) {
                    $userSelect.select2('destroy');
                }
            }
        }

        // Initial toggle
        $(document).on('change', '.approver-select', function () {
            toggleEmployeeFields($(this));
        });
        $('.approver-select').each(function () { toggleEmployeeFields($(this)); });


        $(document).on('change', '.staff-select', function () {
            let $row = $(this).closest('.approver-row');
            let $userDropdown = $row.find('.user-dropdown');
            let roleId = $(this).val();

            // 1. Destroy old Select2 completely
            if ($userDropdown.hasClass('select2-hidden-accessible')) {
                $userDropdown.select2('destroy');
            }

            // 2. Reset the actual <select>
            $userDropdown.empty().append('<option value="" selected disabled>{{ __("index.select_employee") }}</option>');

            if (!roleId) return;

            $.ajax({
                url: '/admin/leave-approval/get-employees-by-role',
                method: 'GET',
                data: { role_id: roleId },
                success: function (response) {
                    let users = response.success ? response.data : [];

                    if (users.length > 0) {
                        users.forEach(user => {
                            $userDropdown.append(`<option value="${user.id}">${user.name}</option>`);
                        });
                    } else {
                        $userDropdown.append('<option disabled>No employees found</option>');
                    }

                    // 3. RE-INITIALIZE SELECT2 (this is mandatory)
                    $userDropdown.select2({
                        width: '100%',
                        placeholder: "{{ __('index.select_employee') }}",
                        allowClear: false
                    });

                    // 4. CRITICAL: When user selects an employee, sync value back to original <select>
                    $userDropdown.off('select2:select').on('select2:select', function (e) {
                        let selectedVal = e.params.data.id;
                        $(this).val(selectedVal); // This forces the real <select> to have the correct value
                    });
                },
                error: function () {
                    $userDropdown.append('<option disabled>Error loading employees</option>');
                    $userDropdown.select2();
                }
            });
        });

        $(document).ready(function () {
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const branchId = "{{ $filterParameters['branch_id'] ?? null }}";
            const filterDepartmentIds = {!! json_encode($filterParameters['department_id'] ?? []) !!} || [];
            const formDepartmentIds = {!! isset($departmentId) ? json_encode($departmentId) : '[]' !!};
            const departmentIds = filterDepartmentIds.length > 0 ? filterDepartmentIds.map(String) : formDepartmentIds.map(String);
            let leaveTypeId = "{{ $leaveApprovalDetail->leave_type_id ?? $filterParameters['leave_type_id'] ?? '' }}";

            const loadLeaveTypeAndDepartments = async (selectedBranchId) => {
                if (!selectedBranchId) return;

                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/get-branch-leave-data') }}/${selectedBranchId}`,
                    });

                    $('#related').empty().append('<option value="" disabled selected>{{ __("index.select_leave_type") }}</option>');
                    if (response.types?.length > 0) {
                        response.types.forEach(t => {
                            const sel = String(t.id) === String(leaveTypeId) ? 'selected' : '';
                            $('#related').append(`<option value="${t.id}" ${sel}>${t.name}</option>`);
                        });
                    }

                    $('#departments').empty().append('<option value="all">{{ __("index.select_all") }}</option>');
                    if (response.departments?.length > 0) {
                        response.departments.forEach(d => {
                            const sel = departmentIds.includes(String(d.id)) ? 'selected' : '';
                            $('#departments').append(`<option value="${d.id}" ${sel}>${d.dept_name}</option>`);
                        });
                    }

                    $('#departments').select2();

                    // Select All logic
                    $('#departments').off('select2:select select2:unselect').on('select2:select', function (e) {
                        if (e.params.data.id === 'all') {
                            $('#departments option').not('[value="all"],:disabled').prop('selected', true);
                            $(this).trigger('change');
                        }
                    }).on('select2:unselect', function (e) {
                        if (e.params.data.id !== 'all') {
                            $('#departments option[value="all"]').prop('selected', false);
                            $(this).trigger('change');
                        }
                    });

                } catch (err) { console.error(err); }
            };

            const init = async () => {
                let branch = isAdmin
                    ? ($('#branch_id').val() || branchId || defaultBranchId)
                    : defaultBranchId;
                if (branch) await loadLeaveTypeAndDepartments(branch);
                if (isAdmin) {
                    $('#branch_id').on('change', () => loadLeaveTypeAndDepartments($('#branch_id').val()));
                }
            };

            init();
        });
    });
</script>
