<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function () {


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

    });

    tinymce.init({
        selector: '#tinymceExample',
        height: 200,
    });

    $(document).ready(function () {
        const loadDepartments = async () => {

            const selectedBranchId = $('#old_branch_id').val();
            if (!selectedBranchId) return;

            try {
                $('#old_department_id').empty().append('<option selected disabled>{{ __("index.select_department") }}</option>');
                $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');

                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                if (!response || !response.data || response.data.length === 0) {
                    $('#old_department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                    return;
                }

                response.data.forEach(data => {
                    $('#old_department_id').append(`<option value="${data.id}">${data.dept_name}</option>`);
                });
            } catch (error) {
                console.error('Error loading departments:', error);
                $('#old_department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            }
        };

        const loadEmployees = async () => {
            const selectedDepartmentId = $('#old_department_id').val();
            if (!selectedDepartmentId) return;
            let employeeId = "{{  $transferDetail->employee_id ?? '' }}";

            try {
                $('#employee_id').empty().append('<option selected disabled>{{ __("index.select_employee") }}</option>');

                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/employees/get-all-employees') }}" + '/' + selectedDepartmentId,
                }).done(function (response) {


                    response.data.forEach(function (data) {
                        $('#employee_id').append('<option ' + ((data.id == employeeId) ? "selected" : '') + ' value="' + data.id + '" >' + data.name + '</option>');
                    });
                });


            } catch (error) {
                $('#employee_id').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
            }
        };

        const loadEmployeeData = async () => {
            const selectedEmployeeId = $('#employee_id').val();
            if (!selectedEmployeeId) return;
            let oldPostId = "{{  $transferDetail->old_post_id ?? '' }}";
            let oldOfficeTimeId = "{{  $transferDetail->old_office_time_id ?? '' }}";
            let oldSupervisorId = "{{  $transferDetail->old_supervisor_id ?? '' }}";

            try {

                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/transfer/get-user-data') }}" + '/' + selectedEmployeeId,
                }).done(function (data) {


                    $('#old_post_id').append('<option ' + ((data.id == oldPostId) ? "selected" : '') + ' value="' + data.post_id + '" >' + data.post + '</option>');
                    $('#old_supervisor_id').append('<option ' + ((data.id == oldSupervisorId) ? "selected" : '') + ' value="' + data.supervisor_id + '" >' + data.supervisor + '</option>');
                    $('#old_office_time_id').append('<option ' + ((data.id == oldOfficeTimeId) ? "selected" : '') + ' value="' + data.office_time_id + '" >' + data.office_time + '</option>');
                });


            } catch (error) {

            }
        };

        // Load departments when branch is selected
        $('#old_branch_id').change(loadDepartments);

        // Load employees when department is selected
        $('#old_department_id').change(loadEmployees);
        $('#employee_id').change(loadEmployeeData);
    });

    document.getElementById('withNotification').addEventListener('click', function (event) {

        document.getElementById('notification').value = 1;
    });


    $(document).ready(function () {
        const loadDepartmentsAndOfficeTime = async () => {
            const selectedBranchId = $('#new_branch_id').val();
            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/transfer/get-user-transfer-branch-data') }}/${selectedBranchId}`,
                });

                if (response.departments && response.departments.length > 0) {
                    response.departments.forEach(department => {
                        $('#new_department_id').append(`<option value="${department.id}">${department.dept_name}</option>`);
                    });
                } else {
                    $('#new_department_id').append('<option disabled>{{ __("index.no_department_found") }}</option>');
                }

                if (response.officeTimes && response.officeTimes.length > 0) {
                    response.officeTimes.forEach(shift => {
                        $('#new_office_time_id').append(`<option value="${shift.id}">${shift.opening_time} - ${shift.closing_time}</option>`);
                    });
                } else {
                    $('#new_office_time_id').append('<option disabled>{{ __("index.no_office_time_found") }}</option>');
                }
            } catch (error) {
                $('#new_department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');

                $('#new_office_time_id').append('<option disabled>{{ __("index.error_loading_office_times") }}</option>');
            }
        };

        const loadSupervisorAndPosts = async () => {
            const selectedDepartmentId = $('#new_department_id').val();
            if (!selectedDepartmentId) return;

            try {
                const response = await fetch(`{{ url('admin/transfer/get-user-transfer-department-data') }}/${selectedDepartmentId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                });

                let data = await response.json();

                console.log(data);
                if (data.supervisors && data.supervisors.length > 0) {
                    data.supervisors.forEach(user => {
                        $('#new_supervisor_id').append(`<option value="${user.id}">${user.name}</option>`);
                    });
                } else {
                    $('#new_supervisor_id').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                }

                if (data.posts && data.posts.length > 0) {
                    data.posts.forEach(post => {
                        $('#new_post_id').append(`<option value="${post.id}">${post.post_name}</option>`);
                    });
                } else {
                    $('#new_post_id').append('<option disabled>{{ __("index.no_posts_found") }}</option>');
                }
            } catch (error) {
                $('#new_supervisor_id').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                $('#new_post_id').append('<option disabled>{{ __("index.error_loading_posts") }}</option>');
            }
        };

        // Load departments when branch is selected
        $('#new_branch_id').change(loadDepartmentsAndOfficeTime);

        // Load employees and posts when department is selected
        $('#new_department_id').change(loadSupervisorAndPosts);
    });

</script>
