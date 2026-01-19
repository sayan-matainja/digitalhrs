@extends('layouts.master')

@section('title', __('index.users'))

@section('action', __('index.lists'))

@section('button')

    <a href="{{ route('admin.users.create')}}">
        <button class="btn btn-primary d-flex align-items-center gap-2">
            <i class="link-icon" data-feather="plus"></i>{{ __('index.add_user') }}
        </button>
    </a>


@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.users.common.breadcrumb')


        <div class="card">
        <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.user_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ __('index.full_name') }}</th>
                            <th class="text-center">{{ __('index.email') }}</th>
                            <th class="text-center">{{ __('index.is_active') }}</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <?php
                            $changeColor = [
                                0 => 'success',
                                1 => 'primary',
                            ]
                            ?>
                        @forelse($admins as $key => $value)
                            <tr>
                                <td class="text-center">
                                    <a href="{{ route('admin.users.show', $value->id) }}" id="showOfficeTimeDetail">
                                        <i class="link-icon" data-feather="eye"></i>
                                    </a>
                                </td>
                                <td>{{ ucfirst($value->name) }} </td>
                                <td class="text-center">{{ $value->email }}</td>

                                    <td class="text-center">
                                        <label class="switch">
                                            <input class="toggleStatus"
                                                   href="{{ route('admin.users.toggle-status', $value->id) }}"
                                                   type="checkbox" {{ $value->is_active == 1 ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>


                                        <td class="text-center">
                                            <a class="nav-link dropdown-toggle p-0" href="#" id="profileDropdown"
                                               role="button"
                                               data-bs-toggle="dropdown"
                                               aria-haspopup="true"
                                               aria-expanded="false"
                                               title="{{ __('index.action') }}"
                                            >
                                            </a>

                                            <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                                                <ul class="list-unstyled p-1 mb-0">

                                                    @if($value->id == auth('admin')->user()->id)
                                                        <li class="dropdown-item py-2">
                                                            <a href="{{ route('admin.users.edit', $value->id) }}">
                                                                <button class="btn btn-primary btn-xs">{{ __('index.edit_detail') }}</button>
                                                            </a>
                                                        </li>

                                                    @endif


                                                        @if($value->id != auth('admin')->user()->id || $value->id != 1)
                                                            <li class="dropdown-item py-2">
                                                                <a class="deleteEmployee"
                                                                   data-href="{{ route('admin.users.delete', $value->id) }}">
                                                                    <button class="btn btn-primary btn-xs">{{ __('index.delete_user') }}</button>
                                                                </a>
                                                            </li>
                                                        @endif


                                                    @if($value->id == auth('admin')->user()->id)
                                                        <li class="dropdown-item py-2">
                                                            <a class="changePassword"
                                                               data-href="{{ route('admin.users.change-password', $value->id) }}">
                                                                <button class="btn btn-primary btn-xs">{{ __('index.change_password') }}</button>
                                                            </a>
                                                        </li>
                                                    @endif


                                                </ul>
                                            </div>
                                        </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            {{ $admins->appends($_GET)->links() }}
        </div>

    </section>
    @include('admin.users.common.password')
@endsection

@section('scripts')
    @include('admin.users.common.scripts')
@endsection
