@extends('layouts.master')
@section('title', __('index.salary_group'))
@section('sub_page', 'Lists')
@section('page')
    <a href="{{ route('admin.salary-groups.index') }}">
        {{ __('index.salary_group') }}
    </a>
@endsection
@section('styles')
    <style>
        .select2-container--open .select2-dropdown {
            z-index: 9999;
        }
    </style>
@endsection
@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.payrollSetting.common.breadcrumb')
        <div class="row">
            <div class="col-lg-2">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <div class="justify-content-end">
                            @can('salary_group')
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#salaryGroupModal" data-action="create" data-url="{{ route('admin.salary-groups.store') }}">
                                    <i class="link-icon" data-feather="plus"></i> {{ __('index.add_salary_group') }}
                                </button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>{{ __('index.name') }}</th>
                                    <th>{{ __('index.salary_components') }}</th>
                                    <th class="text-center">{{ __('index.is_active') }}</th>
                                    <th class="text-center">{{ __('index.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($salaryGroupLists as $key => $value)
                                    <tr>
                                        <td class="text-center">{{ ++$key }}</td>
                                        <td>
                                            {{ ucfirst($value->name) }}<br>
                                            <small>{{ __('index.employee_count') }} : {{ $value->group_employees_count }}</small>
                                        </td>
                                        <td>
                                            <ul>
                                                @foreach($value?->salaryComponents as $key => $componentValue)
                                                    <li>{{ ucwords($componentValue?->name) }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{ route('admin.salary-groups.toggle-status', $value->id) }}"
                                                       type="checkbox" {{ $value->is_active == 1 ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                @can('salary_group')
                                                    <li class="me-2">
                                                        <a href="#" class="edit-salary-group"
                                                           data-bs-toggle="modal" data-bs-target="#salaryGroupModal"
                                                           data-action="edit"
                                                           data-url="{{ route('admin.salary-groups.update', $value->id) }}"
                                                           data-id="{{ $value->id }}"
                                                           data-name="{{ $value->name }}"
                                                           data-salary-component-ids="{{ json_encode($value->salaryComponents->pluck('id')->toArray()) }}"
                                                           data-employee-ids="{{ json_encode($value->groupEmployees->pluck('employee_id')->toArray()) }}"
                                                           data-department-ids="{{ json_encode($value->department_ids ?? []) }}"
                                                           data-branch-id="{{ $value->branch_id ?? '' }}"
                                                           title="Edit Detail">
                                                            <i class="link-icon" data-feather="edit"></i>
                                                        </a>

                                                    </li>

                                                    <li>
                                                        <a class="delete" href="#"
                                                           data-href="{{ route('admin.salary-groups.delete', $value->id) }}"
                                                           title="Delete">
                                                            <i class="link-icon" data-feather="delete"></i>
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
            </div>
        </div>
    </section>

    <div class="modal fade" id="salaryGroupModal" tabindex="-1" aria-labelledby="salaryGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title" id="salaryGroupModalLabel">{{ __('index.add_salary_group') }}</h5>
                </div>
                <form class="forms-sample" method="POST">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="_method" value="POST">
                        <div class="row">
                            <div class="col-lg-12 mb-3">
                                <label for="name" class="form-label">{{ __('index.name') }}<span style="color: red">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="name"
                                       required
                                       name="name"
                                       value="{{ old('name') }}"
                                       autocomplete="off"
                                       placeholder="{{ __('index.enter_salary_group_name') }}">
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="salaryComponent" class="form-label">{{ __('index.assign_salary_components') }} <span style="color: red">*</span></label>
                                <select class="col-md-12 form-select" id="salaryComponent" name="salary_component_id[]" multiple="multiple" required>
                                    @foreach($salaryComponents as $key => $value)
                                        <option value="{{ $key }}">{{ ucfirst($value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!isset(auth()->user()->branch_id))
                                <div class="col-lg-12 mb-3">
                                    <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                    <select class="form-control" id="branch_id" name="branch_id" required>
                                        <option selected disabled>{{ __('index.select_branch') }}</option>
                                        @if(isset($companyDetail))
                                            @foreach($companyDetail->branches()->get() as $key => $branch)
                                                <option value="{{$branch->id}}">{{ ucfirst($branch->name) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif
                            <div class="col-lg-12 mb-3">
                                <label for="department_id" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
                                <select class="form-control" id="department_id" name="department_id[]" multiple required>
                                </select>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="employee_id" class="form-label">{{ __('index.employees') }} <span style="color: red">*</span></label>
                                <select class="form-control" id="employee_id" name="employee_id[]" multiple required>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="submit" class="btn btn-primary">
                            <i class="link-icon" data-feather="plus" id="submit-icon"></i>
                            <span id="submit-text">{{ __('index.create') }} {{ __('index.salary_group') }}</span>
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @include('admin.payrollSetting.salaryGroup.common.scripts')
@endsection
