@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', __('index.employee_attendance_lists'))
@section('styles')
    <style>
        .year_group, .date_range_group {
            display: none;
        }
    </style>
@endsection

@section('main-content')
    @php
        if ($isBsEnabled) {
            $filterData['min_year'] = '2076';
            $filterData['max_year'] = '2089';
            $nepaliDate = \App\Helpers\AppHelper::getCurrentNepaliYearMonth();
            $filterData['current_year'] = $nepaliDate['year'];
        } else {
            $filterData['min_year'] = '2020';
            $filterData['max_year'] = '2033';
            $filterData['current_year'] = now()->format('Y');
        }
    @endphp
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.attendance.common.breadcrumb')
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_report')  }}</h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.attendance.export') }}" method="get">
                    <div class="row align-items-center">

                        @if(!isset(auth()->user()->branch_id))
                            <div class="col-lg-3 col-md-6 mb-4">

                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option selected disabled>{{ __('index.select_branch') }}
                                    </option>
                                    @if(isset($companyDetail))
                                        @foreach($companyDetail->branches()->get() as $key => $branch)
                                            <option value="{{$branch->id}}"
                                                {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id']  == $branch->id) ? 'selected': '' }}>
                                                {{ucfirst($branch->name)}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @endif


                        <div class="col-lg-3 col-md-6 mb-4">

                            <select class="form-select" name="department_id" id="department_id">
                                <option selected disabled> {{  __('index.select_department') }} </option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">

                            <select class="form-select" name="employee_id" id="employee_id">
                                <option selected disabled>{{  __('index.select_employee') }}</option>
                            </select>
                        </div>


                        <div class="col-lg-3 col-md-6 mb-4">

                            <select class="form-select" name="date_option" id="date_option">
                                <option selected disabled>{{  __('index.select_date_option') }}</option>
                                <option value="year">Year</option>
                                <option value="range">Date range</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4 year_group">
                            <input type="number" min="{{ $filterData['min_year'] }}"
                                   max="{{ $filterData['max_year'] }}" step="1"
                                   placeholder="{{ __('index.attendance_year_example', ['year' => $filterData['min_year']]) }}"
                                   id="year"
                                   name="year"
                                   value="{{ $filterData['current_year'] }}"
                                   class="form-control">
                        </div>

                        @if($isBsEnabled)

                            <div class="col-lg-3 col-md-6 mb-4 date_range_group">
                                <input type="text" class="form-control startNpDate" id="start_date" name="start_date"
                                       required value="" autocomplete="off" placeholder="Start Date">
                            </div>
                            <div class="col-lg-3 col-md-6 mb-4 date_range_group">
                                <input type="text" class="form-control npDeadline" id="end_date" name="end_date"
                                       value=""
                                       autocomplete="off" placeholder="End Date">
                            </div>
                        @else
                            <div class="col-lg-3 col-md-4 mb-4 date_range_group">
                                <input type="text" class="form-control" id="attendance_date" name="attendance_date"
                                       value=""/>
                            </div>
                        @endif
                        <div class="col-lg-3 col-md-4 d-md-flex">
                            <button type="submit" class="btn btn-block btn-secondary me-md-2 me-0 mb-md-4 mb-2">{{ __('index.csv_export') }}</button>
                            <a class="btn btn-block btn-primary me-md-2 me-0 mb-4"
                               href="{{ route('admin.attendance.export') }}">{{ __('index.reset') }}</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </section>

@endsection

@section('scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(function () {
            $('input[name="attendance_date"]').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('input[name="attendance_date"]').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                addParameterDownloadExcel();
            });

            $('input[name="attendance_date"]').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                addParameterDownloadExcel();
            });
        });

        @if($isBsEnabled)
        $('#start_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

        $('#end_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });
        @endif

        $(document).ready(function () {
            // Function to toggle visibility based on selected option
            function toggleDateFields() {
                var selectedOption = $('#date_option').val();

                if (selectedOption === 'year') {
                    $('.year_group').show();
                    $('.date_range_group').hide();
                    // Clear date range inputs when switching to year-wise
                    $('#start_date, #end_date, #attendance_date').val('');
                } else if (selectedOption === 'range') {
                    $('.year_group').hide();
                    $('.date_range_group').show();
                    // Clear year/month when switching to date-wise (optional)
                    $('#year').val('{{ $filterData['current_year'] }}');
                    $('#month').val('');
                } else {
                    // When nothing is selected (initial state)
                    $('.year_group').hide();
                    $('.date_range_group').hide();
                }
            }

            // Run on page load
            toggleDateFields();

            // Run whenever the date_option changes
            $('#date_option').on('change', function () {
                toggleDateFields();
            });
        });
    </script>
    @include('admin.attendance.common.filter_scripts')

@endsection

