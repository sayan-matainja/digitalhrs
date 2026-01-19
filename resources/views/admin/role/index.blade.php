@extends('layouts.master')

@section('title',__('index.role'))

@section('action',__('index.lists'))

@section('button')
    @can('create_role')
        <a href="{{ route('admin.roles.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>@lang('index.add_role')
            </button>
        </a>
    @endcan
@endsection


@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.role.common.breadcrumb')

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.role_list')</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.role')</th>
                            <th class="text-center">@lang('index.status')</th>
                            <th class="text-center">@lang('index.can_login')</th>
                            @can('role_permission')
                                <th class="text-center">@lang('index.action')</th>
                            @endcan
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        @forelse($roles as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{ucfirst($value->name)}}</td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="{{route('admin.roles.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <td class="text-center">
                                    <span>{{$value->backend_login_authorize ? __('index.yes'):__('index.no')}}</span>
                                </td>

                                @can('role_permission')
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 align-items-center justify-content-center">

                                            <li class="me-2">
                                                <a href="{{route('admin.roles.edit',$value->id)}}"
                                                   title="@lang('index.edit')">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>

                                            <li>
                                                <a class="deleteRole"
                                                   data-href="{{route('admin.roles.delete',$value->id)}}"
                                                   title="@lang('index.delete')">
                                                    <i class="link-icon" data-feather="delete"></i>
                                                </a>
                                            </li>

                                            <li>
                                                <span class="ms-2">
                                                     <a href="{{route('admin.roles.permission',$value->id)}}">
                                                        <button class="btn btn-xs btn-primary ">
                                                          @lang('index.assign_permissions')
                                                        </button>
                                                     </a>
                                                </span>
                                            </li>

                                        </ul>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>@lang('index.no_records_found')</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{--        <div class="row">--}}
        {{--            <div class="dataTables_paginate">--}}
        {{--                {{$roles->appends($_GET)->links()}}--}}
        {{--            </div>--}}
        {{--        </div>--}}


    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.toggleStatus').change(function (event) {
                event.preventDefault();
                var status = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: '@lang('index.change_status_confirm')',
                    showDenyButton: true,
                    confirmButtonText: `@lang('index.yes')`,
                    denyButtonText: `@lang('index.no')`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    } else if (result.isDenied) {
                        (status === 0) ? $(this).prop('checked', true) : $(this).prop('checked', false)
                    }
                })
            })

            $('.deleteRole').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '@lang('index.confirm_role_deletion')',
                    showDenyButton: true,
                    confirmButtonText: `@lang('index.yes')`,
                    denyButtonText: `@lang('index.no')`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })
        });
    </script>
@endsection






