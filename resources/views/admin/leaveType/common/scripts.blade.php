<script>
    const translations = {
        addLeaveType: @json(__('message.leave_type_added')),
        createLeaveType: @json(__('index.add_leave_type')),
        editLeaveType: @json(__('index.edit_leave_type_detail')),
        updateLeaveType: @json(__('message.leave_type_updated')),
        selectBranch: @json(__('index.select_branch')),

        create: @json(__('index.create')),
        update: @json(__('index.update')),
    };
    $('document').ready(function(){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            var status = $(this).prop('checked') === true ? 1 : 0;
            var href = $(this).attr('href');
            Swal.fire({
                title: `{{ __('index.change_status_confirm') }}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                } else if (result.isDenied) {
                    (status === 0) ? $(this).prop('checked', true) : $(this).prop('checked', false)
                }
            })
        })

        $('.deleteLeaveType').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{__('index.delete_leave_confirmation')}}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        let leaveAllocated = $('#leave_allocated').val();
        (leaveAllocated != '') ? $('.leaveAllocated').show():  $('.leaveAllocated').hide();

         $('#leave_paid').on('change',function(){
            let leavePaid =  $(this).val();
            if(leavePaid == 0 ){
                $('#leave_allocated').val('');
                $('.leaveAllocated').hide();
                $('#leave_allocated').removeAttr('required');
            }else{
                $('.leaveAllocated').show();
                $('#leave_allocated').prop('required', 'true');
            }
        });

        $(document).on('click', '.create-leaveType', function() {
            $('#leaveTypeModalLabel').text(translations.createLeaveType);
            $('#submitButtonText').text(translations.create);
            $('#leaveTypeForm').attr('action', '{{ route("admin.leaves.store") }}');
            $('#formMethod').val('POST');

            $('#branch_id').val('').trigger('change');

            $('#name').val('');
            $('#leave_paid').val('');
            $('#leave_allocated').val('');
            $('#leaveTypeModal').modal('show');
        });

        $(document).on('click', '.edit-leaveType', function() {
            const leaveTypeId = $(this).data('id');

            $.ajax({
                type: 'GET',
                url: `{{ url('admin/leaves') }}/${leaveTypeId}/edit`,
                success: function(response) {
                    const leaveType = response.leaveTypeDetail;

                    $('#leaveTypeModalLabel').text(translations.editLeaveType);
                    $('#submitButtonText').text(translations.update);
                    $('#leaveTypeForm').attr('action', `{{ url('admin/leaves') }}/${leaveTypeId}`);
                    $('#formMethod').val('PUT');

                    let isPaid = leaveType.leave_allocated > 0 ? 1 : 0;
                    leaveType.leave_allocated > 0 ? $('.leaveAllocated').show():  $('.leaveAllocated').hide();
                    // Populate form fields
                    $('#name').val(leaveType.name);
                    $('#leave_paid').val(isPaid);
                    $('#leave_allocated').val(leaveType.leave_allocated);
                    $('#branch_id').val(leaveType.branch_id).trigger('change');
                    $('#gender').val(leaveType.gender).trigger('change');

                    $('#leaveTypeModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error: ' + (xhr.responseJSON.message || 'Failed to load leaveType data'),
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });


        $('#leaveTypeForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const action = form.attr('action');
            const method = $('#formMethod').val();
            const formData = new FormData(this);

            $.ajax({
                type: method === 'PUT' ? 'POST' : method,
                url: action,
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    form.find('button[type="submit"]').prop('disabled', true); // Disable submit button
                },
                success: function(response) {
                    $('#leaveTypeModal').modal('hide');
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: method === 'PUT' ? translations.updateLeaveType : translations.addLeaveType,
                        showConfirmButton: false,
                        timer: 1500,
                        didClose: () => {
                            location.reload(); // Consider replacing with dynamic table update
                        }
                    });
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors || { message: xhr.responseJSON.message };
                    let errorMessage = '';
                    if (errors.message) {
                        errorMessage = errors.message;
                    } else {
                        for (const field in errors) {
                            errorMessage += errors[field][0] + '\n';
                        }
                    }
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error: ' + errorMessage,
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                complete: function() {
                    form.find('button[type="submit"]').prop('disabled', false); // Re-enable submit button
                }
            });
        });
    })
</script>
