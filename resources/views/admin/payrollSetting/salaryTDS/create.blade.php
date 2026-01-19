@extends('layouts.master')

@section('title',__('index.salary_tds'))

@section('page')
    <a href="{{ route('admin.salary-tds.index')}}">
        {{ __('index.salary_tds') }}
    </a>
@endsection

@section('sub_page',__('index.create'))

@section('main-content')

    <section class="content">

        <div id="showSuccessResponse d-none">
            <div class="alert alert-success successSalaryTDS">
                <p class="successMessage"></p>
            </div>
        </div>

        <div id="showErrorResponse d-none">
            <div class="alert alert-danger errorSalaryTDS">
                <p class="errorMessage"></p>
            </div>
        </div>

        @include('admin.section.flash_message')

        @include('admin.payrollSetting.common.breadcrumb')
        <div class="row">
            <div class="col-xl-2 col-lg-3 mb-4">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-xl-10 col-lg-9 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('index.create_salary_tds') }}</h4>
                    </div>
                    <div class="card-body">
                        <form id="salaryTDSAdd" class="forms-sample" action="{{route('admin.salary-tds.store')}}"  method="POST">
                            @csrf
                            <div class="col-lg-3 mb-4">
                                <select class="form-select" id="marital_status" name="marital_status" required >
                                    <option value="" {{old('marital_status') ? '' : 'selected'}}  disabled>{{ __('index.select_marital_status') }} </option>
                                    @foreach(\App\Models\SalaryTDS::MARITAL_STATUS as  $value)
                                        <option value="{{$value}}" {{  (old('marital_status') == $value) ? 'selected': '' }}>  {{ucfirst($value)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="addSalaryTDS">
                                <div class="row salaryTDSList align-items-center justify-content-between mb-3">
                                    <div class="col-lg-3">
                                        <input type="number"
                                               class="form-control"
                                               id="annual_salary_from"
                                               name="annual_salary_from[]"
                                               value=""
                                               placeholder="{{ __('index.enter_annual_salary_from') }}">
                                    </div>

                                    <div class="col-lg-3">
                                        <input type="number"
                                               class="form-control"
                                               id="annual_salary_to"
                                               name="annual_salary_to[]"
                                               value=""
                                               placeholder="{{ __('index.enter_annual_salary_to') }}">
                                    </div>

                                    <div class="col-lg-3">
                                        <input type="number"
                                               class="form-control"
                                               id="tds_in_percent"
                                               name="tds_in_percent[]"
                                               min="0"
                                               step="0.1"
                                               max="100"
                                               value=""
                                               required
                                               placeholder="{{ __('index.enter_tds_in_percent') }}">
                                    </div>

                                    <div class="col-lg-2 text-center addButtonSection float-end">
                                        <button type="button" class="btn btn-md btn-primary" id="add" title="{{ __('index.add_more_tds_detail') }}"> {{ __('index.add') }} </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success" id="salaryTDSSubmit"> {{ __('index.submit') }} </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </section>
@endsection

@section('scripts')
    @include('admin.payrollSetting.salaryTDS.common.scripts')
@endsection
