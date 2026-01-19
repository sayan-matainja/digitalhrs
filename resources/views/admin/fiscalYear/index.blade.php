@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')
@section('title',__('index.fiscal_years'))
@section('action',__('index.lists'))
@section('button')
    @can('create_award_type')
        <a href="{{ route('admin.fiscal_year.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>@lang('index.add_fiscal_year')
            </button>
        </a>
    @endcan
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.fiscalYear.common.breadcrumb')
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.fiscal_year') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.title')</th>
                            <th>@lang('index.start_date')</th>
                            <th>@lang('index.end_date')</th>
{{--                            <th>@lang('index.is_current')</th>--}}
                            @can('fiscal_year')
                                <th class="text-center">@lang('index.action')</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                        @forelse($fiscalYears as $value)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ucfirst($value->year)}}</td>
                                <td>
                                    {{AppHelper::formatDateForView($value->start_date)}}
                                </td>
                                <td>
                                    {{AppHelper::formatDateForView($value->end_date)}}
                                </td>
{{--                                <td class="text-center">--}}
{{--                                    <label class="switch">--}}
{{--                                        <input class="toggleStatus" href="{{route('admin.fiscal_year.toggle-current-status',$value->id)}}"--}}
{{--                                               type="checkbox" {{($value->is_running) == 1 ?'checked':''}}>--}}
{{--                                        <span class="slider round"></span>--}}
{{--                                    </label>--}}
{{--                                </td>--}}

                            @can('fiscal_year')
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">

                                            <li class="me-2">
                                                <a href="{{route('admin.fiscal_year.edit',$value->id)}}"
                                                   title="@lang('index.edit')">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>

                                            <li>
                                                <a class="delete"
                                                   data-href="{{route('admin.fiscal_year.delete',$value->id)}}"
                                                   title="@lang('index.delete')">
                                                    <i class="link-icon" data-feather="delete"></i>
                                                </a>
                                            </li>

                                        </ul>
                                    </td>
                                @endcan

                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>@lang('index.no_records_found')</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.fiscalYear.common.scripts')
@endsection






