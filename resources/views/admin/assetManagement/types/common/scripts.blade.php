
<script>
    const translations = {
        addAssetType: @json(__('message.asset_type_create')),
        createAssetType: @json(__('index.add_asset_types')),
        editAssetType: @json(__('index.edit_asset_types')),
        updateAssetType: @json(__('message.asset_type_update')),
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
                title: '@lang('index.change_status_confirm')',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
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
                title: '@lang('index.delete_confirmation')',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $(document).on('click', '.create-assetType', function() {
            $('#assetTypeModalLabel').text(translations.createClient);
            $('#submitButtonText').text(translations.create);
            $('#assetTypeForm').attr('action', '{{ route("admin.asset-types.store") }}');
            $('#formMethod').val('POST');

            $('#branch_id').val('').trigger('change');

            $('#name').val('');
            $('#assetTypeModal').modal('show');
        });

        $(document).on('click', '.edit-assetType', function() {
            const assetTypeId = $(this).data('id');

            $.ajax({
                type: 'GET',
                url: `{{ url('admin/asset-types') }}/${assetTypeId}/edit`,
                success: function(response) {
                    const assetType = response.assetTypeDetail;

                    $('#assetTypeModalLabel').text(translations.editClient);
                    $('#submitButtonText').text(translations.update);
                    $('#assetTypeForm').attr('action', `{{ url('admin/asset-types') }}/${assetTypeId}`);
                    $('#formMethod').val('PUT');


                    // Populate form fields
                    $('#name').val(assetType.name);
                    $('#branch_id').val(assetType.branch_id).trigger('change');

                    $('#assetTypeModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error: ' + (xhr.responseJSON.message || 'Failed to load assetType data'),
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });


        $('#assetTypeForm').on('submit', function(e) {
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
                    $('#assetTypeModal').modal('hide');
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: method === 'PUT' ? translations.updateAssetType : translations.addAssetType,
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
