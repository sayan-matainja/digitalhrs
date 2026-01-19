
@extends('layouts.master')

@section('title',__('index.role'))

@section('action',__('index.create'))

@section('button')
    <a href="{{route('admin.roles.index')}}" >
        <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> @lang('index.back')</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.role.common.breadcrumb')

        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{route('admin.roles.store')}}" enctype="multipart/form-data" method="POST">
                    @csrf
                    @include('admin.role.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection
