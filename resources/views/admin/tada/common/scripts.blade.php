<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>
<script src="{{asset('assets/js/imageuploadify.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

<script>
    $(document).ready(function (e) {

        $("#branch_id").select2();
        $("#employee_id").select2();
        $("#department_id").select2();
        $("#status").select2();


        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#image-uploadify").imageuploadify();

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.tada_status_change')',
                showDenyButton: true,
                confirmButtonText: '@lang('index.yes')',
                denyButtonText: '@lang('index.no')',
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

        $('body').on('click', '.delete', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_tada_confirm', ['title' => ':name']) }}'.replace(':name', title),
                showDenyButton: true,
                confirmButtonText: '@lang('index.yes')',
                denyButtonText: '@lang('index.no')',
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('body').on('click', '#updateStatus', function (e) {
            e.preventDefault();
            let status = $(this).data('status');
            let action = $(this).data('action');
            let title = $(this).data('title');
            let reason = $(this).data('reason');
            if(status == 'pending'){
                $('.update').prop('disabled',true)
            }
            $('#addslider').modal('show');
            $('#updateTadaStatus').attr('action',action);
            $('.modal-title').html(title);
            $('#tada_status').val(status);
            $('#reason').val(reason);
        }).trigger("change");

        $('#tada_status').change(function(e){
           e.preventDefault();
           let status = $(this).val();
           if(status == 'accepted'){
               $('.remark').removeAttr('required')
           }else{
               $('.remark').attr('required','required');
           }
           (status == 'pending') ? $('.update').prop('disabled',true) : $('.update').prop('disabled',false);
        });


        $('.reset').click(function(event){
            event.preventDefault();
            $('#status').val('');
            $('#employee').val('');

        });

        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";
        const departmentId = "{{ $filterParameters['department_id'] ?? ($tadaDetail->department_id ?? '') }}";
        const employeeId = "{{ $filterParameters['employee_id'] ?? ($tadaDetail->employee_id ?? '') }}";


        const loadDepartments = async (selectedBranchId) => {

            if (!selectedBranchId) return;


            try {
                $('#department_id').empty().append('<option selected disabled>{{ __("index.select_department") }}</option>');

                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                if (!response || !response.data || response.data.length === 0) {
                    $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                    return;
                }


                response.data.forEach(data => {
                    $('#department_id').append(`<option value="${data.id}" ${data.id == departmentId ? 'selected' : ''}>${data.dept_name}</option>`);
                });
            } catch (error) {
                $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            }
        };

        const loadEmployees = async () => {
            const selectedDepartmentId = $('#department_id').val();
            if (!selectedDepartmentId) return;

            try {
                $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');

                const response = await fetch(`{{ url('admin/employees/get-all-employees') }}/${selectedDepartmentId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                });

                const data = await response.json(); // Missing in original code


                console.log(employeeId);
                if (data.data && data.data.length > 0) {
                    // Populate dropdown with employee options
                    data.data.forEach(user => {
                        $('#employee_id').append(`<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''} >${user.name}</option>`);
                    });
                } else {
                    $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                }

            } catch (error) {
                $('#employee_id').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
            }
        };

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;

                $('#branch_id').on('change', async () => {
                    const newBranchId = $('#branch_id').val();
                    await loadDepartments(newBranchId);
                    $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                    await loadEmployees();
                });

                // Trigger initial load if branch is selected
                if (selectedBranchId) {
                    $('#branch_id').trigger('change');
                }
            } else {
                selectedBranchId = defaultBranchId;
                if (selectedBranchId) {
                    await loadDepartments(selectedBranchId);
                    await loadEmployees();
                }
            }

            // Attach department change listener
            $('#department_id').on('change', loadEmployees);

            // Trigger initial employee load if department is pre-selected
            if (departmentId) {
                $('#department_id').trigger('change');
            }
        };

        // Initialize everything
        initializeDropdowns();

    });

</script>
