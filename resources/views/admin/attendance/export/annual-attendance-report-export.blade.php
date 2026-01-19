<table border="1" style="border-collapse: collapse; width: 100%;">
    <thead>
    <tr>
        <th colspan="8" style="text-align: center; font-size: 16px; padding: 10px; background-color: #f0f0f0;">
            <strong>{{ __('index.attendance_report') }} ({{ $year }}): {{ $userName }}</strong>
        </th>
    </tr>
    <tr style="background-color: #e9ecef;">
        <th style="text-align: center; padding: 8px;"><b>{{ __('index.month') }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ __('index.total_days') }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ __('index.working_days') }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ __('index.present') }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ __('index.holiday') }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ __('index.weekend') }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ __('index.leave') }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ __('index.absent') }}</b></th>
    </tr>
    </thead>
    <tbody>
    @forelse($attendanceData as $month)
        <tr>
            <td style="text-align: center; padding: 6px;">{{ $month['month_name'] }}</td>
            <td style="text-align: center; padding: 6px;">{{ $month['total_days'] }}</td>
            <td style="text-align: center; padding: 6px;">{{ $month['working_days'] }}</td>
            <td style="text-align: center; padding: 6px; background-color: #d4edda;">{{ $month['present'] }}</td>
            <td style="text-align: center; padding: 6px;">{{ $month['holidays'] }}</td>
            <td style="text-align: center; padding: 6px;">{{ $month['weekends'] }}</td>
            <td style="text-align: center; padding: 6px; background-color: #fff3cd;">{{ $month['leave'] }}</td>
            <td style="text-align: center; padding: 6px; {{ $month['absent'] > 0 ? 'background-color: #f8d7da; color: #721c24;' : '' }}">
                {{ $month['absent'] }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" style="text-align: center; padding: 10px;">
                <b>{{ __('index.no_records_found') }}</b>
            </td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr style="background-color: #343a40; color: white; font-weight: bold;">
        <th style="text-align: center; padding: 8px;"><b>{{ __('index.total') }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ $totals['total_days'] }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ $totals['working_days'] }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ $totals['present'] }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ $totals['holidays'] }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ $totals['weekends'] }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ $totals['leave'] }}</b></th>
        <th style="text-align: center; padding: 8px;"><b>{{ $totals['absent'] }}</b></th>
    </tr>
    </tfoot>
</table>
