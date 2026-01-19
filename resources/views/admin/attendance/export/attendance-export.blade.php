<table>
    <thead>
    <tr>
        <th colspan="5" style="text-align: center">
            <strong>{{ ucfirst($employeeDetail->name) }}
                @if(count($attendanceRecordDetail) > 0)
                    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                        {{ \App\Helpers\AppHelper::MONTHS[date("n", strtotime($attendanceRecordDetail[0]['attendance_date']))]['np'] }}
                    @else
                        {{ date("F", strtotime($attendanceRecordDetail[0]['attendance_date'])) }}
                    @endif
                @endif
                {{ __('index.attendance_report') }}
            </strong>
        </th>
    </tr>
    <tr>
        <th><b>{{ __('index.date') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_in_at') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_in_latitude') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_in_longitude') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_out_at') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_out_latitude') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.check_out_longitude') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.total_worked_hours') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.overtime') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.undertime') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.attendance_status') }}</b></th>
        <th style="text-align: center;"><b>{{ __('index.shift') }}</b></th>

    </tr>
    </thead>
    @php
        $changeColor = [
            0 => 'danger',
            1 => 'success',
        ];
        $netTotalMinutes = 0;
        $netTotalOverTime = 0;
        $netTotalUnderTime = 0;
        $netTotalLeave = 0;
        $netTotalAbsent = 0;
        $workingHours = 0;
    @endphp
    @forelse($attendanceRecordDetail as $dayIndex => $dayData)
        @php
            $totalMinutes = 0;
            $totalOverTime = 0;
            $totalUnderTime = 0;

            $isFirstIteration = true;

        @endphp
        <tbody>
            @if(isset($dayData['data']) && count($dayData['data']) > 0)
                @foreach($dayData['data'] as $attendance)

                    @php
                        $workingHours = $attendance['working_hour'];

                        $totalMinutes += $attendance['worked_hour'];
                        if($multipleAttendance <= 1 && count($dayData['data']) <= 1){
                            $totalOverTime += $attendance['overtime'];
                            $netTotalOverTime += $attendance['overtime'];
                            $totalUnderTime += $attendance['undertime'];
                            $netTotalUnderTime += $attendance['undertime'];
                        }

                        $netTotalMinutes += $attendance['worked_hour'];
                    @endphp
                    <tr>

                        @if($isFirstIteration)
                            <td>{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}</td>
                            @php
                                $isFirstIteration = false;
                            @endphp
                        @else
                            <td></td>
                        @endif
                        @if($attendance['check_in_at'])
                            <td >
                                {{  \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance['check_in_at']) ?? '' }}
                            </td>
                        @elseif(isset($attendance['night_checkin']))
                            <td >
                                {{  \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkin']) ?? '' }}
                            </td>
                        @else
                            <td></td>
                        @endif
                            <td>
                                {{ $attendance['check_in_latitude'] }}
                            </td>
                            <td>
                                {{ $attendance['check_in_longitude'] }}
                            </td>
                        @if(isset($attendance['check_out_at']))
                            <td >
                               {{ $attendance['check_out_at'] ? \App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting,  $attendance['check_out_at']) : '' }}
                            </td>
                        @elseif(isset($attendance['night_checkout']))
                            <td >
                                {{ $attendance['night_checkout'] ? \App\Helpers\AttendanceHelper::changeNightAttendanceFormat($appTimeSetting, $attendance['night_checkout']) : '' }}
                            </td>
                        @else
                            <td></td>
                        @endif
                            <td>
                                {{ $attendance['check_out_latitude'] }}
                            </td>
                            <td>
                                {{ $attendance['check_out_longitude'] }}
                            </td>
                        <td  >
                            {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['worked_hour']) }}
                        </td>
                        <td  >
                            {{ ($multipleAttendance <= 1 && count($dayData['data']) <= 1) ? \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['overtime']) : '' }}
                        </td>
                        <td  >
                            {{($multipleAttendance <= 1 && count($dayData['data']) <= 1) ?  \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($attendance['undertime']) : '' }}
                        </td>
                            @php
                                $reason = (\App\Helpers\AttendanceHelper::getHolidayOrWeekendDetailForAttendance($dayData['attendance_date']));
                            @endphp
                            @if($reason)
                                <td >
                                    <span class="btn btn-outline-secondary btn-xs">
                                        {{ $reason }}
                                    </span>
                                </td>
                            @else
                                <td></td>
                            @endif

                        <td>{{ ucfirst($attendance['shift']) }}</td>
                    </tr>
                @endforeach

                @if($multipleAttendance > 1 && count($dayData['data']) > 1)
                    <tr class="bg-gray-100">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
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
                            $totalOverTime = $totalUnderTime = 0;
                            $deficiency =  (int)$totalMinutes - (int)$workingHours;

                            if($deficiency > 0){
                                $totalOverTime = $deficiency;
                                $netTotalOverTime += $deficiency;
                            }else{
                                $totalUnderTime = abs($deficiency);
                                $netTotalUnderTime += abs($deficiency);
                            }

                        @endphp
                        <th >{{ $worked_hours }}</th>
                        <th>{{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($totalOverTime) }} </th>
                        <th> {{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($totalUnderTime) }}</th>
                        <th></th>
                        <th></th>


                    </tr>
                @endif
            @else
                <tr>
                    <td>{{ \App\Helpers\AttendanceHelper::formattedAttendanceDate($isBsEnabled, $dayData['attendance_date']) }}</td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>
                    <td ><i class="link-icon" data-feather="x"></i></td>

                    @php
                        $reason = (\App\Helpers\AttendanceHelper::getHolidayOrLeaveDetail($dayData['attendance_date'], $employeeDetail->id));
                    @endphp
                    @if($reason)
                        @php
                            if($reason == 'Leave%'){
                                $netTotalLeave++;
                            }

                            if($reason == 'Absent'){
                                $netTotalAbsent++;
                            }
                        @endphp
                        <td >
                            <span class="btn btn-outline-secondary btn-xs">
                                {{ $reason }}
                            </span>
                        </td>
                    @endif
                </tr>
            @endif

        </tbody>

    @empty
        <tbody>
        <tr>
            <td colspan="100%">
                <p ><b>{{ __('index.no_records_found') }}</b></p>
            </td>
        </tr>
        <tbody>
    @endforelse
        <tfoot>
            <tr>
                <th><b>{{ __('index.total') }}</b></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th  style="text-align: center;"><b>{{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalMinutes) }}</b></th>
                <th style="text-align: center;"><b>{{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalOverTime) }}</b></th>
                <th style="text-align: center;"><b>{{ \App\Helpers\AttendanceHelper::getWorkedTimeInHourAndMinute($netTotalUnderTime) }}</b></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>

            </tr>

            <tr> </tr>
            <tr>
                <th> Remarks:</th>
            </tr>

            <tr>
                <th> Total Leave:</th>
                <td>{{ $netTotalLeave }}</td>
            </tr>
            <tr>
                <th> Total Absent:</th>
                <td>{{ $netTotalAbsent }}</td>
            </tr>

        </tfoot>
</table>
