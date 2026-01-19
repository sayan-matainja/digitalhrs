@extends('layouts.master')

@section('title',__('index.leave_approval'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.leave-approval.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveApproval.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <table class="table table-striped table-responsive">
                        <tbody>

                        <tr>
                            <th class="w-30">{{ __('index.title') }}</th>
                            <td>{{ $leaveApprovalDetail->subject }}</td>
                        </tr>

                        <tr>
                            <th class="w-30">{{ __('index.leave_type') }}</th>
                            <td>{{ $leaveApprovalDetail?->leaveType?->name }}</td>
                        </tr>

                        <tr>
                            <th class="w-30">{{ __('index.departments') }}</th>
                            <td>
                                <ul class="mb-0 ps-0 list-unstyled">
                                    @forelse($leaveApprovalDetail->approvalDepartment as $detail)
                                        <li>{{ $detail?->department?->dept_name }}</li>
                                    @empty
                                    @endforelse
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" class="" >Process</th>
                        </tr>

                        @foreach($leaveApprovalDetail->approvalProcess as $process)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $process->approver)) }}</td>
                                <td>{{ $process->approver == \App\Enum\LeaveApproverEnum::specific_personnel->value ? ucfirst($process?->user?->role?->name)  : '' }}</td>
                                <td>{{ $process->approver == \App\Enum\LeaveApproverEnum::specific_personnel->value ? $process?->user?->name : '' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.leaveApproval.common.scripts')
@endsection

