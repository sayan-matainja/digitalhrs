
@extends('layouts.master')

@section('title',__('index.content'))

@section('action',__('index.create'))

@section('button')
    <a href="{{route('admin.static-page-contents.index')}}" >
        <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.contentManagement.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form class="forms-sample" action="{{route('admin.static-page-contents.store')}}" enctype="multipart/form-data" method="POST">
                    @csrf
                    @include('admin.contentManagement.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')

    @include('admin.contentManagement.common.scripts')

@endsection
