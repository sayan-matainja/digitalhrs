<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $('#attachment').change(function(){
            const input = document.getElementById('image');
            const preview = document.getElementById('image-preview');
            const file = input.files[0];
            const reader = new FileReader();
            reader.addEventListener('load', function() {
                preview.src = reader.result;
            });
            reader.readAsDataURL(file);
            $('#image-preview').removeClass('d-none')

        })




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


        $('#awarded_date_np').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

    });

    $('.toggleStatus').change(function (event) {
        event.preventDefault();
        let status = $(this).prop('checked') === true ? 1 : 0;
        let href = $(this).attr('href');
        Swal.fire({
            title: '{{ __('index.change_status_confirm') }}',
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


    tinymce.init({
        selector: '#tinymceExample',
        height: 200,
    });


    $(document).ready(function () {
        // Define variables
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";
        const departmentId = "{{ $filterParameters['department_id'] ?? ($trainerDetail->department_id ?? '') }}";
        const employeeId = "{{ $filterParameters['employee_id'] ?? ($trainerDetail->employee_id ?? '') }}";


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
    $('#trainer_type').change(function (){

        let internalType = @json($internal);
        let externalType = @json($external);
        let selectedType= $('#trainer_type option:selected').val();

        if(selectedType === internalType){
            $('.externalTrainer').find('input').val('');

            $('.externalTrainer').addClass('d-none');
            $('.internalTrainer').removeClass('d-none');
        }else if(selectedType === externalType){
            $('.internalTrainer').find('select').val('');

            $('.internalTrainer').addClass('d-none');
            $('.externalTrainer').removeClass('d-none');

        }
    })

    document.addEventListener('DOMContentLoaded', function() {

        const inputFields = document.querySelectorAll('input:not([type="submit"]), select');


        inputFields.forEach(input => {
            input.addEventListener('keypress', function(e) {

                if (e.key === 'Enter') {
                    e.preventDefault();


                    const currentIndex = Array.from(inputFields).indexOf(this);
                    const nextInput = inputFields[currentIndex + 1];


                    if (nextInput) {
                        nextInput.focus();
                    }
                }
            });
        });
    });
</script>
