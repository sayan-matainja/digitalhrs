@extends('layouts.master')
@section('title',__('index.transfer'))
@section('action',__('index.edit'))
@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.transfer.common.breadcrumb')
        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.transfer.update',$transferDetail->id)}}" enctype="multipart/form-data"  method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.transfer.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.transfer.common.from_script')

@endsection

