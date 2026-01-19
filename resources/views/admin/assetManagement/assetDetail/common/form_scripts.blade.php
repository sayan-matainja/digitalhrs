<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $("#branch_id").select2({});
        $("#type").select2({});

        let warranty = $('#warranty_available').val();
        if (warranty == 1 ) {
            $('#warranty_end_date').attr('required', 'true');
        } else {
            $('#warranty_end_date').removeAttr('required');
        }

        $('#image').change(function(){
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

        $('#warranty_available').change(function(event){
            event.preventDefault()
            let warrantyAvailable = $(this).val();
            if(warrantyAvailable == 0){
                $('#warranty_end_date').val('');
               $('#warranty_end_date').removeAttr('required')
           }else{
               $('#warranty_end_date').attr('required','true')
           }
        });


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });





    });

    $(document).ready(function () {
        const loadClientAndUsers = async () => {
            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const selectedBranchId = isAdmin ? $('#branch_id').val() : defaultBranchId;


            // Get existing values (for edit forms or old input)
            let assetTypeId = "{{ $assetDetail->type_id ?? old('type_id') ?? '' }}";

            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-asset-data') }}/${selectedBranchId}`,
                });

                // Clear existing options
                $('#type').empty();

                // Populate types
                if (!assetTypeId) {
                    $('#type').append('<option value="" disabled selected>{{ __('index.select_asset_type') }}</option>');
                }
                if (response.types && response.types.length > 0) {
                    response.types.forEach(client => {
                        $('#type').append(
                            `<option value="${client.id}" ${client.id == assetTypeId ? 'selected' : ''}>${client.name}</option>`
                        );
                    });
                } else {
                    $('#type').append('<option disabled>{{ __("index.asset_type_not_found") }}</option>');
                }




            } catch (error) {
                console.error('Error loading data:', error);
                $('#type').append('<option disabled>{{ __("index.error_loading_asset_types") }}</option>');
            }
        };

        // Event Listeners
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        if (isAdmin) {
            $('#branch_id').on('change', loadClientAndUsers);
            $('#branch_id').trigger('change');
        } else {
            loadClientAndUsers();
        }
    });
</script>
