@extends('layouts.master')

@section('title',__('index.complaint'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.complaint.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.complaint.store')}}" enctype="multipart/form-data"  method="POST">
                    @csrf
                    @include('admin.complaint.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.complaint.common.scripts')
@endsection
