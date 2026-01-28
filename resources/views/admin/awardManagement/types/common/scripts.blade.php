
<script>
    const translations = {
        addAwardType: @json(__('message.add_award_type')),
        createAwardType: @json(__('index.add_award_types')),
        editAwardType: @json(__('index.edit_award_types')),
        updateAwardType: @json(__('message.update_award_type')),
        selectBranch: @json(__('index.select_branch')),

        create: @json(__('index.create')),
        update: @json(__('index.update')),
    };
    $(document).ready(function () {
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

        $('.delete').click(function (event) {
            event.preventDefault();
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

        $(document).on('click', '.create-awardType', function() {
            $('#awardTypeModalLabel').text(translations.createAwardType);
            $('#submitButtonText').text(translations.create);
            $('#awardTypeForm').attr('action', '{{ route("admin.award-types.store") }}');
            $('#formMethod').val('POST');

            $('#branch_id').val('').trigger('change');

            $('#name').val('');
            $('#awardTypeModal').modal('show');
        });

        $(document).on('click', '.edit-awardType', function() {
            const awardTypeId = $(this).data('id');

            $.ajax({
                type: 'GET',
                url: `{{ url('admin/award-types') }}/${awardTypeId}/edit`,
                success: function(response) {
                    const awardType = response.awardTypeDetail;

                    $('#awardTypeModalLabel').text(translations.editAwardType);
                    $('#submitButtonText').text(translations.update);
                    $('#awardTypeForm').attr('action', `{{ url('admin/award-types') }}/${awardTypeId}`);
                    $('#formMethod').val('PUT');


                    // Populate form fields
                    $('#name').val(awardType.title);
                    $('#branch_id').val(awardType.branch_id).trigger('change');

                    $('#awardTypeModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error: ' + (xhr.responseJSON.message || 'Failed to load awardType data'),
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });


        $('#awardTypeForm').on('submit', function(e) {
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
                    $('#awardTypeModal').modal('hide');
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: method === 'PUT' ? translations.updateAwardType : translations.addAwardType,
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
    });

</script>
