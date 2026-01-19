<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
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
                title: '{{ __('index.confirm_change_status') }}',
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

        $('.deleteOfficeTime').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_office_time_confirm') }}',
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

        $('body').on('click', '#showOfficeTimeDetail', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            $.get(url, function (data) {
                $('.modal-title').html('{{ __('index.office_time_detail') }}');
                $('.opening_time').text(data.data.opening_time);
                $('.closing_time').text((data.data.closing_time));
                $('.halfday_mark_time').text((data.data.halfday_mark_time));

                $('.checkin_before').text(data.data.checkin_before === null ? '' : `${data.data.checkin_before} minutes`);
                $('.checkin_after').text(data.data.checkin_after === null ? '' : `${data.data.checkin_after} minutes`);
                $('.checkout_before').text(data.data.checkout_before === null ? '' : `${data.data.checkout_before} minutes`);
                $('.checkout_after').text(data.data.checkout_after === null ? '' : `${data.data.checkout_after} minutes`);

                $('.shift').text((data.data.shift));
                $('#addslider').modal('show');
            })
        }).trigger("change");

        $('#apply_rule').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('.late_rule').removeClass('d-none');
            }else{
                $('.late_rule').addClass('d-none');
            }
        });



        $('#is_early_check_in').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('#earlyCheckIn').removeClass('d-none');
            }else{
                $('#earlyCheckIn').addClass('d-none');
            }
        });

        $('#is_late_check_in').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('#lateCheckIn').removeClass('d-none');
            }else{
                $('#lateCheckIn').addClass('d-none');
            }
        });

        $('#is_early_check_out').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('#earlyCheckOut').removeClass('d-none');
            }else{
                $('#earlyCheckOut').addClass('d-none');
            }
        });

        $('#is_late_check_out').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;

            if(status === 1){
                $('#lateCheckOut').removeClass('d-none');
            }else{
                $('#lateCheckOut').addClass('d-none');
            }
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        const openingInput = document.getElementById('opening_time');
        const closingInput = document.getElementById('closing_time');
        const halfdayInput = document.getElementById('halfday_mark_time');
        const shiftTypeInput = document.getElementById('shift_type'); // Assuming shift_type input has this ID

        function calculateMidpoint() {
            const openingVal = openingInput.value;
            const closingVal = closingInput.value;
            const shiftTypeVal = shiftTypeInput ? shiftTypeInput.value : ''; // Fallback if no shift_type

            if (!openingVal || !closingVal) {
                return; // Cannot calculate without both times
            }

            let openingMinutes = parseTimeToMinutes(openingVal);
            let closingMinutes = parseTimeToMinutes(closingVal);
            let isNightShift = shiftTypeVal === '{{ \App\Enum\ShiftTypeEnum::night->value }}'; // Adjust enum value as needed, e.g., 'night'

            let duration;
            let midMinutes;

            if (!isNightShift || closingMinutes > openingMinutes) {
                // Day shift or non-wrapping
                duration = closingMinutes - openingMinutes;
                midMinutes = openingMinutes + (duration / 2);
            } else {
                // Night shift wrapping around midnight
                duration = (1440 - openingMinutes) + closingMinutes;
                midMinutes = openingMinutes + (duration / 2);
                if (midMinutes >= 1440) {
                    midMinutes -= 1440;
                }
            }

            const midTime = minutesToTime(midMinutes);
            halfdayInput.value = midTime;
        }

        function parseTimeToMinutes(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        }

        function minutesToTime(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = Math.floor(minutes % 60);
            return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
        }

        openingInput.addEventListener('change', calculateMidpoint);
        closingInput.addEventListener('change', calculateMidpoint);
        if (shiftTypeInput) {
            shiftTypeInput.addEventListener('change', calculateMidpoint);
        }

        // Initial calculation if editing and values are present
        if (openingInput.value && closingInput.value && halfdayInput.value === '') {
            calculateMidpoint();
        }
    });
</script>
