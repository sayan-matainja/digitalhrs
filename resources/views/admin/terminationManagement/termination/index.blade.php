@extends('layouts.master')

@section('title',__('index.termination'))

@section('action',__('index.lists'))

@section('button')

    <div class="float-end">

        @can('create_termination')
            <a href="{{ route('admin.termination.create')}}">
                <button class="btn btn-primary">
                    <i class="link-icon" data-feather="plus"></i>{{ __('index.add_termination') }}
                </button>
            </a>
        @endcan
        @can('termination_type_list')
            <a href="{{ route('admin.termination-types.index')}}">
                <button class="btn btn-primary">
                    <i class="link-icon" data-feather="list"></i>{{ __('index.termination_types') }}
                </button>
            </a>
        @endcan
    </div>

@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.terminationManagement.termination.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{  __('index.termination_filter') }}</h6>
            </div>

            <form class="forms-sample card-body pb-0" action="{{route('admin.termination.index')}}" method="get">

                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  selected  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id']  == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif


                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option selected disabled> {{  __('index.select_department') }} </option>

                        </select>
                    </div>


                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option selected disabled >{{  __('index.select_employee') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="termination_type_id" id="termination_type_id">
                            <option selected disabled >{{  __('index.select_termination_type') }}</option>
                        </select>
                    </div>
                    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text"  id="nepali-datepicker-from"
                                   name="termination_date"
                                   value="{{ $filterParameters['termination_date'] ?? '' }}"
                                   placeholder="mm/dd/yyyy"
                                   class="form-control nepaliDate"/>
                        </div>


                    @else
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date"  value="{{ $filterParameters['termination_date'] ?? '' }}" name="termination_date" class="form-control">
                        </div>

                    @endif


                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">{{  __('index.filter') }}</button>
                            <a href="{{route('admin.termination.index')}}" class="btn btn-block btn-primary">{{  __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.termination_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee') }}</th>
                            <th>{{ __('index.termination_type') }}</th>
                            <th class="text-center">{{ __('index.notice_date') }}</th>
                            <th class="text-center">{{ __('index.termination_date') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['show_termination','delete_termination','update_termination'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            $color = [
                                \App\Enum\TerminationStatusEnum::approved->value => 'success',
                                \App\Enum\TerminationStatusEnum::onReview->value => 'info',
                                \App\Enum\TerminationStatusEnum::pending->value => 'secondary',
                                \App\Enum\TerminationStatusEnum::cancelled->value => 'warning',
                            ];


                            ?>
                            @forelse($terminationLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{ $value->employee?->name }}</td>
                                    <td>{{ $value->terminationType?->title }}</td>
                                    <td class="text-center">{{ \App\Helpers\AppHelper::formatDateForView($value->notice_date) }}</td>
                                    <td class="text-center">{{ \App\Helpers\AppHelper::formatDateForView($value->termination_date) }}</td>
                                    <td class="text-center">
                                        <a href=""
                                           class="terminationStatusUpdate"
                                           data-href="{{route('admin.termination.update-status',$value->id)}}"
                                           data-status="{{$value->status}}"
                                           data-reason="{{$value->admin_remark}}"
                                           data-id="{{$value->id}}"
                                        >
                                            <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                                {{ ucfirst($value->status) }}
                                            </button>
                                        </a>

                                    </td>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_termination')
                                                <li class="me-2">
                                                    <a href="{{route('admin.termination.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_termination')
                                                <li class="me-2">
                                                    <a href="{{route('admin.termination.show',$value->id)}}" title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_termination')
                                                <li>
                                                    <a class="delete"
                                                       data-title="{{$value->name}} Detail"
                                                       data-href="{{route('admin.termination.delete',$value->id)}}"
                                                       title="{{ __('index.delete') }}">
                                                        <i class="link-icon"  data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            @endcan
                                          </ul>
                                    </td>
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
            {{ $terminationLists->appends($_GET)->links() }}
        </div>
    </section>
    @include('admin.terminationManagement.termination.common.status_update')

@endsection

@section('scripts')
    @include('admin.terminationManagement.termination.common.scripts')
@endsection

