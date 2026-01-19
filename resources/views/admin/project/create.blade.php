@extends('layouts.master')

@section('title', __('index.create_project') )

@section('action', __('index.create_project'))

@section('styles')
    <link rel="stylesheet" href="{{asset('assets/css/imageuploadify.min.css')}}">
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.project.common.breadcrumb')

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form id="projectManagement" class="forms-sample" action="{{route('admin.projects.store')}}" enctype="multipart/form-data" method="POST">
                            @csrf
                            @include('admin.project.common.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.project.common.client_form_model')

    </section>
@endsection

@section('scripts')


    @include('admin.project.common.form_scripts')

@endsection
