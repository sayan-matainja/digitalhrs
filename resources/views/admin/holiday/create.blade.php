
@extends('layouts.master')

@section('title',__('index.holiday'))

@section('action',__('index.create'))

@section('button')
    <div class="float-end">
        <a href="{{route('admin.holidays.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> @lang('index.back')</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.holiday.common.breadcrumb')
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.holiday')</h6>
            </div>
            <div class="card-body">
                <form class="forms-sample" action="{{route('admin.holidays.store')}}" method="POST">
                    @csrf
                    @include('admin.holiday.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')

    @include('admin.holiday.common.scripts')

@endsection
