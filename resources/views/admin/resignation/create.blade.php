@extends('layouts.master')

@section('title',__('index.resignation'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.resignation.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.resignation.store')}}" enctype="multipart/form-data"  method="POST">
                    @csrf
                    @include('admin.resignation.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.resignation.common.scripts')
@endsection
