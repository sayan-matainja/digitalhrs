@extends('layouts.master')

@section('title',__('index.employee_salary'))

@section('action',__('index.list'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.employeeSalary.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.employee_salary_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{route('admin.employee-salaries.index')}}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" id="branch_id" name="branch_id" >
                            <option value="">{{ __('index.all') }}</option>
                            @if(isset($companyDetail))
                                @foreach($companyDetail->branches()->get() as $key => $branch)
                                    <option value="{{$branch->id}}"
                                        {{ (isset($filterParameters['branch_id']) && ($filterParameters['branch_id']) == $branch->id) ? 'selected': '' }}>
                                        {{ucfirst($branch->name)}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" id="department_id" name="department_id" >
                            <option value="">{{ __('index.all') }}</option>

                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <input type="text" id="employee_name" placeholder="{{ __('index.employee_name') }}" name="employee_name" value="{{$filterParameters['employee_name']}}" class="form-control">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-secondary me-2">{{ __('index.filter') }}</button>
                            <a class="btn btn-block btn-primary" href="{{route('admin.employee-salaries.index')}}">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.employee_salaries') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee_name') }}</th>
                            <th class="text-center">{{ __('index.marital_status') }}</th>
{{--                            <th>Gross Salary({{\App\Helpers\AppHelper::getCompanyPaymentCurrencySymbol()}}.)</th>--}}
                            <th class="text-center">{{ __('index.salary_cycle') }}</th>
                            <th class="text-center">{{ __('index.salary_group') }}</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($employeeLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{ucfirst($value->employee_name)}}</td>
                                    <td class="text-center">{{ ucfirst($value->marital_status) }}</td>
{{--                        <td>{{ number_format($value->salary) }}</td>--}}
                            <td class="text-center">
                                <select class="form-control-sm"
                                        name="salary_cycle"
                                        id="salaryCycle"
                                        data-employee="{{$value->employee_id}}"
                                        data-current="{{$value->salary_cycle}}"
                                >
                                    @foreach(\App\Models\EmployeeAccount::SALARY_CYCLE as $salaryCycle)
                                            <option value="{{$salaryCycle}}" {{$value->salary_cycle == $salaryCycle ? 'selected' : '' }}>
                                                {{ucfirst($salaryCycle)}}
                                            </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-center">{{ ucfirst($value->salary_group_name)  }}</td>
                            <td class="text-center">
                                <a class="nav-link dropdown-toggle p-0" href="#" id="payslipDropdown"
                                    role="button"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    title="More Action"
                                > </a>

                                <div class="dropdown-menu p-0" aria-labelledby="payslipDropdown">
                                    <ul class="list-unstyled mb-0">
                                        @php
                                            $employeeSalaryStatus = \App\Helpers\AttendanceHelper::checkEmployeeSalary($value->employee_id)
                                        @endphp

                                        @if($employeeSalaryStatus == 0)
                                            @can('add_salary')
                                                <li class="dropdown-item p-2 border-bottom">
                                                    <a title="generate payroll"
                                                        href="{{ route('admin.employee-salaries.add', $value->employee_id) }}">
                                                        <button class="btn btn-primary btn-xs"> @lang('index.add_salary')
                                                        </button>
                                                    </a>
                                                </li>
                                            @endcan
                                        @else
                                            @can('edit_salary')
                                                <li class="dropdown-item p-2 border-bottom">
                                                    <a title="generate payroll"
                                                        href="{{ route('admin.employee-salaries.edit-salary', $value->employee_id) }}">
                                                        <button class="btn btn-primary btn-xs">{{ __('index.edit_salary') }}
                                                        </button>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('salary_increment')
                                                <li class="dropdown-item p-2 border-bottom">
                                                    <a title="Update Employee Salary"
                                                        href="{{route('admin.employee-salaries.increase-salary',$value->employee_id)}}">
                                                        <button class="btn btn-primary btn-xs">{{ __('index.increase_salary') }}
                                                        </button>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('show_salary_history')
                                                <li class="dropdown-item p-2 border-bottom">
                                                    <a href="{{route('admin.employee-salaries.salary-revise-history.show',$value->employee_id)}}"
                                                        class="viewSalaryReviseHistory me-2"
                                                        title="show salary revised log">
                                                        <button class="btn btn-primary btn-xs">{{ __('index.salary_review_history') }}
                                                        </button>
                                                    </a>
                                                </li>
                                            @endcan
                                                @can('delete_salary')
                                                    <li class="dropdown-item p-2">
                                                        <a
                                                            data-href="{{ route('admin.employee-salaries.delete-salary',$value->employee_id) }}"
                                                            class="deleteEmployeeSalary me-2"
                                                            title="show salary revised log">
                                                            <button class="btn btn-primary btn-xs">{{ __('index.delete') }}
                                                            </button>
                                                        </a>
                                                    </li>
                                                @endcan
                                        @endif

                                    </ul>
                                </div>
                            </td>
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

@endsection

@section('scripts')
    @include('admin.payroll.employeeSalary.common.scripts')
@endsection






