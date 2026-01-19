
@extends('layouts.master')

@section('title',__('index.router'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.router.common.breadcrumb')
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.router_detail') </h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{route('admin.routers.store')}}" enctype="multipart/form-data" method="POST">
                    @csrf
                    @include('admin.router.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection
