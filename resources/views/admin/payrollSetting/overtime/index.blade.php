@extends('layouts.master')
@section('title',__('index.overtime'))
@section('sub_page',__('index.lists'))
@section('page')
    <a href="{{ route('admin.overtime.index')}}">
        {{ __('index.overtime') }}
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
                            @can('overtime_setting')
                                <a class="btn btn-success"
                                   href="{{ route('admin.overtime.create')}}">
                                    <i class="link-icon"
                                       data-feather="plus"></i> {{ __('index.add') }} {{ __('index.overtime') }}
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.payroll_type') }}</th>
                                    <th>{{ __('index.title') }}</th>
                                    <th class="text-center">{{ __('index.max_daily_ot') }}</th>
                                    <th class="text-center">{{ __('index.pay_percent') }}</th>
                                    <th class="text-center">{{ __('index.is_active') }}</th>
                                    @can('overtime_setting')
                                        <th class="text-center">{{ __('index.action') }}</th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>

                                @forelse($overTimeData as $ot)

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ ucfirst($ot->payroll_type) }}
                                        </td>
                                        <td>
                                            {{ $ot->title ?? $ot->id }}<br>
                                            <small>{{ __('index.employee_count') }}
                                                : {{ ($ot->ot_employees_count) }}</small>
                                        </td>
                                        <td class="text-center"> {{ $ot->max_daily_ot_hours }} {{ __('index.hour') }}</td>
                                        <td class="text-center">{{ $ot->overtime_pay_rate ? $currency . $ot->overtime_pay_rate : $ot->pay_percent. '%' }}</td>
                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleStatus"
                                                       href="{{route('admin.overtime.toggle-status',$ot->id)}}"
                                                       type="checkbox"{{($ot->is_active) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        @can('overtime_setting')
                                            <td class="text-center">
                                                <ul class="d-flex list-unstyled mb-0 justify-content-center">

                                                    <li class="me-2">
                                                        <a href="{{route('admin.overtime.edit',$ot->id)}}"
                                                           title="Edit Detail">
                                                            <i class="link-icon" data-feather="edit"></i>
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="delete" href="#"
                                                           data-href="{{route('admin.overtime.delete',$ot->id)}}"
                                                           title="Delete">
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
    @include('admin.payrollSetting.overtime.common.scripts')
@endsection






