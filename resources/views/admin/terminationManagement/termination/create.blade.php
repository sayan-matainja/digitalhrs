@extends('layouts.master')

@section('title',__('index.termination'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.terminationManagement.termination.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.termination.store')}}" enctype="multipart/form-data"  method="POST">
                    @csrf
                    @include('admin.terminationManagement.termination.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.terminationManagement.termination.common.scripts')

@endsection
