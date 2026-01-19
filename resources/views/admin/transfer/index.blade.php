@extends('layouts.master')

@section('title',__('index.transfer'))

@section('action',__('index.lists'))

@section('button')
    @can('create_transfer')
        <a href="{{ route('admin.transfer.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_transfer') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.transfer.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{  __('index.transfer_filter') }}</h6>
            </div>

            <form class="forms-sample card-body pb-0" action="{{route('admin.transfer.index')}}" method="get">

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
                                   name="transfer_date"
                                   value="{{ $filterParameters['transfer_date'] ?? '' }}"
                                   placeholder="mm/dd/yyyy"
                                   class="form-control nepaliDate"/>
                        </div>


                    @else
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date"  value="{{ $filterParameters['transfer_date'] ?? '' }}" name="transfer_date" class="form-control">
                        </div>

                    @endif


                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">{{  __('index.filter') }}</button>
                            <a href="{{route('admin.transfer.index')}}" class="btn btn-block btn-primary">{{  __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.transfer_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">{{ __('index.employee') }}</th>
                            <th class="text-center">{{ __('index.new_branch') }}</th>
                            <th class="text-center">{{ __('index.new_department') }}</th>
                            <th class="text-center">{{ __('index.transfer_date') }}</th>
                            @canany(['show_transfer','delete_transfer','update_transfer'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>

                            @forelse($transferLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td class="text-center">
                                        {{ $value?->employee?->name }}
                                    </td>
                                    <td class="text-center">
                                        {{ $value?->branch?->name }}
                                    </td>
                                    <td class="text-center">
                                        {{ $value?->department?->dept_name }}
                                    </td>
                                    <td class="text-center">
                                        {{ \App\Helpers\AppHelper::formatDateForView($value->transfer_date) }}
                                    </td>


                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_transfer')
                                                <li class="me-2">
                                                    <a href="{{route('admin.transfer.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_transfer')
                                                <li class="me-2">
                                                    <a href="{{route('admin.transfer.show',$value->id)}}" title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_transfer')
                                                <li>
                                                    <a class="deleteWarning"
                                                       data-title="{{$value->subject}} Detail"
                                                       data-href="{{route('admin.transfer.delete',$value->id)}}"
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
            {{ $transferLists->appends($_GET)->links() }}
        </div>
    </section>
{{--    @include('admin.transfer.common.status_update')--}}

@endsection

@section('scripts')
    @include('admin.transfer.common.scripts')
@endsection

