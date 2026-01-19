@extends('layouts.master')
@section('title',  __('index.salary_tds') )
@section('sub_page',__('index.lists'))
@section('page')
    <a href="{{ route('admin.salary-tds.index')}}">
        {{ __('index.salary_tds') }}
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
                            @can('salary_tds')
                                <a class="btn btn-success"
                                   href="{{ route('admin.salary-tds.create')}}">
                                    <i class="link-icon" data-feather="plus"></i> {{ __('index.add_salary_tds') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="card-header bg-transparent">
                            <h5 class="text-center">{{ __('index.salary_tds_detail_for_single') }}</h5>
                        </div>
                        <div class="table-responsive ">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.annual_salary_from') }}</th>
                                    <th>{{ __('index.annual_salary_to') }}</th>
                                    <th class="text-center">{{ __('index.tds') }}</th>
                                    <th class="text-center">{{ __('index.status') }}</th>
                                    <th class="text-center">{{ __('index.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($singleSalaryTDS as $key => $value)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td>{{number_format($value->annual_salary_from)}}</td>
                                        <td>{{number_format($value->annual_salary_to)}}</td>
                                        <td class="text-center">{{$value->tds_in_percent}}</td>
                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{route('admin.salary-tds.toggle-status',$value->id)}}"
                                                       type="checkbox" {{($value->status) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                @can('salary_tds')
                                                    <li class="me-2">
                                                        <a href="{{route('admin.salary-tds.edit',$value->id)}}"
                                                           title="Edit Detail">
                                                            <i class="link-icon" data-feather="edit"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="delete" href="#"
                                                           data-href="{{route('admin.salary-tds.delete',$value->id)}}"
                                                           title="Delete">
                                                            <i class="link-icon" data-feather="delete"></i>
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

                    <div class="card-body pt-0">
                        <div class="card-header bg-transparent">
                            <h5 class="text-center">{{ __('index.salary_tds_detail_for_married') }}</h5>
                        </div>
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.annual_salary_from') }}</th>
                                    <th>{{ __('index.annual_salary_to') }}</th>
                                    <th class="text-center">{{ __('index.tds') }}</th>
                                    <th class="text-center">{{ __('index.status') }}</th>
                                    <th class="text-center">{{ __('index.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    @forelse($marriedSalaryTDS as $key => $value)
                                        <td>{{++$key}}</td>
                                        <td>{{number_format($value->annual_salary_from)}}</td>
                                        <td>{{number_format($value->annual_salary_to)}}</td>
                                        <td class="text-center">{{$value->tds_in_percent}}</td>
                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{route('admin.salary-tds.toggle-status',$value->id)}}"
                                                       type="checkbox"{{($value->status) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        @can('salary_tds')
                                        <td class="text-center">
                                            <ul class="d-flex list-unstyled mb-0">
                                                <li class="me-2">
                                                    <a href="{{route('admin.salary-tds.edit',$value->id)}}" title="Edit Detail">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="delete" href="#"
                                                       data-href="{{route('admin.salary-tds.delete',$value->id)}}" title="Delete">
                                                        <i class="link-icon"  data-feather="delete"></i>
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
  @include('admin.payrollSetting.salaryTDS.common.scripts')
@endsection






