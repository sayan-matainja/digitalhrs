@php use App\Helpers\AppHelper; @endphp
@php use App\Helpers\AttendanceHelper; @endphp
@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', __('index.employee_attendance_detail'))

@section('button')
    <a href="{{ route('admin.attendances.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon"
                                                  data-feather="arrow-left"></i> {{ __('index.back') }}</button>
    </a>
@endsection

@section('main-content')
    @php
    if ($isBsEnabled) {
        $filterData['min_year'] = '2076';
        $filterData['max_year'] = '2089';
        $filterData['month'] = 'np';
        $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
        $filterData['current_year'] = $nepaliDate['year'];
        $filterData['current_month'] = $nepaliDate['month'];
    } else {
        $filterData['min_year'] = '2020';
        $filterData['max_year'] = '2033';
        $filterData['current_year'] = now()->format('Y');
        $filterData['current_month'] = now()->month;
        $filterData['month'] = 'en';
    }
    @endphp

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.attendance.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_of') . ' ' .ucfirst($userDetail->name) }}</h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.attendances.show', $userDetail->id) }}"
                    method="get">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <input type="number" min="{{ $filterData['min_year'] }}"
                                max="{{ $filterData['max_year'] }}" step="1"
                                placeholder="{{ __('index.attendance_year_example', ['year' => $filterData['min_year']]) }}"
                                id="year"
                                name="year"
                                value="{{ $filterParameter['year'] }}"
                                class="form-control">
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <select class="form-select form-select-lg" name="month" id="month">
                                <option
                                    value="" {{ !isset($filterParameter['month']) ? 'selected' : '' }}>{{ __('index.all_month') }}</option>
                                @foreach($months as $key => $value)
                                    <option
                                        value="{{ $key }}" {{ (isset($filterParameter['month']) && $key == $filterParameter['month']) ? 'selected' : '' }}>
                                        {{ $value[$filterData['month']] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-4 mb-4 d-flex gap-2">
                            <button type="submit" class="btn btn-block btn-success">{{ __('index.filter') }}</button>
                            @can('attendance_csv_export')
                                <button type="button" id="download-excel"
                                        data-href="{{ route('admin.attendances.show', $userDetail->id) }}"
                                        class="btn btn-block btn-secondary">
                                    {{ __('index.csv_export') }}
                                </button>
                            @endcan
                            <a class="btn btn-block btn-primary"
                            href="{{ route('admin.attendances.show', $userDetail->id) }}">{{ __('index.reset') }}</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.total_days_in_month') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalDays']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.present_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalPresent']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.absent_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalAbsent']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.weekend_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalWeekend']) : 0 }}</h5>
                    </div>
                </div>
            </div>

            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.holiday_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalHoliday']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.leave_days') }}</h6>
                        <h5 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? number_format($attendanceSummary['totalLeave']) : 0 }}</h5>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.working_hours') }}</h6>
                        <h6 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? $attendanceSummary['totalWorkingHours'] : '-' }}</h6>
                    </div>
                </div>
            </div>
            <div class=" col-xl-3 col-md-6 mb-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center">
                        <h6 class="card-title w-100 mb-0 border-end">{{ __('index.worked_hours') }}</h6>
                        <h6 class="text-primary ps-5 text-nowrap">{{ $attendanceSummary ? $attendanceSummary['totalWorkedHours'] : '-' }}</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.attendance_details_of', ['month' => $monthName]) }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>{{ __('index.date') }}</th>
                            <th style="text-align: center;">{{ __('index.check_in_at') }}</th>
                            <th style="text-align: center;">{{ __('index.check_out_at') }}</th>
                            <th style="text-align: center;">{{ __('index.worked_hour') }}</th>
                            <th style="text-align: center;">{{ __('index.status') }}</th>
                            <th style="text-align: center;">{{ __('index.shift') }}</th>
                            @can('attendance_update')
                                <th style="text-align: center;">{{ __('index.action') }}</th>
                            @endcan
                        </tr>
                        </thead>

                            @php
                            $changeColor = [
                                0 => 'danger',
                                1 => 'success',
                            ]
                            @endphp

                        @forelse($attendanceDetail as $dayIndex => $dayData)
                            @php
                                $totalMinutes = 0;
                                $isFirstIteration = true;

                            @endphp
                        <tbody>
                            @if(isset($dayData['data']) && count($dayData['data']) > 0)
                                @foreach($dayData['data'] as $attendance)

                                    @php
                                        $totalMinutes += $attendance['worked_hour'];
                                    @endphp
                                    <tr>

                                        @if($isFirstIteration)
                                            <td>{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}</td>
                                            @php
                                                $isFirstIteration = false; // Set to false after displaying the date for the first time
                                            @endphp
                                        @else
                                            <td></td>
                                        @endif
                                            @if(isset($attendance['shift'])  && ($attendance['shift'] == \App\Enum\ShiftTypeEnum::night->value))
                                                @if(isset($attendance['night_checkin']))
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($attendance['check_in_type']).' '.__('index.checkin') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_in_latitude'].','.$attendance['check_in_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'}}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{  \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkin']) }}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                                @if(isset($attendance['night_checkout']))
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($attendance['check_out_type']).' '.__('index.checkout') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_out_latitude'].','.$attendance['check_out_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{ \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkout'])}}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                            @else
                                                @if(isset($attendance['check_in_at']))
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_in_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkin_location') : strtoupper($attendance['check_in_type']).' '.__('index.checkin') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_in_latitude'].','.$attendance['check_in_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed'}}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{  \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_in_at']) }}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                                @if(isset($attendance['check_out_at']))
                                                    <td class="text-center">
                                                        <span class="btn btn-outline-secondary btn-xs checkLocation"
                                                                title="{{$attendance['check_out_type'] == \App\Enum\EmployeeAttendanceTypeEnum::wifi->value ? __('index.show_checkout_location') : strtoupper($attendance['check_out_type']).' '.__('index.checkout') }}"
                                                                data-bs-toggle="modal"
                                                                data-href="{{'https://maps.google.com/maps?q='.$attendance['check_out_latitude'].','.$attendance['check_out_longitude'].'&t=&z=20&ie=UTF8&iwloc=&output=embed' }}"
                                                                data-bs-target="{{'#addslider' }}">
                                                            {{  \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting,  $attendance['check_out_at']) }}
                                                        </span>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                            @endif
                                        <td  class="text-center">
                                            {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['worked_hour']) }}
                                        </td>
                                        @if(!is_null($attendance['attendance_status']))
                                            <td class="text-center">
                                                <a class="btn btn-{{ $changeColor[$attendance['attendance_status']] }} btn-xs"
                                                     title="{{ __('index.change_attendance_status') }}">
                                                    {{ ($attendance['attendance_status'] == \App\Models\Attendance::ATTENDANCE_APPROVED) ? __('index.present') : __('index.rejected') }}
                                                </a>
                                            </td>
                                        @else
                                            <td  class="text-center">
                                                <span class="btn btn-light btn-xs disabled">
                                                    {{ __('index.pending') }}
                                                </span>
                                            </td>
                                        @endif
                                            @if($attendance['shift'])
                                                <td class="text-center">
                                                    <span class="btn btn-info btn-xs">
                                                        {{ ucfirst($attendance['shift']) }}
                                                    </span>
                                                </td>
                                            @else
                                                <td></td>
                                            @endif
                                            @can('attendance_update')
                                                <td class="text-center">

                                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                        @if(isset($attendance['shift'])  && ($attendance['shift'] == \App\Enum\ShiftTypeEnum::night->value))
                                                            <li class="me-2">
                                                                <a href=""
                                                                    class="editNightAttendance"
                                                                    data-href="{{ route('admin.night_attendances.update', $attendance['id']) }}"
                                                                    data-in="{{ $attendance['night_checkin'] }}"
                                                                    data-out="{{ $attendance['night_checkout'] ?? null }}"
                                                                    data-remark="{{ $attendance['edit_remark'] }}"
                                                                    data-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $attendance['attendance_date']) }}"
                                                                    data-name="{{ ucfirst($userDetail->name) }}"
                                                                    title="{{ __('index.edit_attendance_time') }}"
                                                                >
                                                                    <i class="link-icon" data-feather="edit"></i>
                                                                </a>
                                                            </li>
                                                        @else
                                                            @if(count($dayData['data']) < $multipleAttendance && isset($attendance['check_out_at']))
                                                                <li class="me-2">
                                                                    <a href=""
                                                                    class="addEmployeeAttendance"
                                                                    data-href="{{ route('admin.attendances.store') }}"
                                                                    data-name="{{ ucfirst($userDetail->name) }}"
                                                                    data-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                                    data-cdate="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $attendance['attendance_date']) }}"
                                                                    data-user_id="{{ $userDetail->id }}"
                                                                    title="{{ __('index.add_attendance_time') }}">
                                                                    <i class="link-icon" data-feather="plus-circle"></i>
                                                                </a>
                                                                </li>
                                                            @endif
                                                            @if(isset($attendance['id']))
                                                                <li class="me-2">
                                                                    <a href=""
                                                                        class="editAttendance"
                                                                        data-href="{{ route('admin.attendances.update', $attendance['id']) }}"
                                                                        data-in="{{ date('H:i', strtotime($attendance['check_in_at'])) }}"
                                                                        data-out="{{ $attendance['check_out_at'] ? date('H:i', strtotime($attendance['check_out_at'])) : null }}"
                                                                        data-remark="{{ $attendance['edit_remark'] }}"
                                                                        data-date="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $attendance['attendance_date']) }}"
                                                                        data-name="{{ ucfirst($userDetail->name) }}"
                                                                        title="{{ __('index.edit_attendance_time') }}">
                                                                        <i class="link-icon" data-feather="edit"></i>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endif
                                                        @can('attendance_delete')
                                                            <li class="me-2">
                                                                <a class="deleteAttendance" href="{{ route('admin.attendance.delete', $attendance['id']) }}">
                                                                    <i class="link-icon"  data-feather="delete"></i>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </td>
                                            @endcan
                                    </tr>
                                @endforeach

                                @if($multipleAttendance > 1 && count($dayData['data']) > 1)
                                    <tr class="bg-light">
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        @php
                                            $hours = floor($totalMinutes / 60);
                                            $minutes = $totalMinutes % 60;
                                            if ($hours == 0 && $minutes == 0) {
                                                $worked_hours = '';
                                            } elseif ($hours == 0) {
                                                $worked_hours = $minutes . ' min';
                                            } elseif ($minutes == 0) {
                                                $worked_hours = $hours . ' hr';
                                            } else {
                                                $worked_hours = $hours . ' hr ' . $minutes . ' min';
                                            }
                                        @endphp
                                        <th class="text-center">{{ $worked_hours }}</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>

                                    </tr>
                                @endif
                            @else
                                <tr>
                                    <td>{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}</td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    @php
                                        $reason = (\App\Helpers\AttendanceHelper::getHolidayOrLeaveDetail($dayData['attendance_date'], $userDetail->id));
                                    @endphp
                                    @if($reason)
                                        <td class="text-center">
                                            <span class="btn btn-outline-secondary btn-xs">
                                                {{ $reason }}
                                            </span>
                                        </td>
                                    @endif
                                    <td  class="text-center"><i class="link-icon" data-feather="x"></i></td>
                                    <td  class="text-center">
                                        @if(isset($reason) && $reason == 'Absent')
                                            <a href=""
                                                class="addEmployeeAttendance"
                                                data-href="{{ route('admin.attendances.store') }}"
                                                data-name="{{ ucfirst($userDetail->name) }}"
                                                data-date="{{ date('Y-m-d', strtotime($dayData['attendance_date'])) }}"
                                                data-cdate="{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}"
                                                data-user_id="{{ $userDetail->id }}"
                                                title="{{ __('index.add_attendance_time') }}">
                                                <i class="link-icon" data-feather="plus-circle"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="100%">
                                        <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                    </td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <iframe id="iframeModalWindow" class="attendancelocation" height="500px" width="100%" src=""
                                name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.attendance.common.edit-attendance-form')
        @include('admin.attendance.common.create-attendance-form')
        @include('admin.attendance.common.edit-night-attendance-form')

    </section>
@endsection

@section('scripts')
    @include('admin.attendance.common.scripts')
@endsection

