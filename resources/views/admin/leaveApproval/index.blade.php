@extends('layouts.master')

@section('title',__('index.leave_approval'))

@section('action',__('index.lists'))

@section('button')
    @can('create_leave_approval')
        <a href="{{ route('admin.leave-approval.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_leave_approval') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.leaveApproval.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.leave_approval_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{route('admin.leave-approval.index')}}" method="get">

                <div class="row align-items-center">

                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-xxl col-xl-4 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option selected disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option
                                            {{ isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id ? 'selected' : '' }} value="{{$branch->id}}">{{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <!-- Departments Field -->
                    <div class="col-xxl col-xl-4 col-md-6 mb-4">
                        <select class="form-select" id="departments" multiple name="department_id[]">
                            <option disabled>{{ __('index.select_department') }}</option>

                        </select>
                    </div>

                    <div class="col-xxl col-xl-4 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="leave_type_id" id="related">
                            <option selected disabled >{{ __('index.select_leave_type') }}</option>

                        </select>
                    </div>


                    <div class="col-xxl col-xl-4 mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-success me-2">{{ __('index.filter') }}</button>
                            <a class="btn btn-block btn-primary"
                               href="{{route('admin.leave-approval.index')}}">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.leave_approval_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.name') }}</th>
                            <th>{{ __('index.related') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['update_leave_approval','delete_leave_approval','show_leave_approval'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($leaveApprovals as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{ $value->subject }}</td>
                                <td>{{ $value->leaveType?->name }}</td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="{{route('admin.leave-approval.toggle-status',$value->id)}}"
                                               type="checkbox"{{($value->status) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                @canany(['update_leave_approval','delete_leave_approval','show_leave_approval'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_leave_approval')
                                                <li class="me-2">
                                                    <a href="{{ route('admin.leave-approval.edit',$value->id) }}"
                                                       title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('show_leave_approval')
                                                <li class="me-2">
                                                    <a href="{{ route('admin.leave-approval.show',$value->id) }}"
                                                       title="{{ __('index.show') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_leave_approval')
                                                <li>
                                                    <a class="delete"
                                                       data-title="{{$value->name}} Award Detail"
                                                       data-href="{{route('admin.leave-approval.delete',$value->id)}}"
                                                       title="{{ __('index.delete') }}">
                                                        <i class="link-icon" data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </td>
                                @endcanany
                            </tr>
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

        <div class="dataTables_paginate mt-3">
            {{ $leaveApprovals->appends($_GET)->links() }}
        </div>

    </section>

@endsection

@section('scripts')
    @include('admin.leaveApproval.common.scripts')
@endsection

