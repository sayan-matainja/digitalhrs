@extends('layouts.master')
@section('title',__('index.salary_components'))
@section('sub_page',__('index.lists'))
@section('page')
        <a href="{{ route('admin.bonus.index')}}">
            {{ __('index.bonus') }}
        </a>
@endsection
@section('styles')
    <style>
        .select2-close-mask{
            z-index: 2099;
        }
        .select2-dropdown{
            z-index: 3051;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.dataTables.min.css') }}">
@endsection
@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.payrollSetting.common.breadcrumb')

        <div class="row">
            <div class="col-lg-2 mb-4">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <div class="justify-content-end">
                            @can('bonus')

                                <a class="btn btn-success" href="#" data-bs-toggle="modal" data-bs-target="#bonusModal" data-mode="create">
                                    <i class="link-icon" data-feather="plus"></i>{{ __('index.add_bonus') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>{{ __('index.title') }}</th>
                                    <th class="text-center">{{ __('index.assigned_employees') }}</th>
                                    <th class="text-center">{{ __('index.value_type') }}</th>
                                    <th class="text-center">{{ __('index.value') }}</th>
                                    <th class="text-center">{{ __('index.status') }}</th>
                                    @can('bonus')
                                    <th class="text-center">{{ __('index.action') }}</th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($bonusList as $key => $value)
                                    <tr>
                                        <td class="text-center">{{++$key}}</td>
                                        <td>{{ucfirst($value->title)}}</td>
                                        <td class="text-center">
                                            <a
                                                onclick="showEmployees({{ json_encode($value->bonusEmployee, JSON_HEX_APOS) }})"
                                                title="{{ __('index.employee_list_title') }}">
                                                <i class="link-icon" data-feather="users"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{ \App\Enum\BonusTypeEnum::from($value->value_type)->getFormattedName() }}
                                        </td>

                                        <td class="text-center">{{ $value->value  }}{{$value->value_type == \App\Enum\BonusTypeEnum::fixed->value ? '': '%'}}</td>
                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{route('admin.bonus.toggle-status',$value->id)}}"
                                                       type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>

                                        @can('bonus')
                                        <td class="text-center">
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">

                                                    <li class="me-2">
                                                        <a href="#" data-href="{{route('admin.bonus.edit',$value->id)}}" class="edit-bonus" data-id="{{ $value->id }}"
                                                           data-bs-toggle="modal" data-bs-target="#bonusModal" data-mode="edit"
                                                           title="Edit Detail">
                                                            <i class="link-icon" data-feather="edit"></i>
                                                        </a>

                                                    </li>

                                                    <li>
                                                        <a class="delete"
                                                           data-href="{{route('admin.bonus.delete',$value->id)}}"
                                                           title="Delete">
                                                            <i class="link-icon" data-feather="delete"></i>
                                                        </a>
                                                    </li>
                                            </ul>
                                        </td>
                                        @endcan
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
    <div class="modal fade" id="bonusModal" tabindex="-1" aria-labelledby="bonusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header text-center">
                    <h5 class="modal-title transferTitle" id="bonusModalLabel"></h5>
                </div>
                <form class="forms-sample" id="bonusForm" action="" method="POST">
                    @csrf
                    <div class="modal-body pb-2">
                        <input type="hidden" name="_method" id="formMethod" value="POST">
                        <div class="row align-items-center justify-content-between">

                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="name" class="form-label"> {{ __('index.title') }} <span style="color: red">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="name" name="title" required
                                       value="{{ old('title') }}"
                                       autocomplete="off"
                                       placeholder="{{ __('index.enter_bonus_type') }}">
                            </div>

                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="value_type" class="form-label">{{ __('index.value_type') }} <span style="color: red">*</span></label>
                                <select class="form-select" id="value_type" name="value_type" required>
                                    <option selected disabled>{{ __('index.select_value_type') }}</option>
                                    @foreach(\App\Enum\BonusTypeEnum::cases() as $case)
                                        <option value="{{ $case->value }}">{{ Str::title(str_replace('_', ' ', $case->name)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="value" class="form-label">{{ __('index.value') }}</label>
                                <input type="number" min="0" step="0.1" class="form-control" id="value" name="value"
                                       value="{{ old('value') }}"
                                       autocomplete="off">
                            </div>

                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="applicable_month" class="form-label">{{ __('index.applicable_month') }}<span style="color: red">*</span></label>
                                <select class="form-select" id="applicable_month" name="applicable_month" required>
                                    <option value="" {{ isset($bonusDetail) || old('applicable_month') ? '' : 'selected' }} disabled>{{ __('index.select_month') }}</option>
                                    @foreach($months as $key=>$value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!isset(auth()->user()->branch_id))
                                <div class="col-lg-6 col-md-6 mb-3">
                                    <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                    <select class="form-control" id="branch_id" name="branch_id" required>
                                        <option selected disabled>{{ __('index.select_branch') }}</option>
                                        @if(isset($companyDetail))
                                            @foreach($companyDetail->branches()->get() as $key => $branch)
                                                <option value="{{$branch->id}}">
                                                    {{ucfirst($branch->name)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="department_id" class="form-label">{{ __('index.department') }} </label>
                                <select class="form-control" id="department_id" name="department_id[]" multiple>

                                </select>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="employee_id" class="form-label">{{ __('index.employees') }} </label>
                                <select class="form-control" id="employee_id" name="employee_id[]" multiple>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <input type="checkbox" name="apply_for_all" id="apply_for_all" value="1">
                                {{ __('index.apply_for_all') }}
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="submitBonus">
                            <i class="link-icon" data-feather="plus"></i><span>{{ __('index.add') }}</span>
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bonusEmployeeDetail" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title bonusEmployeeTitle">{{ __('index.employee_list_title') }}</h5>
                </div>
                <div class="modal-body">
                    <table id="dataTableExample" class="table">
                        <tbody>
                        <tr>
                            <td>{{__('index.employees')}}</td>
                            <td class="bonus_employee_id"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>


        function showEmployees(data) {

            if (data && data.length > 0) {
                $('.bonus_employee_id').empty();

                let employeeList = '<ul class="mb-0">';
                data.forEach(bonus => {
                    if (bonus.employee && bonus.employee.name) {
                        employeeList += `<li>${bonus.employee.name}</li>`;
                    }
                });
                employeeList += '</ul>';

                $('.bonus_employee_id').html(employeeList);
                $('.bonusEmployeeTitle').text('@lang('index.employee_list_title')');

                // Use Bootstrap 5 modal method
                const modal = new bootstrap.Modal(document.getElementById('bonusEmployeeDetail'));
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
    @include('admin.payrollSetting.bonus.common.scripts')
@endsection






