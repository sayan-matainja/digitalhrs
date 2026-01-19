@extends('layouts.master')
@section('title',__('index.training'))
@section('action',__('index.edit'))
@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.trainingManagement.training.common.breadcrumb')
        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.training.update',$trainingDetail->id)}}" enctype="multipart/form-data"  method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.trainingManagement.training.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.trainingManagement.training.common.scripts')
@endsection

