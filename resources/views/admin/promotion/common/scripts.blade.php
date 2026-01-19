<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>
<script>
    $('document').ready(function(){


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.nepali_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

        $('body').on('click', '.deleteWarning', function (event) {
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
        });

    });

    $(document).ready(function () {

        // Define variables
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";
        const departmentId = "{{ $filterParameters['department_id'] ?? ($promotionDetail->department_id ?? '') }}";
        const employeeId = "{{ $filterParameters['employee_id'] ?? ($promotionDetail->employee_id ?? '') }}";

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

        const loadEmployeesAndPosts = async () => {
            const selectedDepartmentId = $('#department_id').val();
            if (!selectedDepartmentId) return;

            try {
                $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');
                $('#post_id').empty().append('<option selected disabled>{{ __("index.select_post") }}</option>');

                const response = await fetch(`{{ url('admin/promotion/get-employees-posts') }}/${selectedDepartmentId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                });

                const data = await response.json();

                console.log(data);
                if (data.users && data.users.length > 0) {
                    data.users.forEach(user => {
                        $('#employee_id').append(`<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''}>${user.name}</option>`);
                    });
                } else {
                    $('#employee_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                }

                if (data.posts && data.posts.length > 0) {
                    data.posts.forEach(post => {
                        $('#post_id').append(`<option value="${post.id}">${post.post_name}</option>`);
                    });
                } else {
                    $('#post_id').append('<option disabled>{{ __("index.no_posts_found") }}</option>');
                }
            } catch (error) {
                $('#employee_id').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                $('#post_id').append('<option disabled>{{ __("index.error_loading_posts") }}</option>');
            }
        };

        const loadEmployeeData = async () => {
            const selectedEmployeeId = $('#employee_id').val();
            if (!selectedEmployeeId) return;
            let oldPostId = "{{  $promotionDetail->old_post_id ?? '' }}";

            try {

                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/transfer/get-user-data') }}" + '/' + selectedEmployeeId,
                }).done(function (data) {


                    $('#old_post_id').append('<option ' + ((data.id == oldPostId) ? "selected" : '') + ' value="' + data.post_id + '" >' + data.post + '</option>');
                });


            } catch (error) {

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
                    await loadEmployeesAndPosts();
                });

                // Trigger initial load if branch is selected
                if (selectedBranchId) {
                    $('#branch_id').trigger('change');
                }
            } else {
                selectedBranchId = defaultBranchId;
                if (selectedBranchId) {
                    await loadDepartments(selectedBranchId);
                    await loadEmployeesAndPosts();
                }
            }

            // Attach department change listener
            $('#department_id').on('change', loadEmployeesAndPosts);

            // Trigger initial employee load if department is pre-selected
            if (departmentId) {
                $('#department_id').trigger('change');
            }

            $('#employee_id').change(loadEmployeeData);
        };

        // Initialize everything
        initializeDropdowns();
    });
    tinymce.init({
        selector: '#tinymceExample',
        height: 200,
    });



    document.getElementById('withNotification').addEventListener('click', function (event) {

        document.getElementById('notification').value = 1;
    });

</script>
