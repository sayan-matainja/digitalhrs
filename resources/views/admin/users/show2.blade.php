@extends('layouts.master')

@section('title', __('index.users'))

@section('action', __('index.detail'))

@section('button')
    <div class="d-md-flex">

            <a href="{{ route('admin.users.edit', $userDetail->id) }}">
                <button class="btn btn-secondary me-2">
                    <i class="link-icon" data-feather="edit"></i>{{ __('index.edit_detail') }}
                </button>
            </a>


        <a href="{{ route('admin.users.index') }}">
            <button class="btn btn-primary "><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')
        @include('admin.users.common.breadcrumb')


        <div class="d-md-flex align-items-center text-md-start text-center mb-md-4 mb-2">
            <img class="wd-100 ht-100 rounded-circle" style="object-fit: cover"
                 src="{{ asset(\App\Models\Admin::AVATAR_UPLOAD_PATH . $userDetail->avatar) }}" alt="profile">
            <div class="ms-md-3 mt-md-0 mt-2">
                <span class="fw-bold">{{ ucfirst($userDetail->name) }}</span>
                <p class="">{{ ucfirst($userDetail->email) }}</p>
            </div>
        </div>

        <div class="row profile-body">
            <div class="col-lg-6 mb-4 d-flex">
                <div class="card rounded w-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0" style="align-content: center;">{{ __('index.user_detail') }}</h6>
                    </div>
                    <div class="card-body card-profile py-2">

                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4">{{ __('index.username') }}:</label>
                                <p class="d-inline-block">{{ $userDetail->username }}</p>
                            </div>

                        </div>





                        <div class="d-md-flex align-items-center justify-content-between mb-2 border-bottom pb-2">


                            <div class="w-100 py-2 d-flex align-items-center">
                                <label class="fw-bolder mb-0 text-uppercase w-45 border-end me-4">{{ __('index.is_active') }}:</label>
                                <p class="d-inline-block">{{ $userDetail->is_active == 1 ? __('index.yes') : __('index.no') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>

    </section>
@endsection
