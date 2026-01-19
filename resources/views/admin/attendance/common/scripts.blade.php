<script>
    $(document).ready(function(){

        $("#branch_id").select2({});
        $("#department_id").select2({});
        $("#month").select2({});

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#checkIn').click(function (event) {
            event.preventDefault();
            let href = $(this).attr('href');
            Swal.fire({
                title: '{{ __('index.confirm_check_in') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });

        $('#checkOut').click(function (event) {
            event.preventDefault();
            let href = $(this).attr('href');
            Swal.fire({
                title: '{{ __('index.confirm_check_out') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });


        $('.checkLocation').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            $('.attendancelocation').attr("src", href);
        })


        $('.editAttendance').click(function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            let attendanceIn = $(this).data('in');
            let attendanceOut = $(this).data('out');
            let editRemark = $(this).data('remark');
            let date = $(this).data('date');
            let name = $(this).data('name');

            $('.modal-title').html('{{ __('index.edit_attendance_title', ['name' => ':name', 'date' => ':date']) }}'.replace(':name', name).replace(':date', date));
            $('#editAttendance').attr('action', url);
            $('#check_in').val(attendanceIn);
            $('#check_out').val(attendanceOut);
            $('#remark').val(editRemark);
            $('#attendanceForm').modal('show');
        });

        {{--$('.editNightAttendance').click(function (event) {--}}
        {{--    event.preventDefault();--}}
        {{--    let url = $(this).data('href');--}}
        {{--    let attendanceIn = $(this).data('in');--}}
        {{--    let attendanceOut = $(this).data('out');--}}
        {{--    let editRemark = $(this).data('remark');--}}
        {{--    let date = $(this).data('date');--}}
        {{--    let name = $(this).data('name');--}}

        {{--    $('.modal-title').html('{{ __('index.edit_attendance_title', ['name' => ':name', 'date' => ':date']) }}'.replace(':name', name).replace(':date', date));--}}
        {{--    $('#editNightAttendance').attr('action', url);--}}
        {{--    $('#nightCheckIn').val(attendanceIn);--}}
        {{--    $('#nightCheckOut').val(attendanceOut);--}}
        {{--    $('#nightEditRemark').val(editRemark);--}}
        {{--    $('#nightAttendanceForm').modal('show');--}}
        {{--});--}}
        $('.editNightAttendance').click(function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            let attendanceIn = $(this).data('in');
            let attendanceOut = $(this).data('out');
            let editRemark = $(this).data('remark');
            let date = $(this).data('date');
            let name = $(this).data('name');

            function formatDateTime(datetime) {
                if (!datetime) return null;
                let date = new Date(datetime);
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                let hours = String(date.getHours()).padStart(2, '0');
                let minutes = String(date.getMinutes()).padStart(2, '0');
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            }

            let formattedIn = formatDateTime(attendanceIn);
            let formattedOut = formatDateTime(attendanceOut);

            $('.modal-title').html('{{ __('index.edit_attendance_title', ['name' => ':name', 'date' => ':date']) }}'.replace(':name', name).replace(':date', date));
            $('#editNightAttendance').attr('action', url);
            $('#nightCheckIn').val(formattedIn);
            $('#nightCheckOut').val(formattedOut);
            $('#nightEditRemark').val(editRemark);
            $('#nightAttendanceForm').modal('show');
        });

        $('.addEmployeeAttendance').click(function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            let name = $(this).data('name');
            let date = $(this).data('date');
            let cdate = $(this).data('cdate');
            let user_id = $(this).data('user_id');

            $('.add-modal-title').html('{{ __('index.create_attendance_title', ['name' => ':name', 'date' => ':date']) }}'.replace(':name', name).replace(':date', cdate));
            $('#addDate').val(date);
            $('#empId').val(user_id);
            $('#createAttendance').attr('action', url);
            $('#attendanceCreateForm').modal('show');
        });

        function getAttendanceFilterParam() {
            let params = {
                year: $('#year').val(),
                month: $('#month').val()
            };
            return params;
        }

        function getDayWiseAttendanceFilterParam() {
            let params = {
                attendance_date: $('#attendance_date').val()
            };
            return params;
        }

        $('#download-excel').on('click', function (e) {
            e.preventDefault();
            let route = $(this).data('href');
            let filtered_params = getAttendanceFilterParam();
            filtered_params.download_excel = true;
            let queryString = $.param(filtered_params);
            let url = route + '?' + queryString;
            window.open(url, '_blank');
        });

        $('#download-daywise-attendance-excel').on('click', function (e) {
            e.preventDefault();
            let route = $(this).data('href');
            let filtered_params = getDayWiseAttendanceFilterParam();
            filtered_params.download_excel = true;
            let queryString = $.param(filtered_params);
            let url = route + '?' + queryString;
            window.open(url, '_blank');
        });

        $('.reset').click(function(event) {
            event.preventDefault();
            let currentDate = $(this).data('date');
            $('#attendance_date').val(currentDate);
        });

        $('.detailReset').click(function(event) {
            event.preventDefault();
            let CurrentYear = $(this).data('year');
            let currentMonth = $(this).data('month');
            $('#year').val(CurrentYear);
            $('#month').val(currentMonth);
        });

        $('.dayAttendance').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

    });
</script>
