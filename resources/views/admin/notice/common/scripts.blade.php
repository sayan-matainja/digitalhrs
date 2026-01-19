<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>
<script>
    $(document).ready(function () {

        // Initialize Select2 FIRST (empty is fine)
        $('#branch_id').select2({
            placeholder: "{{ __('index.select_branch') }}"
        });

        $('#department_id').select2({
            placeholder: "{{ __('index.select_department') }}",
            allowClear: true
        });

        $('#employee_id').select2({
            placeholder: "{{ __('index.select_employee') }}",
            allowClear: true
        });

        $('#notice').select2({
            placeholder: "{{ __('index.select_notice_receiver') }}"
        });

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        const userBranchId = {{ auth()->user()->branch_id ?? 'null' }};


        @if(isset($noticeDetail))
            window.preselectedDepartments = @json($noticeDetail->noticeDepartments->pluck('department_id')->map('strval')->toArray() ?? []);
        window.preselectedEmployees   = @json($noticeDetail->noticeReceiversDetail->pluck('notice_receiver_id')->map('strval')->toArray() ?? []);
        @elseif(request()->filled('department_id') || request()->filled('employee_id'))
            window.preselectedDepartments = @json((array) $filterParameters['department_id']);
        window.preselectedEmployees   = @json((array) $filterParameters['employee_id']);
        @else
            window.preselectedDepartments = [];
        window.preselectedEmployees   = [];
        @endif



        function getCurrentBranchId() {
            return $('#branch_id').val() || userBranchId;
        }

        // Load Departments
        // Load Departments - REVISED
        async function loadDepartments(branchId) {
            if (!branchId) return;

            const $deptSelect = $('#department_id');

            // Destroy existing Select2 if exists
            if ($deptSelect.hasClass("select2-hidden-accessible")) {
                $deptSelect.select2('destroy');
            }

            $deptSelect.empty().append('<option value="">Loading...</option>');

            try {
                const response = await $.get(`{{ url('admin/departments/get-All-Departments') }}/${branchId}`);

                $deptSelect.empty(); // Clear loading

                const preselected = window.preselectedDepartments.map(String);

                if (response.data?.length > 0) {
                    response.data.forEach(dept => {
                        const idStr = String(dept.id);
                        const selected = preselected.includes(idStr);
                        $deptSelect.append(new Option(dept.dept_name, dept.id, selected, selected));
                    });
                } else {
                    $deptSelect.append('<option disabled>@lang("index.no_departments_found")</option>');
                }

                // NOW initialize or re-initialize Select2
                $deptSelect.select2({
                    placeholder: "{{ __('index.select_department') }}",
                    allowClear: true
                });

                // If something was preselected, open it to show selection
                if (preselected.length > 0) {
                    $deptSelect.val(preselected).trigger('change');
                }

                // Now load employees if needed
                if ($deptSelect.val() && $deptSelect.val().length > 0) {
                    await loadEmployees();
                }

            } catch (err) {
                console.error('Failed to load departments:', err);
                $deptSelect.empty().append('<option disabled>@lang("index.error_loading")</option>');
                $deptSelect.select2({
                    placeholder: "{{ __('index.select_department') }}",
                    allowClear: true
                });
            }
        }

        // Load Employees
        async function loadEmployees() {
            const deptIds = $('#department_id').val() || [];
            const $empSelect = $('#employee_id');

            if (deptIds.length === 0) {
                if ($empSelect.hasClass("select2-hidden-accessible")) {
                    $empSelect.select2('destroy');
                }
                $empSelect.empty().select2({
                    placeholder: "{{ __('index.select_employee') }}",
                    allowClear: true
                });
                return;
            }

            // Destroy existing
            if ($empSelect.hasClass("select2-hidden-accessible")) {
                $empSelect.select2('destroy');
            }

            $empSelect.empty().append('<option value="">Loading employees...</option>');

            try {
                const response = await $.ajax({
                    url: '{{ route("admin.employees.fetchByDepartment") }}',
                    method: 'POST',
                    data: { department_ids: deptIds, _token: '{{ csrf_token() }}' },
                    dataType: 'json'
                });

                $empSelect.empty();

                const preselected = window.preselectedEmployees.map(String);

                response.forEach(emp => {
                    const idStr = String(emp.id);
                    const selected = preselected.includes(idStr);
                    $empSelect.append(new Option(emp.name, emp.id, selected, selected));
                });

                // Re-init Select2
                $empSelect.select2({
                    placeholder: "{{ __('index.select_employee') }}",
                    allowClear: true
                });

                // Force set preselected values
                if (preselected.length > 0) {
                    $empSelect.val(preselected).trigger('change');
                }

            } catch (err) {
                console.error('Failed to load employees:', err);
                $empSelect.empty().append('<option disabled>@lang("index.error_loading_employees")</option>');
                $empSelect.select2({
                    placeholder: "{{ __('index.select_employee') }}",
                    allowClear: true
                });
            }
        }

        // Events
        $('#branch_id').on('change', function () {
            const branchId = $(this).val() || userBranchId;
            window.preselectedDepartments = []; // reset on branch change
            window.preselectedEmployees = [];
            loadDepartments(branchId);
        });

        $('#department_id').on('change', function () {
            const selectedDepts = $(this).val() || [];
            if (selectedDepts.length === 0) {
                $('#employee_id').empty().trigger('change');
            } else {
                loadEmployees();
            }
        });

        // Select All
        $('#select_all_departments').on('change', function () {
            $('#department_id option').prop('selected', this.checked);
            $('#department_id').trigger('change');
        });

        $('#select_all_employees').on('change', function () {
            $('#employee_id option').prop('selected', this.checked);
            $('#employee_id').trigger('change');
        });

        // Initialize on page load
        const initialBranchId = getCurrentBranchId();

        // Always init branch Select2 (it's static)
        $('#branch_id').select2({
            placeholder: "{{ __('index.select_branch') }}"
        });

        if (initialBranchId) {
            $('#branch_id').val(initialBranchId);
             loadDepartments(initialBranchId); // This now properly inits Select2
        } else if (window.preselectedDepartments.length > 0) {
             loadDepartments(userBranchId || '');
        }




        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.confirm_change_notice_status')',
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
                title: '@lang('index.confirm_delete_notice')',
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

        $('.sendNotice').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.confirm_send_notice')',
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

        $('body').on('click', '#showNoticeDescription', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            $.get(url, function (data) {


                $('.modal-title').html(@json(__('index.notice_detail_modal_title', ['title' => '']))+': ' + data.data.title );
                $('#description').text((data.data.description));
                $('#addslider').modal('show');
            })
        }).trigger("change");

        $('input[type="checkbox"]').click(function(){
            if($(this).is(":checked")){
                $('#notice').select2('destroy').find('option').prop('selected', 'selected').end().select2();
            }
            else if($(this).is(":not(:checked)")){
                $('#notice').select2('destroy').find('option').prop('selected', false).end().select2();
            }
        });

        $('.reset').click(function(event){
            event.preventDefault();
            $('#notice_receiver').val('');
            $('.fromDate').val('');
            $('.toDate').val('');
        });

        $('#fromDate').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

        $('#toDate').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });
    });


    document.getElementById('withNotification').addEventListener('click', function (event) {

        document.getElementById('notification').value = 1;
    });

</script>
