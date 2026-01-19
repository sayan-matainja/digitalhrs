<table class="table table-bordered">
    <thead>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Address</th>
        <th>Date of Birth</th>
        <th>Gender</th>
        <th>Marital Status</th>
        <th>Phone</th>
        <th>Status</th>
        <th>Employment Type</th>
        <th>User Type</th>
        <th>Joining Date</th>
        <th>Workspace Type</th>
        <th>Company</th>
        <th>Branch</th>
        <th>Department</th>
        <th>Post</th>
        <th>Shift</th>
        <th>Supervisor</th>
        <th>Employee Code</th>
    </tr>
    </thead>
    <tbody>

    @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->address }}</td>
            <td>{{ $user->dob }}</td>
            <td>{{ $user->gender }}</td>
            <td>{{ $user->marital_status }}</td>
            <td>{{ $user->phone }}</td>
            <td>{{ $user->status }}</td>
            <td>{{ $user->employment_type }}</td>
            <td>{{ $user->user_type }}</td>
            <td>{{ $user->joining_date }}</td>
            <td>{{ $user->workspace_type == \App\Models\User::FIELD ? 'Field' : 'Office' }}</td>
            <td>{{ $user?->company?->name ?? 'N/A' }}</td>
            <td>{{ $user?->branch?->name ?? 'N/A' }}</td>
            <td>{{ $user?->department?->dept_name ?? 'N/A' }}</td>
            <td>{{ $user?->post?->post_name ?? 'N/A' }}</td>
            <td>{{ $user?->officeTime?->shift .' ('.$user?->officeTime?->opening_time .'-'.$user?->officeTime?->closing_time.')' ?? 'N/A' }}</td>
            <td>{{ $user?->supervisor?->name ?? 'N/A' }}</td>
            <td>{{ $user->employee_code }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
