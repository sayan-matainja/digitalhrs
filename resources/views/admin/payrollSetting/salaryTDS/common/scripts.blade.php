<script>
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.errorSalaryTDS').hide();

        $('.successSalaryTDS').hide();

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            var status = $(this).prop('checked') === true ? 1 : 0;
            var href = $(this).attr('href');
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
                title: `{{__('index.delete_confirmation')}}`,
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
                +'<button type="button" class="btn btn-md btn-danger remove" title="remove Salary TDS" id="removeSalaryTDS"> {{ __('index.remove_checklist_text') }} </button>'+
                '</div>';
            $(".salaryTDSList").first().clone().find("input").val("").end().append(removeButton).appendTo("#addSalaryTDS");
            $(".addButtonSection:last").remove();
        })

        $("#addSalaryTDS").on('click', '.remove', function(){
            $(this).closest(".salaryTDSList").remove();
        });

        $('#salaryTDSAdd').submit(function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            let url = $(this).attr('action');
            let type = $(this).attr('method');
            let redirectUrlAfterSuccess = "{{route('admin.salary-tds.index')}}";
            $.ajax({
                url: url,
                type: type,
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if(response.status_code == 200) {
                        let message = response.message;
                        $('#showSuccessResponse').removeClass('d-none');
                        $('.successSalaryTDS').show();
                        $('.successMessage').text(message);
                        $('div.alert.alert-success').not('.alert-important').delay(2000).slideUp(900);
                        window.location.href = redirectUrlAfterSuccess;
                    }
                },
                error: function(error) {
                    let errorMessage = error.responseJSON.message;
                    $('#showErrorResponse').removeClass('d-none');
                    $('.errorSalaryTDS').show();
                    $('.errorMessage').text(errorMessage);
                    $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
                }
            });
        });
    });
</script>
