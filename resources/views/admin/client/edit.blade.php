@extends('layouts.master')

@section('title', __('index.edit_client'))

@section('action', __('index.edit_client_detail'))

@section('button')
    <a href="{{ route('admin.clients.index') }}">
        <button class="btn btn-sm btn-primary">
            <i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}
        </button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.client.common.breadcrumb')

        <div class="card">
            <div class="card-body pb-0">
                <form id="clientEdit" class="forms-sample" action="{{ route('admin.clients.update', $clientDetail->id) }}" enctype="multipart/form-data" method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.client.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.client.common.scripts')
@endsection
