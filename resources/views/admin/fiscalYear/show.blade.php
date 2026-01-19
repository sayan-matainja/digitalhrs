@extends('layouts.master')

@section('title','Fiscal Year')

@section('action',$fiscalYear->title)

@section('button')
    <div class="float-end">
        <a href="{{route('admin.fiscal_year.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> @lang('index.back')</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.fiscalYear.common.breadcrumb')

        <div class="card support-main">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <tbody>
                        <tr>

                            <th>@lang('index.year')</th><td>{{ ucfirst($fiscalYear->year) }}</td>
                            <th>@lang('index.start_date')</th><td>
                                {{\App\Helpers\AppHelper::formatDateForView($fiscalYear->start_date)}}
                            </td>
                            <th>@lang('index.end_date')</th>  <td>
                                {{\App\Helpers\AppHelper::formatDateForView($fiscalYear->end_date)}}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection


