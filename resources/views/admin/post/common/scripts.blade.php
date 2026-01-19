<script>

    $(document).ready(function () {
        $("#department_id").select2({});
        $("#branch_id").select2({});
        // Setup CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle status change handler
        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '{{ __("index.change_status_confirmation") }}',
                showDenyButton: true,
                confirmButtonText: `{{ __("index.yes") }}`,
                denyButtonText: `{{ __("index.no") }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                } else if (result.isDenied) {
                    // Revert checkbox state if user clicks 'No'
                    (status === 0) ? $(this).prop('checked', true) : $(this).prop('checked', false);
                }
            });
        });

        // Delete post confirmation handler
        $('.deletePost').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __("index.delete_post_confirmation") }}',
                showDenyButton: true,
                confirmButtonText: `{{ __("index.yes") }}`,
                denyButtonText: `{{ __("index.no") }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });

        // Show employee list in modal
        $('body').on('click', '#showEmployee', function (e) {
            e.preventDefault();
            console.log('hello');
            let employee = $(this).data('employee');
            console.log(employee);
            $('.employee').remove();
            $('.modal-title').html('{{ __("index.employee_list_title") }}');
            if (employee.length > 0) {
                $('.postEmptyCase').addClass('d-none');
                employee.forEach(function (data) {
                    let avatar = data.avatar ? '{{ asset(\App\Models\User::AVATAR_UPLOAD_PATH) }}' + '/' + data.avatar : '{{ asset('assets/images/img.png') }}';
                    $('.employeeList').append(
                        '<div class="col-lg-6 d-flex align-items-center mb-3 employee">' +
                        '<img class="rounded-circle w-25 me-2 employeeImage" ' + 'style="object-fit: cover" ' +
                        'src="' + avatar + '" ' +
                        'alt="profile">' +
                        '<span class="employeeName">' + data.name + '</span>' +
                        '</div>'
                    );
                });
            } else {
                $('.postEmptyCase').removeClass('d-none');
            }
            $('#showEmployees').modal('show');
        }).trigger("change");



        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? $postDetail->branch_id ?? null }}";
        const departmentId = "{{ $filterParameters['department_id'] ??  $postDetail->dept_id ?? '' }}";


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


        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;

                $('#branch_id').on('change', async () => {
                    const newBranchId = $('#branch_id').val();
                    await loadDepartments(newBranchId);

                });

                // Trigger initial load if branch is selected
                if (selectedBranchId) {
                    $('#branch_id').trigger('change');
                }
            } else {
                selectedBranchId = defaultBranchId;
                if (selectedBranchId) {
                    await loadDepartments(selectedBranchId);

                }
            }

        };

        // Initialize everything
        initializeDropdowns();
    });

</script>
