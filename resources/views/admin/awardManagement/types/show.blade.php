@extends('layouts.master')

@section('title',__('index.award_types'))

@section('action',$awardTypes->title)

@section('button')
    <div class="float-end">
        <a href="{{route('admin.award-types.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.awardManagement.types.common.breadcrumb')

        <div class="card support-main">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee_name') }}</th>
                            <th>{{ __('index.awarded_date') }}</th>
                            <th class="text-center">{{ __('index.gift_item') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($awardTypes->awards as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>
                                    {{ ucfirst($value->employee?->name) }}
                                </td>
                                <td>
                                    {{\App\Helpers\AppHelper::formatDateForView($value->awarded_date)}}
                                </td>
                                <td class="text-center">
                                    {{ucfirst($value->gift_item)}}
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
    </section>
@endsection


