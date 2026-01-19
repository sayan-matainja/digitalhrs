@extends('layouts.master')

@section('title', __('index.edit_project'))

@section('action', __('index.edit'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/imageuploadify.min.css') }}">
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.project.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="projectEdit" class="forms-sample" action="{{ route('admin.projects.update', $projectDetail->id) }}" enctype="multipart/form-data"  method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.project.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.project.common.form_scripts')
@endsection
