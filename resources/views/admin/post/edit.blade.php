@extends('layouts.master')

@section('title', __('index.edit_post_title'))

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">{{ __('index.post_section') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.edit') }}</li>
            </ol>
            <a href="{{ route('admin.posts.index') }}" >
                <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
            </a>
        </nav>
        <div class="card">
            <div class="card-body">
                <form class="forms-sample" action="{{ route('admin.posts.update', $postDetail->id) }}" enctype="multipart/form-data" method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.post.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection
@section('scripts')
    @include('admin.post.common.scripts')
@endsection
