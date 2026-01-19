@extends('layouts.master')

@section('title', __('index.theme_color'))


@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.theme_color') }}</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.theme_color') }}</h6>
            </div>
            <div class="card-body pb-0">

                @if(isset($themeDetail))
                    <form class="forms-sample" method="POST" action="{{ route('admin.theme-color-setting.update', $themeDetail->id) }}">
                        @method('PUT')
                        @else
                            <form class="forms-sample" method="POST" action="{{ route('admin.theme-color-setting.store') }}">
                                @endif
                                @csrf
                                @include('admin.themeColor.form')
                            </form>
            </div>
        </div>

    </section>
@endsection
