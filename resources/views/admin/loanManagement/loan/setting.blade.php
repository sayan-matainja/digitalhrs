@extends('layouts.master')

@section('title',__('index.loan_setting'))

@section('action',__('index.setting'))


@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.loanManagement.loan.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>@lang('index.name') </th>
                            <th>@lang('index.value')</th>
                            <th class="text-center">@lang('index.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $key => $datum)
                                    <form class="forms-sample"
                                          action="{{route('admin.loanSetting.update',$datum->id)}}"  method="POST">
                                        @csrf
                                            <tr>
                                                <td class="text-center">
                                                    <i class="link-icon" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                       title="{{__('seeder.'.$datum->key.'_description')}}" data-feather="info"></i>
                                                </td>
                                                <td>
                                                    {{ucfirst(__('seeder.'.$datum->key))}} <span style="color: red">*</span>
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control" id="value" name="value"
                                                           value="{{ $datum->value}}" autocomplete="off">
                                                </td>


                                                <td class="text-center">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="link-icon" data-feather="plus"></i> @lang('index.update')
                                                    </button>
                                                </td>

                                            </tr>

                                    </form>
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
@endsection






