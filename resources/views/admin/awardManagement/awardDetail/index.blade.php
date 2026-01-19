@extends('layouts.master')

@section('title',__('index.award'))

@section('action',__('index.lists'))

@section('button')

    <div class="float-end">

        @can('create_award')
            <a href="{{ route('admin.awards.create')}}">
                <button class="btn btn-primary">
                    <i class="link-icon" data-feather="plus"></i>{{ __('index.add_award') }}
                </button>
            </a>
        @endcan
        @can('award_type_list')
            <a href="{{ route('admin.award-types.index')}}">
                <button class="btn btn-primary">
                    <i class="link-icon" data-feather="list"></i>{{ __('index.award_types') }}
                </button>
            </a>
        @endcan
    </div>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.awardManagement.awardDetail.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{  __('index.award_filter') }}</h6>
            </div>

            <form class="forms-sample card-body pb-0" action="{{route('admin.awards.index')}}" method="get">

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
                        <select class="form-select" name="award_type_id" id="award_type_id">
                            <option selected disabled >{{  __('index.select_award') }}</option>
                        </select>
                    </div>
                        @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                            <div class="col-lg-3 col-md-6 mb-4">
                                <input type="text"  id="nepali-datepicker-from"
                                       name="awarded_date"
                                       value="{{ $filterParameters['awarded_date'] ?? '' }}"
                                       placeholder="mm/dd/yyyy"
                                       class="form-control awarded_date"/>
                            </div>


                        @else
                            <div class="col-lg-3 col-md-6 mb-4">
                                <input type="date"  value="{{ $filterParameters['awarded_date'] ?? '' }}" name="awarded_date" class="form-control">
                            </div>

                        @endif


                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">{{  __('index.filter') }}</button>
                            <a href="{{route('admin.awards.index')}}" class="btn btn-block btn-primary">{{  __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.award_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee') }}</th>
                            <th>{{ __('index.award') }}</th>
                            <th>{{ __('index.gift_item') }}</th>
                            <th>{{ __('index.awarded_date') }}</th>
                            @canany(['show_award','delete_award','update_award'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($awardLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{ $value->employee?->name }}</td>
                                    <td>{{ $value->type?->title }}</td>
                                    <td>{{ $value->gift_item }}</td>
                                    <td>
{{--                                        @if($value->award_base == \App\Enum\AwardBaseEnum::yearly->value)--}}
{{--                                            {{ date('Y', strtotime($value->awarded_date)) }}--}}
{{--                                        @elseif($value->award_base == \App\Enum\AwardBaseEnum::monthly->value)--}}
{{--                                            {{ date('F Y', strtotime($value->awarded_date)) }}--}}
{{--                                        @else--}}
                                            {{ \App\Helpers\AppHelper::formatDateForView($value->awarded_date) }}
{{--                                        @endif--}}
                                    </td>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_award')
                                                <li class="me-2">
                                                    <a href="{{route('admin.awards.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_award')
                                                <li class="me-2">
                                                    <a href="{{route('admin.awards.show',$value->id)}}" title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_award')
                                                <li>
                                                    <a class="delete"
                                                       data-title="{{$value->name}} Award Detail"
                                                       data-href="{{route('admin.awards.delete',$value->id)}}"
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
            {{$awardLists->appends($_GET)->links()}}
        </div>
    </section>

@endsection

@section('scripts')
    @include('admin.awardManagement.awardDetail.common.scripts')
@endsection

