{{-- resources/views/admin/employees/export/user_list.blade.php --}}
<table>
    <thead>
        <tr>
            <th>EMPLOYEE ID</th>
            <th>SURNAME</th>
            <th>FIRST NAME</th>
            <th>MIDDLE NAME</th>
            <th>NIN</th>
            <th>BVN</th>
            <th>DATE OF BIRTH</th>
            <th>PHONE NO.</th>
            <th>EMAIL</th>
            <th>EMPLOYMENT DATE</th>
            <th>EMPLOYMENT TYPE</th>
            <th>WORKPLACE</th>
            <th>SUPERVISOR</th>
            <th>BRANCH/COMPANY</th>
            <th>DEPARTMENT</th>
            <th>DESIGNATION</th>
            <th>GRADE LEVEL</th>
            <th>TAX ID</th>
            <th>SBU CODE</th>
            <th>RSA NO</th>
            <th>HMO ID</th>
            <th>SHIFT</th>
            <th>BANK NAME</th>
            <th>ACCOUNT NUMBER</th>
            <th>ACCOUNT TYPE</th>
            <th>ACCOUNT HOLDER</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
            <tr>
                {{-- EMPLOYEE ID --}}
                <td>{{ $user->employee_code ?? 'N/A' }}</td>

                {{-- SURNAME --}}
                <td>{{ $user->surname ?? 'N/A' }}</td>

                {{-- FIRST NAME --}}
                <td>{{ $user->first_name ?? 'N/A' }}</td>

                {{-- MIDDLE NAME --}}
                <td>{{ $user->middle_name ?? 'N/A' }}</td>

                {{-- NIN --}}
                <td style="mso-number-format:'\@'">{{ $user->nin ?? 'N/A' }}</td>

                {{-- BVN (from accountDetail) --}}
                <td style="mso-number-format:'\@'">{{ $user->accountDetail->bvn ?? 'N/A' }}</td>

                {{-- DATE OF BIRTH --}}
                <td>{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('d/m/Y') : 'N/A' }}</td>

                {{-- PHONE NO. --}}
                <td style="mso-number-format:'\@'">{{ $user->phone ?? 'N/A' }}</td>

                {{-- EMAIL --}}
                <td>{{ $user->email ?? 'N/A' }}</td>

                {{-- EMPLOYMENT DATE --}}
                <td>{{ $user->joining_date ? \Carbon\Carbon::parse($user->joining_date)->format('d/m/Y') : 'N/A' }}</td>

                {{-- EMPLOYMENT TYPE --}}
                <td>{{ $user->employment_type ? ucfirst($user->employment_type) : 'N/A' }}</td>

                {{-- WORKPLACE --}}
                <td>{{ $user->workspace_type == \App\Models\User::FIELD ? 'Field' : 'Office' }}</td>

                {{-- SUPERVISOR --}}
                <td>{{ $user->supervisor ? ucfirst($user->supervisor->name) : 'N/A' }}</td>

                {{-- BRANCH/COMPANY --}}
                <td>{{ $user->branch ? ucfirst($user->branch->name) : ($user->company ? ucfirst($user->company->name) : 'N/A') }}</td>

                {{-- DEPARTMENT --}}
                <td>{{ $user->department ? ucfirst($user->department->dept_name) : 'N/A' }}</td>

                {{-- DESIGNATION --}}
                <td>{{ $user->post ? ucfirst($user->post->post_name) : 'N/A' }}</td>

                {{-- GRADE LEVEL --}}
                <td>{{ $user->grade_level ?? 'N/A' }}</td>

                {{-- TAX ID --}}
                <td style="mso-number-format:'\@'">{{ $user->tax_id ?? 'N/A' }}</td>

                {{-- SBU CODE --}}
                <td style="mso-number-format:'\@'">{{ $user->sbu_code ?? 'N/A' }}</td>

                {{-- RSA NO --}}
                <td style="mso-number-format:'\@'">{{ $user->rsa_no ?? 'N/A' }}</td>

                {{-- HMO ID --}}
                <td style="mso-number-format:'\@'">{{ $user->hmo_id ?? 'N/A' }}</td>

                {{-- SHIFT --}}
                <td>{{ $user->officeTime ? $user->officeTime->opening_time . ' - ' . $user->officeTime->closing_time : 'N/A' }}</td>

                {{-- BANK DETAILS --}}
                <td>{{ $user->accountDetail->bank_name ?? 'N/A' }}</td>
                <td style="mso-number-format:'\@'">{{ $user->accountDetail->bank_account_no ?? 'N/A' }}</td>
                <td>{{ $user->accountDetail->bank_account_type ? ucfirst($user->accountDetail->bank_account_type) : 'N/A' }}</td>
                <td>{{ $user->accountDetail->account_holder ?? 'N/A' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="26">No records found</td>
            </tr>
        @endforelse
    </tbody>
</table>
