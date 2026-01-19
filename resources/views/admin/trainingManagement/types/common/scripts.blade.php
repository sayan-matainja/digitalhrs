
<script>
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
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

    });

    function openModal(id = null,branch_id = null, title = '') {
        const modal = new bootstrap.Modal(document.getElementById('formModal'));
        const form = document.getElementById('trainingTypeForm');
        const methodField = document.getElementById('method-field');
        const modalTitle = document.getElementById('formModalLabel');
        const submitText = document.getElementById('form-submit-text');
        const formIcon = document.getElementById('form-icon');
        const titleInput = document.getElementById('title');
        const selectBranch = document.getElementById('branch_id');

        // Reset form
        form.reset();
        methodField.innerHTML = '';

        if (id) {
            // Edit mode
            form.action = `{{ url('admin/training-types') }}/${id}`;
            methodField.innerHTML = '@method("PUT")';
            modalTitle.textContent = '{{ __("index.edit") }} {{ __("index.training_types") }}';
            submitText.textContent = '{{ __("index.update") }}';
            formIcon.setAttribute('data-feather', 'edit-2');
            titleInput.value = title;
            if (branch_id) {
                selectBranch.value = branch_id;
            }
        } else {
            // Create mode
            form.action = '{{ route("admin.training-types.store") }}';
            modalTitle.textContent = '{{ __("index.add_training_types") }}';
            submitText.textContent = '{{ __("index.create") }}';
            formIcon.setAttribute('data-feather', 'plus');
        }

        feather.replace();

        modal.show();
    }

    document.getElementById('trainingTypeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        this.submit();
    });

</script>
