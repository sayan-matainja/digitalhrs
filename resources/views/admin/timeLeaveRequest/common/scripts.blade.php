<script>
    $(document).ready(function () {

        $("#branch_id").select2({});
        $("#requestedBy").select2({});
        $("#department_id").select2({});

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $('body').on('click', '#leaveRequestUpdate', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            let status = $(this).data('status');
            let remark = $(this).data('remark');
            $('.modal-title').html(`{{__('index.time_leave_status_update')}}`);
            $('#updateLeaveStatus').attr('action', url)
            $('#status').val(status)
            $('#remark').val(remark)
            $('#statusUpdate').modal('show');
        });

        $('.reset').click(function (event) {
            event.preventDefault();
            $('#requestedBy').val('');
            $('#leaveType').val('');
            $('#month').val('');
            $('#status').val('');
            $('#year').val('');
        })

    });
</script>
