@extends('layouts.master')

@section('title',__('index.warning'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.warning.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.warning.store')}}" enctype="multipart/form-data"  method="POST">
                    @csrf
                    @include('admin.warning.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.warning.common.scripts')
@endsection
