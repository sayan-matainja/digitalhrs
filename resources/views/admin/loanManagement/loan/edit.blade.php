
@extends('layouts.master')

@section('title', __('index.loan'))

@section('action', __('index.edit'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.loanManagement.loan.common.breadcrumb')
        <div class="card">
            <div class="card-body">
                <form id="loan-form" class="forms-sample" action="{{ route('admin.loan.update', $loanDetail->id) }}" enctype="multipart/form-data" method="POST">
                    @method('PUT')
                    @csrf
                    @include('admin.loanManagement.loan.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.loanManagement.loan.common.form_scripts')
@endsection
