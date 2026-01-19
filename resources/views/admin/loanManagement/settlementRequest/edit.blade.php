
@extends('layouts.master')

@section('title', __('index.loan_settlement_request'))

@section('action', __('index.edit'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.loanManagement.settlementRequest.common.breadcrumb')
        <div class="card">
            <div class="card-body">
                <form id="loan-form" class="forms-sample" action="{{ route('admin.request-settlement.update', $requestDetail->id) }}" enctype="multipart/form-data" method="POST">
                    @method('PUT')
                    @csrf
                    @include('admin.loanManagement.settlementRequest.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.loanManagement.settlementRequest.common.form_scripts')
@endsection
