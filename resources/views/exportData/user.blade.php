<table>
    <thead>
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Email</th>
        <th>Username</th>
        <th>Address</th>
        <th>DOB</th>
        <th>Gender</th>
        <th>Phone</th>
        <th>Status</th>
        <th>Role</th>
        <th>Employment Type</th>
        <th>User Type</th>
        <th>Joining Date</th>
        <th>Bank Name</th>
        <th>Bank Account No</th>
        <th>Bank Account Type</th>
        <th>Branch</th>
        <th>Department</th>
        <th>Post</th>
    </tr>
    </thead>
    <tbody>
    @foreach($employeeDetail as $key => $datum)
        <tr>
            <td>{{ $datum->id }}</td>
            <td>{{ $datum->name }}</td>
            <td>{{ $datum->email }}</td>
            <td>{{ $datum->username }}</td>
            <td>{{ $datum->address }}</td>
            <td>{{ $datum->dob }}</td>
            <td>{{ $datum->gender }}</td>
            <td>{{ $datum->phone }}</td>
            <td>{{ $datum->status }}</td>
            <td>{{ $datum->role->name }}</td>
            <td>{{ $datum->employment_type }}</td>
            <td>{{ $datum->role->name }}</td>
            <td>{{ $datum->joining_date }}</td>
            <td>{{ $datum->accountDetail->bank_name ?? '' }}</td>
            <td>{{ $datum->accountDetail->bank_account_no ?? '' }}</td>
            <td>{{ $datum->accountDetail->bank_account_type ?? '' }}</td>
            <td>{{ $datum->branch->name }}</td>
            <td>{{ $datum->department->dept_name }}</td>
            <td>{{ $datum->post->post_name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
