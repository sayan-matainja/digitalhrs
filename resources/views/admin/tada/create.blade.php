
@extends('layouts.master')

@section('title',__('index.tada'))

@section('action',__('index.create_tada'))

@section('styles')
    <link rel="stylesheet" href="{{asset('assets/css/imageuploadify.min.css')}}">
@endsection

@section('button')
    <a href="{{route('admin.tadas.index')}}" >
        <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.tada.common.breadcrumb')

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form id="createForm" class="forms-sample" action="{{route('admin.tadas.store')}}" enctype="multipart/form-data" method="POST">
                            @csrf
                            @include('admin.tada.common.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.tada.common.scripts')
@endsection
