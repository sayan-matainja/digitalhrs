@extends('layouts.master')

@section('title',__('index.general_setting'))

@section('action',__('index.lists'))


@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.generalSetting.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>@lang('index.name') </th>
                            <th>@lang('index.value')</th>
                            @can('general_setting')
                                <th class="text-center">@lang('index.action')</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($generalSettings as $key => $datum)
                                    <form class="forms-sample"
                                          action="{{route('admin.general-settings.update',$datum->id)}}" method="post">
                                        @method('PUT')
                                        @csrf


                                            <tr>
                                                <td class="text-center">
                                                    <i class="link-icon" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                       title="{{__('seeder.'.$datum->key.'_description')}}" data-feather="info"></i>
                                                </td>
                                                <td>
                                                    {{ucfirst(__('seeder.'.$datum->key))}} <span style="color: red">*</span>
                                                </td>
                                                @if($datum->key == 'attendance_method')
                                                <td>
                                                    <select class="form-select" id="value" name="value">
                                                        <option {{ isset($generalSettingDetail) ? '':'selected'}} disabled>Select Attendance Type</option>
                                                        @foreach(\App\Enum\EmployeeAttendanceTypeEnum::cases() as $key => $enum)
                                                            <option value="{{$enum->value}}" {{ $datum->value == $enum->value ? 'selected':''}} > {{$enum->name}}</option>
                                                        @endforeach
                                                    </select>

                                                </td>
                                                @else

                                                    <td>
                                                        <input type="text" class="form-control" id="value" name="value"
                                                               value="{{ $datum->value}}" autocomplete="off">
                                                    </td>

                                                @endif

                                                @can('general_setting')
                                                    <td class="text-center">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="link-icon" data-feather="plus"></i> @lang('index.update')
                                                        </button>
                                                    </td>
                                                @endcan
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






