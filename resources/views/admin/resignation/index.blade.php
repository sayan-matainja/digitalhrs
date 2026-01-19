@php use App\Enum\ResignationStatusEnum; @endphp
@extends('layouts.master')

@section('title',__('index.resignation'))

@section('action',__('index.lists'))

@section('button')
    @can('create_resignation')
        <a href="{{ route('admin.resignation.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_resignation') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.resignation.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{  __('index.resignation_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{route('admin.resignation.index')}}" method="get">

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

                    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text"  id="nepali-datepicker-from"
                                   name="resignation_date"
                                   value="{{ $filterParameters['resignation_date'] ?? '' }}"
                                   placeholder="mm/dd/yyyy"
                                   class="form-control nepaliDate"/>
                        </div>


                    @else
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date"  value="{{ $filterParameters['resignation_date'] ?? '' }}" name="resignation_date" class="form-control">
                        </div>

                    @endif


                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">{{  __('index.filter') }}</button>
                            <a href="{{route('admin.resignation.index')}}" class="btn btn-block btn-primary">{{  __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.resignation_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee') }}</th>
                            <th>{{ __('index.resignation_date') }}</th>
                            <th>{{ __('index.last_date') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['show_resignation','delete_resignation','update_resignation'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            $color = [
                                ResignationStatusEnum::approved->value => 'success',
                                ResignationStatusEnum::onReview->value => 'info',
                                ResignationStatusEnum::pending->value => 'secondary',
                                ResignationStatusEnum::cancelled->value => 'danger',
                            ];


                            ?>
                        @forelse($resignationLists as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{ $value?->employee?->name }}</td>
                                <td>{{ \App\Helpers\AppHelper::formatDateForView($value->resignation_date) }}</td>
                                <td>{{ \App\Helpers\AppHelper::formatDateForView($value->last_working_day) }}</td>
                                <td class="text-center">
                                    @if(($value->status ==  ResignationStatusEnum::approved->value)  && strtotime(date('Y-m-d')) > strtotime($value->last_working_day))

                                        <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                            {{ ucfirst($value->status) }}
                                        </button>

                                    @else
                                        <a href=""
                                           class="resignationStatusUpdate"
                                           data-href="{{ route('admin.resignation.update-status',$value->id) }}"
                                           data-status="{{$value->status}}"
                                           data-reason="{{$value->admin_remark}}"
                                           data-id="{{$value->id}}"
                                        >
                                            <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                                {{ ucfirst($value->status) }}
                                            </button>
                                        </a>
                                    @endif


                                </td>
                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('show_resignation')
                                            <li class="me-2">
                                                <a href="{{route('admin.resignation.show',$value->id)}}"
                                                   title="{{ __('index.show_detail') }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @if(strtotime(date('Y-m-d')) <= strtotime($value->last_working_day))
                                            @can('update_resignation')
                                                <li class="me-2">
                                                    <a href="{{route('admin.resignation.edit',$value->id)}}"
                                                       title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan


                                            @can('delete_resignation')
                                                <li>
                                                    <a class="delete"
                                                       data-title="{{$value->name}} Detail"
                                                       data-href="{{route('admin.resignation.delete',$value->id)}}"
                                                       title="{{ __('index.delete') }}">
                                                        <i class="link-icon" data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            @endcan
                                        @endif

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
            {{ $resignationLists->appends($_GET)->links() }}
        </div>
    </section>
    @include('admin.resignation.common.status_update')

@endsection

@section('scripts')
    @include('admin.resignation.common.scripts')
@endsection

