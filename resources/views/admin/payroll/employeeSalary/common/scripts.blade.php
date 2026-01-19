
<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $(document).ready(function () {
        $('#branch_id').select2();
        $('#department_id').select2();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.error').hide();

        $('body').on('change','#salaryCycle',function (event) {
            event.preventDefault();
            let salaryCycle = $(this).val()
            let employeeId = $(this).data('employee')
            let currentCycle = $(this).data('current')
            let url = "{{url('admin/employee-salaries/update-cycle')}}" +'/' + employeeId + '/' + salaryCycle;
            Swal.fire({
                title: '{{ __('index.confirm_change_cycle') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }else{
                    $(this).val(currentCycle);
                }
            })
        })

        $('.generatePayroll').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '{{ __('index.confirm_generate_payroll') }}',
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

        $('.deleteEmployeeSalary').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.confirm_delete_payroll') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                // width:'1000px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })



    });


    $(document).ready(function () {
        const loadDepartments = async () => {
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const selectedBranchId = isAdmin ? $('#branch_id').val() : defaultBranchId;
            // Changed selector to #branch
            let departmentId = "{{ $userDetail->department_id ?? $filterParameters['department_id'] ?? old('department_id') }}";

            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                $('#department_id').empty(); // Changed selector to #department_id

                // Departments
                if (!departmentId) {
                    $('#department_id').append('<option disabled selected>{{ __('index.select_department') }}</option>');
                }
                if (response.data && response.data.length > 0) {
                    response.data.forEach(department => {
                        $('#department_id').append(`<option ${department.id == departmentId ? 'selected' : ''} value="${department.id}">${department.dept_name}</option>`);
                    });
                } else {
                    $('#department_id').append('<option disabled>{{ __("index.no_department_found") }}</option>');
                }

            } catch (error) {
                $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            }
        };



        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        if (isAdmin) {
            $('#branch_id').on('change', loadDepartments);
            $('#branch_id').trigger('change');
        } else {
            loadDepartments(); // Load directly for regular users
        }
    });

</script>
