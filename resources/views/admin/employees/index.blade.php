@php use App\Models\User; @endphp
@extends('layouts.master')

@section('title', __('index.employees_title'))

@section('action', __('index.employees_action'))

@section('button')
    @can('create_employee')
        <div class="float-md-end d-flex align-items-center gap-2 justify-content-center">

            <a href="{{ route('admin.employees.create')}}">
                <button class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="link-icon" data-feather="plus"></i>{{ __('index.add_employee') }}
                </button>
            </a>
        </div>
    @endcan
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.employees.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.employee_lists') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.employees.index') }}" id="employeeFilterForm" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                            <select class="form-control" id="branch" name="branch_id">
                                <option selected disabled>{{ __('index.select_branch') }}</option>
                                @foreach($branches as $branch)
                                    <option
                                        {{ ($filterParameters['branch_id'] == $branch->id) ? 'selected' : '' }} value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <select class="form-control" id="department" name="department_id">
                            <option selected disabled>{{ __('index.select_department') }}</option>
                        </select>
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="text" placeholder="{{ __('index.employee_name') }}" id="employeeName"
                               name="employee_name" value="{{ $filterParameters['employee_name'] }}"
                               class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="text" placeholder="{{ __('index.employee_email') }}" id="email" name="email"
                               value="{{ $filterParameters['email'] }}" class="form-control">
                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="number" placeholder="{{ __('index.employee_phone') }}" id="phone" name="phone"
                               value="{{ $filterParameters['phone'] }}" class="form-control">
                    </div>

                    <div class="col-xxl-4 col-xl-4 col-md-6">
                        <div class="d-md-flex align-items-center gap-2">
                            <button type="submit" value="filter" class="btn btn-block btn-success mb-4">{{ __('index.filter') }}</button>

                            @can('create_employee')
                            <button type="button" id="export_employee" data-href="{{ route('admin.employees.index') }}" value="export"
                                            class="btn btn-block btn-secondary mb-4">{{ __('index.export_csv') }}</button>

                            @endcan
                            <a class="btn btn-block btn-primary mb-4" href="{{ route('admin.employees.index') }}">{{ __('index.reset') }}</a>
                        </div>
                    </div>

                </div>


            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.employee_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            @can('show_detail_employee')
                                <th>#</th>
                            @endcan
                            <th>{{ __('index.full_name') }}</th>
                            <th>{{ __('index.address') }}</th>
                            <th class="text-center">{{ __('index.email') }}</th>
                            <th class="text-center">{{ __('index.designation') }}</th>
                            <th class="text-center">{{ __('index.department') }}</th>
                            <th class="text-center">{{ __('index.shift') }}</th>
                            <th class="text-center">{{ __('index.holiday_check_in') }}</th>
                            <th class="text-center">{{ __('index.workplace') }}</th>
                            <th class="text-center">{{ __('index.is_active') }}</th>
                            @canany(['edit_employee','delete_employee','change_password','force_logout'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
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
                        @forelse($users as $key => $value)
                            <tr>
                                @can('show_detail_employee')
                                    <td>
                                        <a href="{{ route('admin.employees.show', $value->id) }}"
                                           id="showOfficeTimeDetail">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                @endcan
                                <td>
                                    <p>{{ ucfirst($value->name) }}</p>
                                    <small class="text-muted">({{ ucfirst($value->role ? $value->role->name : 'N/A') }}
                                        )</small>
                                </td>
                                <td>{{ ucfirst($value->address) }}</td>
                                <td class="text-center">{{ $value->email }}</td>
                                <td class="text-center">{{ $value->post ? ucfirst($value->post->post_name) : 'N/A' }}</td>
                                <td class="text-center">{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                <td class="text-center">{{ $value->officeTime ? ucfirst($value->officeTime->shift) : 'N/A' }}</td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleHolidayCheckIn"
                                               href="{{ route('admin.employees.toggle-holiday-checkin', $value->id) }}"
                                               type="checkbox" {{ $value->allow_holiday_check_in == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <a class="changeWorkPlace btn btn-{{ $changeColor[$value->workspace_type] }} btn-xs"
                                       data-href="{{ route('admin.employees.change-workspace', $value->id) }}"
                                       title="Change workspace">
                                        {{ $value->workspace_type == User::FIELD ? 'Field' : 'Office' }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="{{ route('admin.employees.toggle-status', $value->id) }}"
                                               type="checkbox" {{ $value->is_active == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['edit_employee','delete_employee','change_password','force_logout'])
                                    <td class="text-center">
                                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown"
                                           role="button"
                                           data-bs-toggle="dropdown"
                                           aria-haspopup="true"
                                           aria-expanded="false"
                                           title="{{ __('index.action') }}"
                                        >
                                        </a>

                                        <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                                            <ul class="list-unstyled p-1 mb-0">
                                                @can('edit_employee')
                                                    <li class="dropdown-item py-2">
                                                        <a href="{{ route('admin.employees.edit', $value->id) }}">
                                                            <button
                                                                class="btn btn-primary btn-xs">{{ __('index.edit_detail') }}</button>
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('delete_employee')
                                                    @if( (isset(auth()->user()->id) && $value->id != auth()->user()->id) || $value->id != 1)
                                                        <li class="dropdown-item py-2">
                                                            <a class="deleteEmployee"
                                                               data-href="{{ route('admin.employees.delete', $value->id) }}">
                                                                <button
                                                                    class="btn btn-primary btn-xs">{{ __('index.delete_user') }}</button>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endcan

                                                @can('change_password')
                                                    <li class="dropdown-item py-2">
                                                        <a class="changePassword"
                                                           data-href="{{ route('admin.employees.change-password', $value->id) }}">
                                                            <button
                                                                class="btn btn-primary btn-xs">{{ __('index.change_password') }}</button>
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('force_logout')
                                                    <li class="dropdown-item py-2">
                                                        <a class="forceLogOut"
                                                           data-href="{{ route('admin.employees.force-logout', $value->id) }}">
                                                            <button
                                                                class="btn btn-primary btn-xs">{{ __('index.force_logout') }}</button>
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('view_card_pdf')
                                                    <li class="dropdown-item py-2">
                                                        <a href="{{ route('employee.card.view', $value->employee_code) }}" target="_blank">
                                                            <button class="btn btn-primary btn-xs">ID Card</button>
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                @endcanany
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
            {{ $users->appends($_GET)->links() }}
        </div>

    </section>
    @include('admin.employees.common.password')
@endsection

@section('scripts')
    @include('admin.employees.common.scripts')
@endsection
