
@extends('layouts.master')

@section('title',__('index.advance_salary'))

@section('page',__('index.advance_salary'))
@section('sub_page',__('index.create'))
@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payrollSetting.common.breadcrumb')
        <div class="row">
            <div class="col-xl-2 col-lg-3 mb-4">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-xl-10 col-lg-9 mb-4">
                <div class="card">
                    <div class="card-body">
                        <form class="forms-sample" action="{{route('admin.advance-salaries.setting.store',$advanceSalarySetting->id)}}"  method="POST">
                            @csrf
                            <div class="row">

                                <div class="col-lg-6 col-md-6 mb-3">
                                    <label for="title" class="form-label"> {{ $advanceSalarySetting->name }} <span style="color: red">*</span></label>
                                    <input type="number"
                                           class="form-control"
                                           id="title" step="0.1" min="0" name="value" required
                                           value="{{ isset($advanceSalarySetting) ? $advanceSalarySetting->value: old('title') }}"
                                           autocomplete="off"
                                           placeholder="{{ __('index.advance_salary_limit') }}(%)">
                                    @error('title')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                @can('advance_salary_limit')
                                <div class="col-6 col-md-6 mt-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="link-icon" data-feather="{{ isset($underTime) ? 'edit-2':'plus'}}"></i>
                                        {{ __('index.update') }}
                                    </button>
                                </div>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </section>
@endsection

