
@extends('layouts.master')

@section('title',__('index.notification'))

@section('action',__('index.edit'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.notification.common.breadcrumb')

        <div class="card">
            <div class="card-body pb-0">
                <form id="notification" class="forms-sample" action="{{route('admin.notifications.update',$notificationDetail->id)}}" enctype="multipart/form-data" method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.notification.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')

    @include('admin.notification.common.scripts')

@endsection

