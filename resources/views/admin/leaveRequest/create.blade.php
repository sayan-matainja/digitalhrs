@extends('layouts.master')

@section('title',__('index.leave_request'))

@section('action',__('index.create'))

@section('main-content')
    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveRequest.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form class="forms-sample"
                      action="{{route('admin.employee-leave-request.store')}}"
                      method="post">
                    @csrf

                    <div class="row">
                        <div class="col-lg-3 mb-3">
                            <label for="leave_type" class="form-label">{{ __('index.leave_type') }}<span style="color: red">*</span></label>
                            <select class="form-select" id="leaveType" name="leave_type_id" required>
                                <option selected disabled> {{ __('index.select_leave_type') }}</option>
                                @foreach($leaveTypes as $leave)
                                    <option value="{{ $leave->id }}" @if( old('leave_type_id')  == $leave->id) selected @endif > {{ $leave->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <label for="leave_for" class="form-label">{{ __('index.leave_for') }}<span style="color: red">*</span></label>
                            <select class="form-select" id="leave_for" name="leave_for" required>
                                <option selected disabled>{{ __('index.select_leave_for') }} </option>
                                <option value="full_day">Full Day</option>
                                <option value="half_day">Half Day</option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label for="leave_from" class="form-label">{{ __('index.from_date') }}<span style="color: red">*</span></label>
                            @if($bsEnabled)
                                <input type="text" class="form-control leave_from" id="leave_from" value="{{old('leave_from')}}" name="leave_from" autocomplete="off">
                            @else
                                <input class="form-control" type="date" name="leave_from" value="{{old('leave_from')}}" required  />
                            @endif
                        </div>


                        <div class="col-lg-3 col-md-6 mb-4">
                            <label for="leave_to" class="form-label">{{ __('index.to_date') }}</label>
                            @if($bsEnabled)
                                <input type="text" class="form-control leave_to" id="leave_to" value="{{old('leave_to')}}" name="leave_to" autocomplete="off">
                            @else
                                <input class="form-control" type="date" name="leave_to" value="{{old('leave_to')}}"  />

                            @endif
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <label for="leave_in" class="form-label">{{ __('index.leave_in') }}<span style="color: red">*</span></label>
                            <select class="form-select" id="leave_in" name="leave_in" required>
                                <option selected disabled>{{ __('index.select_leave_in') }} </option>
                                <option value="first_half">First Half</option>
                                <option value="second_half">Second Half</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <label for="info" class="form-label">{{ __('index.total_duration') }} </label>
                            <input class="form-control bg-light" type="text" readonly id="no_of_days" name="no_of_days" value="{{old('end_time')}}"  />
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="note" class="form-label">{{ __('index.reason') }}  <span style="color: red"> *</span> </label>
                            <textarea class="form-control" name="reasons" rows="5" >{{  old('reasons') }}</textarea>
                        </div>

                        <div class="text-start">
                            <button type="submit" class="btn btn-primary">
                                {{ __('index.submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            @if($bsEnabled)
            $('.leave_from').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
                onChange: function () {
                    calculateDays();
                }
            });

            $('.leave_to').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
                onChange: function () {
                    calculateDays();
                }
            });
            @else
            $('input[name="leave_from"], input[name="leave_to"]').on('change', function () {
                calculateDays();
            });
            @endif

            function calculateDays() {
                let from = $('input[name="leave_from"]').val();
                let to   = $('input[name="leave_to"]').val();
                let noOfDaysField = $('#no_of_days');

                if (!from || !to) {
                    noOfDaysField.val('');
                    return;
                }

                try {
                    @if($bsEnabled)
                    if (typeof NepaliFunctions !== "undefined") {
                        // Convert BS → AD using library
                        let fromAd = NepaliFunctions.BS2AD(from);
                        let toAd   = NepaliFunctions.BS2AD(to);
                        processDates(fromAd, toAd);
                    } else {
                        console.warn("NepaliFunctions not available. Duration will be empty.");
                        noOfDaysField.val('');
                    }
                    @else
                    processDates(from, to);
                    @endif
                } catch (e) {
                    console.error("Error calculating duration:", e.message);
                    noOfDaysField.val('');
                }
            }

            function processDates(from, to) {
                let noOfDaysField = $('#no_of_days');

                let start = new Date(from);
                let end   = new Date(to);

                if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                    noOfDaysField.val('');
                    return;
                }

                let diff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                noOfDaysField.val(diff > 0 ? diff : 0);
            }
        });

        $(document).ready(function () {
            $('#leave_for').change(function() {
                var value = $(this).val();
                var leaveToDiv = $('#leave_to').closest('.col-lg-3.col-md-6.mb-4');
                var leaveInDiv = $('#leave_in').closest('.col-lg-3.col-md-6.mb-4');
                var infoDiv = $('#no_of_days').closest('.col-lg-3.col-md-6.mb-4');

                if (value === 'full_day') {
                    leaveToDiv.show();
                    $('#leave_to').prop('required', true);
                    infoDiv.show();
                    leaveInDiv.hide();
                    $('#leave_in').prop('required', false);
                    calculateDays(); // Recalculate if dates are set
                } else if (value === 'half_day') {
                    leaveToDiv.hide();
                    $('#leave_to').prop('required', false).val('');
                    infoDiv.hide();
                    $('#no_of_days').val('');
                    leaveInDiv.show();
                    $('#leave_in').prop('required', true);
                }
            }).trigger('change');
        });

    </script>
@endsection




