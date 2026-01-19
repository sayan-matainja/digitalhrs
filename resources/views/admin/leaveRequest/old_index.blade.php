
@extends('layouts.master')

@section('title',__('index.leave_requests'))

@section('action',__('index.lists'))

@section('button')
    @canany(['create_leave_request','access_admin_leave'])
        <a href="{{ route('admin.leave-request.add')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.create_leave_request') }}
            </button>
        </a>
    @endcanany
@endsection

@section('main-content')
        <?php
        if (\App\Helpers\AppHelper::ifDateInBsEnabled()) {
            $filterData['min_year'] = '2076';
            $filterData['max_year'] = '2089';
            $filterData['month'] = 'np';
        } else {
            $filterData['min_year'] = '2020';
            $filterData['max_year'] = '2033';
            $filterData['month'] = 'en';
        }
        ?>

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveRequest.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.leave_request_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{route('admin.leave-request.index')}}" method="get">

                <div class="row align-items-center">

                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-xxl col-xl-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option selected disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option {{ $filterParameters['branch_id'] == $branch->id ? 'selected' : '' }} value="{{$branch->id}}">{{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <!-- Departments Field -->
                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option selected disabled>{{ __('index.select_department') }}</option>

                        </select>
                    </div>
                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select" id="requestedBy" name="requested_by" required>
                            <option selected disabled>{{ __('index.select_employee') }}</option>

                        </select>

                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="leave_type" id="leaveType">
                            <option value="" {{!isset($filterParameters['leave_type']) ? 'selected': ''}} >{{ __('index.all_leave_type') }}</option>

                        </select>
                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <input type="number" min="{{ $filterData['min_year']}}"
                               max="{{ $filterData['max_year']}}" step="1"
                               placeholder="{{ __('index.leave_requested_year') }} : {{$filterData['min_year']}}"
                               id="year"
                               name="year" value="{{$filterParameters['year']}}"
                               class="form-control">
                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="month" id="month">
                            <option
                                value="" {{!isset($filterParameters['month']) ? 'selected': ''}} >{{ __('index.all_month') }}</option>
                            @foreach($months as $key => $value)
                                <option
                                    value="{{$key}}" {{ (isset($filterParameters['month']) && $key == $filterParameters['month'] ) ?'selected':'' }} >
                                    {{$value[$filterData['month']]}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="status" id="status">
                            <option
                                value="" {{!isset($filterParameters['status']) ? 'selected': ''}} >{{ __('index.all_status') }}</option>
                            @foreach(\App\Models\LeaveRequestMaster::STATUS as  $value)
                                <option
                                    value="{{$value}}" {{ (isset($filterParameters['status']) && $value == $filterParameters['status'] ) ?'selected':'' }} > {{ucfirst($value)}} </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xxl col-xl-3 mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-secondary me-2">{{ __('index.filter') }}</button>
                            <a class="btn btn-block btn-primary"
                               href="{{route('admin.leave-request.index')}}">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ __('index.type') }}</th>
                            <th>{{ __('index.leave_for') }}</th>
                            <th>{{ __('index.leave_date') }}</th>
                            <th>{{ __('index.requested_date') }}</th>
                            <th>{{ __('index.requested_by') }}</th>
                            <th class="text-center">{{ __('index.requested_days') }}</th>
                            @canany(['show_leave_request_detail','access_admin_leave'])
                                <th class="text-center">{{ __('index.reason') }}</th>
                            @endcanany
                            @canany(['update_leave_request','access_admin_leave'])
                                <th class="text-center">{{ __('index.status') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>

                            <?php
                            $color = [
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'secondary',
                                'cancelled' => 'danger'
                            ];

                            ?>
                        @forelse($leaveDetails as $key => $value)
                            @php
                                if(is_null($value->leave_to) || (strtotime($value->leave_from) == strtotime($value->leave_to))){
                                    $leaveDate = \App\Helpers\AppHelper::formatDateForView($value->leave_from);
                                }else{
                                     $leaveDate = \App\Helpers\AppHelper::formatDateForView($value->leave_from) .' to '. \App\Helpers\AppHelper::formatDateForView($value->leave_to);
                                }
                            @endphp
                            @if(auth('admin')->user())
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $value->leaveType ? ucfirst($value->leaveType->name) : ''}}</td>
                                    <td> {{ ucfirst(str_replace('_',' ',$value->leave_for)) }}</td>

                                    <td>
                                        {{ $leaveDate }}
                                        @if(isset($value->leave_in))
                                            <br/>
                                            ({{ ucfirst(str_replace('_',' ',$value->leave_in)) }})
                                        @endif
                                    </td>
                                    <td>{{\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)}}</td>
                                    <td>{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'}} </td>
                                    <td class="text-center">{{($value->no_of_days )}}</td>

                                    <td class="text-center">
                                        <a href="#" class="showLeaveReason"
                                           data-href="{{ route('admin.leave-request.show', $value->id) }}"
                                           title="{{ __('index.show_leave_reason') }}">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>

                                    </td>
                                    <td class="text-center">
                                        <a href=""
                                           class="leaveRequestUpdate"
                                           data-href="{{route('admin.leave-request.update-status',$value->id)}}"
                                           data-status="{{$value->status}}"
                                           data-remark="{{$value->admin_remark}}"
                                           data-id="{{$value->id}}"
                                        >
                                            <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                                {{ucfirst($value->status)}}
                                            </button>
                                        </a>
                                    </td>

                                </tr>
                            @else
                                @php
                                    $inRole = false;
                                    $approver = null;
                                    // Get the next approver for pending leaves
                                    $approver = \App\Helpers\AppHelper::getNextApprover($value->id, $value->leave_type_id, $value->requested_by);
                                    $permissionKey = 'access_admin_leave';

                                    $roleArray = \App\Helpers\AppHelper::getRoleByPermission($permissionKey);

                                    if(auth()->user()){
                                        $inRole = in_array(auth()->user()->role_id, $roleArray);
                                    }

                                @endphp
                                @if(($approver == auth()->user()->id && $value->status =='pending')  || ($inRole && $value->status =='pending'))
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $value->leaveType ? ucfirst($value->leaveType->name) : ''}}</td>
                                        <td> {{ ucfirst(str_replace('_',' ',$value->leave_for)) }}</td>
                                        <td>
                                            {{ $leaveDate }}
                                            @if(isset($value->leave_in))
                                                <br/>
                                                ({{ ucfirst(str_replace('_',' ',$value->leave_in)) }})
                                            @endif
                                        </td>
                                        <td>{{\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)}}</td>
                                        <td>{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'}} </td>
                                        <td class="text-center">{{($value->no_of_days )}}</td>

                                        @canany(['show_leave_request_detail','access_admin_leave'])
                                            <td class="text-center">
                                                <a href="#" class="showLeaveReason"
                                                   data-href="{{ route('admin.leave-request.show', $value->id) }}"
                                                   title="{{ __('index.show_leave_reason') }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>

                                            </td>
                                        @endcanany

                                        @canany(['update_leave_request','access_admin_leave'])

                                            <td class="text-center">
                                                <a href=""
                                                   class="leaveRequestUpdate"
                                                   data-href="{{route('admin.leave-request.update-status',$value->id)}}"
                                                   data-status="{{$value->status}}"
                                                   data-remark="{{$value->admin_remark}}"
                                                   data-id="{{$value->id}}"
                                                >
                                                    <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                                        {{ucfirst($value->status)}}
                                                    </button>
                                                </a>
                                            </td>
                                        @endcanany
                                    </tr>
                                @elseif( ($value->requestApproval->where('leave_request_id', $value->id)->contains('approved_by', auth()->user()->id) || ($approver == auth()->user()->id && $value->status != 'pending')) || ($inRole && $value->status !='pending'))
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $value->leaveType ? ucfirst($value->leaveType->name) : ''}}</td>
                                        <td> {{ ucfirst(str_replace('_',' ',$value->leave_for)) }}</td>
                                        <td>
                                            {{ $leaveDate }}
                                            @if(isset($value->leave_in))
                                                <br/>
                                                ({{ ucfirst(str_replace('_',' ',$value->leave_in)) }})
                                            @endif
                                        </td>
                                        <td>{{\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)}}</td>
                                        <td>{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'}} </td>
                                        <td class="text-center">{{($value->no_of_days )}}</td>

                                        @canany(['show_leave_request_detail','access_admin_leave'])
                                            <td class="text-center">
                                                <a href="#" class="showLeaveReason"
                                                   data-href="{{ route('admin.leave-request.show', $value->id) }}"
                                                   title="{{ __('index.show_leave_reason') }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>

                                            </td>
                                        @endcanany

                                        @canany(['show_leave_request_detail','access_admin_leave'])
                                            <td class="text-center">

                                                @php
                                                    $approval = $value->requestApproval
                                                               ->where('leave_request_id', $value->id)
                                                               ->where('approved_by', auth()->user()->id)
                                                               ->first();

                                                @endphp
                                                @if(isset($approval))
                                                    <a href="javascript:void(0)" class="show-approval-info"
                                                       data-id="{{$value->id}}">
                                                        <button
                                                            class="btn btn-{{ $value->status == 'rejected' ? 'danger' : ($approval->status == 1 ? 'success' : 'danger') }} btn-xs">
                                                            {{  $value->status == 'rejected' ? 'Rejected' : ($approval->status == 1 ? 'Approved' : 'Rejected') }}
                                                        </button>
                                                    </a>
                                                @else
                                                    <a href="javascript:void(0)" class="show-approval-info"
                                                       data-id="{{$value->id}}">
                                                        <button
                                                            class="btn btn-{{ $value->status == 'rejected' ? 'danger' : 'success' }} btn-xs">
                                                            {{  ucfirst($value->status) }}
                                                        </button>
                                                    </a>

                                                @endif
                                            </td>
                                        @endcanany
                                    </tr>
                                @else

                                @endif
                            @endif


                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </section>
    <div class="dataTables_paginate mt-3">
        {{$leaveDetails->appends($_GET)->links()}}
    </div>

    @include('admin.leaveRequest.show')
    @include('admin.leaveRequest.common.form-model')
    @include('admin.leaveRequest.common.approval-info-model')
@endsection

@section('scripts')
    @include('admin.leaveRequest.common.scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.showLeaveReason').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();
                    const url = this.getAttribute('data-href');

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {

                            if (data && data.data) {
                                const leaveRequest = data.data;
                                document.getElementById('referredBy').innerText = leaveRequest.name || 'Admin';
                                document.getElementById('description').innerText = leaveRequest.reasons || 'N/A';
                                document.getElementById('adminRemark').innerText = leaveRequest.admin_remark || 'N/A';

                                const modalElement = document.getElementById('addslider');

                                if (modalElement) {
                                    const modal = new bootstrap.Modal(modalElement);
                                    modal.show();
                                } else {
                                    console.error('Modal element not found');
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });


    </script>
@endsection






