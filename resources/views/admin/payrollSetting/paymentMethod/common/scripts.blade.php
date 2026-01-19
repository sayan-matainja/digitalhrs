<script>
    $('document').ready(function(e){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.errorPaymentMethod').hide();

        $('.successPaymentMethod').hide();

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: `{{ __('index.change_status_confirm') }}`,
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
                title: `{{ __('index.delete_confirmation') }}`,
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

        $('#add').on('click',function(){
            let removeButton = '<div class="col-lg-2 text-center removeButton">'
                +'<button type="button" class="btn btn-sm btn-danger remove" title="remove paymentMethod" id="removePaymentMethod"> Remove </button>'+
                '</div>';
            $(".paymentMethodList").first().clone().find("input").val("").end().append(removeButton).appendTo("#addPaymentMethod");
            $(".addButtonSection:last").remove();
        })

        $("#addPaymentMethod").on('click', '.remove', function(){
            $(this).closest(".paymentMethodList").remove();
        });

        $('.editPaymentMethod').click(function(e){
            e.preventDefault();
            let url = $(this).attr('href');
            let paymentName = $(this).data('name');
            $('#updateForm').attr('action', url);
            $('#payment_method').val(paymentName);
        });


        $('#updateForm').on('submit',function(e){
            e.preventDefault()
            let url = $(this).attr('action');
            let formData = $(this).serialize();
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
            }).done(function(response) {
                if(response.status_code == 200){
                    $('#addslider').modal('hide');
                    let message = response.message;
                    let updatedName = response.data.name;
                    let row = $('tr[data-id="' + response.data.id + '"]');
                    row.find('.name').text(updatedName);
                    row.find('.editPaymentMethod').data('name',updatedName);
                    $('#showSuccessResponse').removeClass('d-none');
                    $('.successPaymentMethod').show();
                    $('.successMessage').text(message);
                    $('div.alert.alert-success').not('.alert-important').delay(2000).slideUp(900);
                }
            }).error(function(error){
                let errorMessage = error.responseJSON.message;
                $('#showErrorResponse').removeClass('d-none');
                $('.errorPaymentMethod').show();
                $('.errorMessage').text(errorMessage);
                $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
            });
        });


    });
</script>
