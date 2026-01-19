@extends('layouts.master')

@section('title',__('index.complaint'))

@section('action',__('index.lists'))

@section('button')
    @can('create_complaint')
        <a href="{{ route('admin.complaint.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_complaint') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.complaint.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{  __('index.complaint_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.complaint.index') }}" method="get">

                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  selected  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id']  == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif


                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" multiple name="department_id[]" id="department_id">
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" multiple name="employee_id[]" id="employee_id">
                        </select>
                    </div>

                    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text"  id="nepali-datepicker-from"
                                   name="complaint_date"
                                   value="{{ $filterParameters['complaint_date'] ?? '' }}"
                                   placeholder="mm/dd/yyyy"
                                   class="form-control nepali_date"/>
                        </div>


                    @else
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date"  value="{{ $filterParameters['complaint_date'] ?? '' }}" name="complaint_date" class="form-control">
                        </div>

                    @endif


                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">{{  __('index.filter') }}</button>
                            <a href="{{route('admin.complaint.index')}}" class="btn btn-block btn-primary">{{  __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.complaint_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.subject') }}</th>
                            <th class="text-center">{{ __('index.employees') }}</th>
                            <th class="text-center">{{ __('index.complaint_date') }}</th>
                            @canany(['show_complaint','delete_complaint','update_complaint'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($complaintLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{ $value->subject }}</td>
                                    <td class="text-center">
                                        <a
                                           onclick="showEmployees({{ json_encode($value->complaintEmployee, JSON_HEX_APOS) }})"
                                           title="{{ __('index.employee_list_title') }}">
                                            <i class="link-icon" data-feather="users"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {{ \App\Helpers\AppHelper::formatDateForView($value->complaint_date) }}
                                    </td>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_complaint')
                                                <li class="me-2">
                                                    <a href="{{route('admin.complaint.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_complaint')
                                                <li class="me-2">
                                                    <a href="{{route('admin.complaint.show',$value->id)}}" title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_complaint')
                                                <li>
                                                    <a class="deleteComplaint"
                                                       data-title="{{ $value->subject }} Detail"
                                                       data-href="{{route('admin.complaint.delete',$value->id)}}"
                                                       title="{{ __('index.delete') }}">
                                                        <i class="link-icon"  data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            @endcan
                                          </ul>
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
            {{ $complaintLists->appends($_GET)->links() }}
        </div>
    </section>
    @include('admin.complaint.employee')

@endsection

@section('scripts')
    @include('admin.complaint.common.scripts')

    <script>
        function showEmployees(data) {
            console.log(data);
            if (data && data.length > 0) {
                $('.complaint_employee_id').empty();

                let employeeList = '<ul class="mb-0">';
                data.forEach(complaint => {
                    if (complaint.employee && complaint.employee.name) {
                        employeeList += `<li>${complaint.employee.name}</li>`;
                    }
                });
                employeeList += '</ul>';

                $('.complaint_employee_id').html(employeeList);
                $('.complaintEmployeeTitle').text('@lang('index.employee_list_title')');

                // Use Bootstrap 5 modal method
                const modal = new bootstrap.Modal(document.getElementById('complaintEmployeeDetail'));
                modal.show();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Employees Not Found',
                    icon: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    </script>
@endsection

