@extends('layouts.master')

@section('title',__('index.office_time'))

@section('action',__('index.lists'))

@section('button')
    @can('create_office_time')
        <a href="{{ route('admin.office-times.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_office_time') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')

    <section class="content">


        @include('admin.section.flash_message')

        @include('admin.officeTime.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.office_time_filter')</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.office-times.index') }}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  {{ !isset($filterParameters['branch_id']) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" id="shift_type" name="shift_type">
                            <option selected disabled>{{ __('index.select_shift') }}</option>
                            @foreach($shifts as $type)
                                <option
                                    value="{{ $type->value }}" {{ (isset($filterParameters['shift_type']) && $filterParameters['shift_type'] == $type->value) ? 'selected':old('shift_type') }} >{{ ucfirst($type->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" id="category" name="category">
                            <option selected disabled>{{ __('index.select_category') }}</option>
                            @foreach($category as $value)
                                <option
                                    value="{{ $value }}" {{ (isset($filterParameters['category']) && $filterParameters['category'] == $value) ? 'selected':old('category') }} >{{ removeSpecialChars($value) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">@lang('index.filter')</button>
                            <a class="btn btn-block btn-primary" href="{{ route('admin.office-times.index') }}">@lang('index.reset')</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Office Time Lists</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">{{ __('index.opening_time') }}</th>
                            <th class="text-center">{{ __('index.closing_time') }} </th>
                            <th class="text-center">{{ __('index.shift') }}</th>
                            <th class="text-center">{{ __('index.branch') }}</th>
                            <th class="text-center">{{ __('index.category') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['show_office_time','edit_office_time','delete_office_time'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        @forelse($officeTimes as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td class="text-center">{{\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceView($value->opening_time)}}</td>
                                <td class="text-center">{{\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceView($value->closing_time)}}</td>
                                <td class="text-center">{{ucfirst($value->shift)}}</td>
                                <td class="text-center">{{ucfirst($value->branch?->name)}}</td>
                                <td class="text-center">{{removeSpecialChars($value->category)}}</td>

                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{route('admin.office-times.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['show_office_time','edit_office_time','delete_office_time'])
                                    <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('edit_office_time')
                                            <li class="me-2">
                                                <a href="{{route('admin.office-times.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('show_office_time')
                                            <li class="me-2">
                                                <a href=""
                                                   id="showOfficeTimeDetail"
                                                   title="{{ __('index.show_detail') }}"
                                                   data-href="{{route('admin.office-times.show',$value->id)}}"
                                                   data-id="{{ $value->id }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('delete_office_time')
                                            <li>
                                                <a class="deleteOfficeTime"
                                                   data-href="{{route('admin.office-times.delete',$value->id)}}" title="{{ __('index.delete') }}">
                                                    <i class="link-icon"  data-feather="delete"></i>
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


    </section>
    @include('admin.officeTime.show')
@endsection

@section('scripts')

    @include('admin.officeTime.common.scripts')
@endsection

