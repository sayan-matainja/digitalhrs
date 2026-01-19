@extends('layouts.master')

@section('title',__('index.award'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.awardManagement.awardDetail.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.awards.store')}}" enctype="multipart/form-data"  method="POST">
                    @csrf
                    @include('admin.awardManagement.awardDetail.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.awardManagement.awardDetail.common.scripts')
@endsection
