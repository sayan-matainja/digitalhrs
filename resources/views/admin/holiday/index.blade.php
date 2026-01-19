@extends('layouts.master')

@section('title', __('index.holiday'))

@section('action', __('index.lists'))

@section('button')
    <div class="float-md-end">
        @can('create_holiday')
            <a href="{{ route('admin.holidays.create') }}">
                <button class="btn btn-primary">
                    <i class="link-icon" data-feather="plus"></i>@lang('index.add_holiday')
                </button>
            </a>
        @endcan

        @can('import_holiday')
            <a href="{{ route('admin.holidays.import-csv.show') }}">
                <button class="btn btn-success">
                    <i class="link-icon"></i>@lang('index.import_holiday_csv')
                </button>
            </a>
        @endcan
    </div>
@endsection

@section('main-content')
        <?php
        if(\App\Helpers\AppHelper::ifDateInBsEnabled()){
            $filterData['min_year'] = '2076';
            $filterData['max_year'] = '2089';
            $filterData['month'] = 'np';
        }else{
            $filterData['min_year'] = '2020';
            $filterData['max_year'] = '2033';
            $filterData['month'] = 'en';
        }
        ?>

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.holiday.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.holiday_filter')</h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.holidays.index') }}" method="get">
                    <div class="row align-items-center">
                        <div class="col-lg col-md-6 mb-4">
                            <input type="text" placeholder="@lang('index.event_name')" id="event" name="event" value="{{ $filterParameters['event'] }}" class="form-control">
                        </div>

                        <div class="col-lg col-md-6 mb-4">
                            <input type="number" min="{{ $filterData['min_year'] }}" max="{{ $filterData['max_year'] }}" step="1"
                                placeholder="@lang('index.leave_requested_year') e.g : {{ $filterData['min_year'] }}"
                                id="year" name="event_year" value="{{ $filterParameters['event_year'] }}" class="form-control">
                        </div>

                        <div class="col-lg col-md-6 mb-4">
                            <select class="form-select form-select-lg" name="month" id="month">
                                <option value="" {{ !isset($filterParameters['month']) ? 'selected' : '' }}>@lang('index.all_month')</option>
                                @foreach($months as $key => $value)
                                    <option value="{{ $key }}" {{ (isset($filterParameters['month']) && $key == $filterParameters['month'] ) ? 'selected' : '' }}>
                                        {{ $value[$filterData['month']] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6 mb-4">
                            <div class="d-flex float-md-end">
                                <button type="submit" class="btn btn-block btn-success me-2">@lang('index.filter')</button>
                                <a class="btn btn-block btn-primary" href="{{ route('admin.holidays.index') }}">@lang('index.reset')</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.holiday_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.event')</th>
                            <th>@lang('index.event_date')</th>
                            <th class="text-center">@lang('index.status')</th>
                            @canany(['show_holiday','edit_holiday','delete_holiday'])
                                <th class="text-center">@lang('index.action')</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($holidays as $key => $value)
                            <tr>
                                <td>{{ (($holidays->currentPage()- 1 ) * (\App\Models\Holiday::RECORDS_PER_PAGE) + (++$key)) }}</td>
                                <td>{{ ucfirst($value->event) }}</td>
                                <td>{{ \App\Helpers\AppHelper::formatDateForView($value->event_date) }}</td>

                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{ route('admin.holidays.toggle-status', $value->id) }}"
                                               type="checkbox" {{ ($value->is_active) == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['show_holiday','edit_holiday','delete_holiday'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('edit_holiday')
                                                <li class="me-2">
                                                    <a href="{{ route('admin.holidays.edit', $value->id) }}" title="@lang('index.edit')">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_holiday')
                                                <li class="me-2">
                                                    <a href=""
                                                       id="showHolidayDetail"
                                                       data-href="{{ route('admin.holidays.show', $value->id) }}"
                                                       data-id="{{ $value->id }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_holiday')
                                                <li>
                                                    <a class="deleteHoliday"
                                                       data-href="{{ route('admin.holidays.delete', $value->id) }}" title="@lang('index.delete')">
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
                                    <p class="text-center"><b>@lang('index.no_records_found')</b></p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            {{ $holidays->appends($_GET)->links() }}
        </div>
    </section>
        @include('admin.holiday.show')
@endsection

@section('scripts')

    @include('admin.holiday.common.scripts')

@endsection
