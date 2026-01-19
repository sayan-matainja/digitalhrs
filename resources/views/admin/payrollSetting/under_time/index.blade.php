@extends('layouts.master')
@section('title','UnderTime')
@section('sub_page','Lists')
@section('page')
    <a href="{{ route('admin.under-time.index')}}">
        UnderTime
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.payrollSetting.common.breadcrumb')
        <div class="row">
            <div class="col-lg-2">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <div class="justify-content-end">
                            <a class="btn btn-success"
                               href="{{ route('admin.under-time.create')}}">
                                <i class="link-icon" data-feather="plus"></i> {{ __('index.add_undertime') }}
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.title') }}</th>
                                    <th>{{ __('index.threshold_time') }}</th>
                                    <th>{{ __('index.penalty_rate') }}</th>
                                    <th>{{ __('index.is_active') }}</th>
                                    <th>{{ __('index.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    @forelse($underTimeData as $ut)
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $ut->title ?? $ut->id }}<br>
{{--                                            <small>Employee Count : {{ ($ut->ut_employees_count) }}</small>--}}
                                        </td>
                                        <th> {{ $ut->applied_after_minutes }} Minutes</th>
                                        <th>{{ $currency . $ut->ut_penalty_rate }}</th>
                                        <td>
                                            <label class="switch">
                                                <input class="toggleStatus"
                                                       href="{{ route('admin.under-time.toggle-status',$ut->id)}}"
                                                       type="checkbox"{{($ut->is_active) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                <li class="me-2">
                                                    <a href="{{ route('admin.under-time.edit',$ut->id)}}"
                                                       title="Edit Detail">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="delete" href="#"
                                                       data-href="{{route('admin.under-time.delete',$ut->id)}}"
                                                       title="Delete">
                                                        <i class="link-icon" data-feather="delete"></i>
                                                    </a>
                                                </li>
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
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.payrollSetting.under_time.common.scripts')
@endsection






