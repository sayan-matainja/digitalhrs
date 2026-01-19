
@extends('layouts.master')

@section('title',__('index.fiscal_year'))

@section('action',__('index.edit'))

@section('button')
    <a href="{{route('admin.fiscal_year.index')}}" >
        <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> @lang('index.back')</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.fiscalYear.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.fiscal_year.update',$fiscalYearDetail->id)}}"  method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.fiscalYear.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.fiscalYear.common.scripts')
@endsection

