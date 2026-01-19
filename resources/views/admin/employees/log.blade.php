@extends('layouts.master')

@section('title', 'Employee Location')

@section('action', 'Location Log')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.employees.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Employee Location Log Filter</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.employee.log') }}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option selected disabled>{{ __('index.select_branch') }}</option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterData['branch_id']) && $filterData['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option selected disabled>{{ __('index.select_department') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option selected disabled>{{ __('index.select_employee') }}</option>
                        </select>
                    </div>

                        @if($bsEnabled)

                            <div class="col-lg-3 col-md-6 mb-4">
                                <input type="text" id="nepali_startDate" class="form-control nepaliDate" name="date"
                                       value="{{ $filterData['date'] ?? \App\Helpers\AppHelper::getCurrentDateInBS() }}">
                            </div>
                        @else
                            <div class="col-lg-3 col-md-6 mb-4">
                                <input type="date" class="form-control" name="date"
                                       value="{{ $filterData['date'] ?? now()->format('Y-m-d') }}">
                            </div>
                        @endif

                    <div class="col-lg-3 col-md-6 d-md-flex">
                        <button type="submit" class="btn btn-block btn-success me-md-2 me-0 mb-md-4 mb-2">
                            {{ __('index.filter') }}
                        </button>
                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4"
                           href="{{ route('admin.employee.log') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Location Logs</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="text-center">SN</th>
                            <th>{{ __('index.employee_name') }}</th>
                            <th class="text-center">{{ __('index.date') }}</th>
                            <th class="text-center">{{ __('index.location') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($logData as $userId => $logs)
                            @php
                                $user = $logs->first()->employee; // Get user details
                                $date = $logs->first()->created_at; // Get user details
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $user->name }}</td>
                                <td class="text-center">

                                    {{ \App\Helpers\AppHelper::formatDateForView($date)  }}
                                    @if($logs->count() > 1)
                                        <span class="badge bg-primary ms-2">{{ $logs->count() }} Records</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-xs toggle-details"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#details-{{ $userId }}"
                                            aria-expanded="false"
                                            aria-controls="details-{{ $userId }}">
                                        View Details
                                    </button>
                                </td>

                            </tr>
                            <tr class="collapse" id="details-{{ $userId }}">
                                <td colspan="5">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                        <tr>
                                            <th class="text-center">SN</th>
                                            <th class="text-center">Time</th>
                                            <th class="text-center">Location</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($logs as $log)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td class="text-center">
                                                    {{ \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView(\App\Helpers\AppHelper::check24HoursTimeAppSetting(), $log->created_at) }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                          title="Show Location"
                                                          data-bs-toggle="modal"
                                                          data-href="{{ 'https://maps.google.com/maps?q=' . ($log->latitude ?? '0') . ',' . ($log->longitude ?? '0') . '&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                          data-bs-target="#addslider">
                                                        View Location
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <!-- Modal for Google Maps -->
                    <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addsliderLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addsliderLabel">Location   <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button></h5>

                                </div>
                                <div class="modal-body">
                                    <iframe id="locationFrame" width="100%" height="400" frameborder="0" style="border:0" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.attendance.common.filter_scripts')
    <script>
        $(document).ready(function () {
            // Handle location modal
            $('.checkLocation').on('click', function () {
                const mapUrl = $(this).data('href');
                $('#locationFrame').attr('src', mapUrl);
            });

            // Clear modal iframe when closed to prevent memory issues
            $('#addslider').on('hidden.bs.modal', function () {
                $('#locationFrame').attr('src', '');
            });

            $('.nepaliDate').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
            });
        });
    </script>
@endsection
