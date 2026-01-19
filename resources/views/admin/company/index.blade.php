@extends('layouts.master')

@section('title', __('index.company_profile'))

{{--@section('nav-head', __('index.company_profile'))--}}

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.company_profile') }}</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-body pb-0">
                <h4 class="mb-4">{{ __('index.company_profile') }}</h4>
                <form class="forms-sample" enctype="multipart/form-data" method="POST"
                      @if(!$companyDetail)
                          action="{{route('admin.company.store')}}"
                      @else
                          action="{{route('admin.company.update', $companyDetail->id)}}"
                >
                    @method('PUT')
                    @endif

                    @csrf
                    @include('admin.company.form')
                </form>
            </div>
        </div>

    </section>
@endsection
