
@extends('layouts.master')

@section('title',__('index.leave_type'))

@section('action',__('index.lists'))

@section('button')
    @canany(['leave_type_create','access_admin_leave'])
        <button class="btn btn-primary create-leaveType mb-3">
            <i class="link-icon" data-feather="plus"></i> {{ __('index.add_leave_type') }}
        </button>

    @endcan
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.dataTables.min.css') }}">
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveType.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.leave_type_filter')</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.leaves.index') }}" method="get">

                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-4 col-md-6 mb-4">
                            <select class="form-select" id="branch" name="branch_id">
                                <option
                                    {{ !isset($filterParameters['branch_id']) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif

                    <div class="col-lg-4 col-md-6 mb-4">
                        <input type="text" class="form-control" name="type" id="title" placeholder="{{ __('index.leave_type') }}"
                               value="{{ $filterParameters['type'] }}">
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-success me-2">@lang('index.filter')</button>
                            <a class="btn btn-block btn-primary"
                               href="{{ route('admin.leaves.index') }}">@lang('index.reset')</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ __('index.name') }}</th>
                            <th>{{ __('index.branch') }}</th>
                            <th class="text-center">{{ __('index.is_paid') }}</th>
                            <th class="text-center">{{ __('index.allocated_days') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['leave_type_edit','leave_type_delete','access_admin_leave'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>


                        @forelse($leaveTypes as $key => $value)
                            <tr>
                                <td class="text-center">{{++$key}}</td>
                                <td>{{ucfirst($value->name)}}</td>
                                <td>{{ ucfirst($value->branch->name ?? '') }}</td>
                                <td class="text-center">{{($value->leave_allocated) ? __('index.yes'):__('index.no')}}</td>
                                <td class="text-center">{{($value->leave_allocated) ?? '-'}}</td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="{{route('admin.leaves.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                @canany(['leave_type_edit','leave_type_delete','access_admin_leave'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @canany(['leave_type_edit','access_admin_leave'])
                                                <li class="me-2">
                                                    <a class="edit-leaveType"  data-id="{{ $value->id }}" data-href="{{ route('admin.leaves.edit', $value->id) }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>

                                                </li>
                                            @endcanany

                                            @canany(['leave_type_delete','access_admin_leave'])
                                                <li>
                                                    <a class="deleteLeaveType"
                                                       data-href="{{route('admin.leaves.delete',$value->id)}}"
                                                       title="{{ __('index.delete_leave_type') }}">
                                                        <i class="link-icon" data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            @endcanany
                                        </ul>
                                    </td>
                            @endcanany

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


    </section>

    <div class="modal fade" id="leaveTypeModal" tabindex="-1" aria-labelledby="leaveTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title" id="leaveTypeModalLabel">{{ __('index.add_leave_type') }}</h5>
                </div>
                <div class="modal-body">
                    <form id="leaveTypeForm" class="forms-sample" enctype="multipart/form-data" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">


                        <div class="row">
                            @if(!isset(auth()->user()->branch_id))
                                <div class="col-lg-6 mb-4">
                                    <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option selected disabled>{{ __('index.select_branch') }}</option>
                                        @if(isset($companyDetail))
                                            @foreach($companyDetail->branches()->get() as $key => $branch)
                                                <option value="{{$branch->id}}">{{ucfirst($branch->name)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif
                            <div class="col-lg-6 mb-4">
                                <label for="name" class="form-label">{{ __('index.leave_type_name') }}  <span style="color: red">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autocomplete="off" placeholder="{{ __('index.leave_type_placeholder') }}">
                            </div>
                            <div class="col-lg-6 mb-4">
                                <label for="gender" class="form-label">{{ __('index.applies_to_gender') }}</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="" {{isset($leaveDetail) ? '': 'selected'}} disabled>{{ __('index.select_gender') }}</option>
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender->value }}" {{ (isset($leaveDetail) && ($leaveDetail->gender ) == $gender->value) ? 'selected':old('gender') }} >
                                            {{ ucfirst($gender->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label for="leave_paid" class="form-label">{{ __('index.is_paid_leave') }} <span style="color: red">*</span></label>
                                <select class="form-select" id="leave_paid" required name="leave_paid">
                                    <option selected disabled></option>
                                    <option value="1">{{ __('index.yes') }}</option>
                                    <option value="0">{{ __('index.no') }}</option>
                                </select>
                            </div>

                            <div class="col-lg-6 mb-4 leaveAllocated " >
                                <label for="leave_allocated" class="form-label">{{ __('index.leave_allocated_days') }} <span style="color: red">*</span></label>
                                <input type="number" min="1" class="form-control" id="leave_allocated"  name="leave_allocated" value="{{ old('leave_allocated') }}" autocomplete="off" placeholder="">
                            </div>

                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="link-icon" data-feather="plus"></i> <span id="submitButtonText">{{ __('index.save') }}</span>
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script src="{{ asset('assets/js/dataTables.min.js') }}"></script>
    <script>
        @if($leaveTypes->isNotEmpty())
        let table = new DataTable('#dataTableExample', {
            pageLength: @json(getRecordPerPage()),
            searching: false,
            paging: true,
        });
        @endif

    </script>
  @include('admin.leaveType.common.scripts')
@endsection






