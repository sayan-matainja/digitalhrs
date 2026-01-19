<script>
    const translations = {
        addLoanType: @json(__('message.loan_type_create')),
        createLoanType: @json(__('index.add_loan_types')),
        editLoanType: @json(__('index.edit_loan_types')),
        updateLoanType: @json(__('message.loan_type_update')),
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

        $(document).on('click', '.create-loanType', function() {
            $('#loanTypeModalLabel').text(translations.createLoanType);
            $('#submitButtonText').text(translations.create);
            $('#loanTypeForm').attr('action', '{{ route("admin.loan-types.store") }}');
            $('#formMethod').val('POST');

            @if(!isset(auth()->user()->branch_id))
            $('#branch_id').val('').trigger('change');
            @endif
            $('#name').val('');
            $('#minimum_amount').val('');
            $('#maximum_amount').val('');
            $('#interest_rate').val('');
            $('#interest_type').val('').trigger('change');
            $('#term').val('');
            $('#is_active').prop('checked', true);
            $('#loanTypeModal').modal('show');
        });

        $(document).on('click', '.edit-loanType', function() {
            const loanTypeId = $(this).data('id');

            $.ajax({
                type: 'GET',
                url: `{{ url('admin/loan-types') }}/${loanTypeId}/edit`,
                success: function(response) {
                    const loanType = response.LoanTypeDetail;

                    $('#loanTypeModalLabel').text(translations.editLoanType);
                    $('#submitButtonText').text(translations.update);
                    $('#loanTypeForm').attr('action', `{{ url('admin/loan-types') }}/${loanTypeId}`);
                    $('#formMethod').val('PUT');

                    // Populate form fields
                    @if(!isset(auth()->user()->branch_id))
                    $('#branch_id').val(loanType.branch_id).trigger('change');
                    @endif
                    $('#name').val(loanType.name);
                    $('#minimum_amount').val(loanType.minimum_amount);
                    $('#maximum_amount').val(loanType.maximum_amount);
                    $('#interest_rate').val(loanType.interest_rate);
                    $('#interest_type').val(loanType.interest_type).trigger('change');
                    $('#term').val(loanType.term);
                    $('#is_active').prop('checked', loanType.is_active == 1);

                    $('#loanTypeModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error: ' + (xhr.responseJSON.message || 'Failed to load loanType data'),
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });

        $(document).on('click', '.view-loanType', function() {
            const loanTypeId = $(this).data('id');

            $.ajax({
                type: 'GET',
                url: `{{ url('admin/loan-types') }}/${loanTypeId}`,
                success: function(response) {
                    const loanType = response.LoanTypeDetail;

                    @if(!isset(auth()->user()->branch_id))
                    $('#showBranch').text(loanType.branch ? loanType.branch.name : '--');
                    @endif
                    $('#showName').text(loanType.name);
                    $('#showMinAmount').text(loanType.minimum_amount);
                    $('#showMaxAmount').text(loanType.maximum_amount);
                    $('#showInterestRate').text(loanType.interest_rate + '%');
                    $('#showInterestType').text(loanType.interest_type.replace('_', ' ').toUpperCase());
                    $('#showTerm').text(loanType.term );
                    $('#showStatus').text(loanType.is_active == 1 ? '{{ __('index.active') }}' : '{{ __('index.inactive') }}');

                    $('#showLoanTypeModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error: ' + (xhr.responseJSON.message || 'Failed to load loanType data'),
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        });

        $('#loanTypeForm').on('submit', function(e) {
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
                    $('#loanTypeModal').modal('hide');
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: method === 'PUT' ? translations.updateLoanType : translations.addLoanType,
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
