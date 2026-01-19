@extends('layouts.master')

@section('title', __('index.holiday'))

@section('action', __('index.edit'))

@section('button')
    <div class="float-md-end">
        <a href="{{ route('admin.holidays.index') }}">
            <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> @lang('index.back')</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.holiday.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">@lang('index.holiday')</h4>
                <form class="forms-sample" action="{{ route('admin.holidays.update', $holidayDetail->id) }}" method="post">
                    @method('PUT')
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
