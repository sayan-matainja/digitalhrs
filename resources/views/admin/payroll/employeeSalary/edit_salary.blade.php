@extends('layouts.master')

@section('title',__('index.employee_salary'))

@section('action',__('index.edit_salary'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.employee-salaries.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.employeeSalary.common.breadcrumb')

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"> {{ __('index.payroll_edit') }}- {{ $employee->name }}</h6>
            </div>
            <form class="forms-sample" action="{{ route('admin.employee-salaries.update-salary',$employee->id) }}" method="POST">
            <div class="card-body">

                    @csrf
                    @method('PUT')
                    <input type="hidden" readonly name="employee_id" value ="{{$employee->id}}">
                    <div class="payroll-fil" x-data="createEmployeeSalary('{{$percentType}}', {{ json_encode($salaryComponents) }}, {{ json_encode($employeeSalary) }})">

                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 col-md-6">
                                <label for="payroll_type" class="form-label">{{ __('index.payroll_type') }}</label>
                                <select class="form-control" name="payroll_type" id="payroll_type" x-model="payroll_type" @change="updateFields()">
                                    <option selected disabled>{{ __('index.select_payroll_type') }}</option>
                                    <option value="annual">{{ __('index.annual') }}</option>
                                    <option value="hourly">{{ __('index.hourly') }}</option>
                                </select>
                            </div>
                            <div class="p-2 col-md-6">
                                <label for="payment_type" class="form-label">{{ __('index.payment_type') }}</label>
                                <select class="form-control" name="payment_type" id="payment_type" x-model="payment_type" @change="updateFields()">
                                    <option selected disabled>{{ __('index.select_salary_cycle') }}</option>
                                    <option value="monthly">{{ __('index.monthly') }}</option>
                                    <option value="weekly">{{ __('index.weekly') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Conditional rendering based on payroll_type and payment_type -->
                        <div class="row">
                            <div class="col-lg-4 col-md-4 mb-4" x-show="payroll_type === 'hourly' || (payroll_type === 'annual' && payment_type === 'weekly')">
                                <label for="hourRate" class="form-label">{{ __('index.hourly_rate') }}</label>
                                <input type="number" min="0" step="0.01" x-model="hour_rate" name="hour_rate" class="form-control" @input="calculateAnnualSalary()" oninput="validity.valid||(value='');" placeholder="Enter Hourly Rate" id="hourRate">
                            </div>
                            <div class="col-lg-4 col-md-4 mb-4" x-show="payment_type === 'weekly'">
                                <label for="weeklyHour" class="form-label">{{ __('index.working_hours_in_week') }}</label>
                                <input type="number" min="0" step="0.1" x-model="weekly_hours" class="form-control" @input="calculateAnnualSalary()" oninput="validity.valid||(value='');" placeholder="Enter Weekly Hours" name="weekly_hours" id="weeklyHour">
                            </div>
                            <div class="col-lg-4 col-md-4 mb-4" x-show="payroll_type === 'hourly' && payment_type === 'monthly'">
                                <label for="monthlyHour" class="form-label">{{ __('index.working_hours_in_month') }}</label>
                                <input type="number" min="0" step="0.1" x-model="monthly_hours" class="form-control" @input="calculateAnnualSalary()" oninput="validity.valid||(value='');" placeholder="Enter Monthly Hours" name="monthly_hours" id="monthlyHour">
                            </div>
                            <div class="col-lg-4 col-md-4 mb-4">
                                <label for="annualSalary" class="form-label">{{ __('index.annual_salary') }}</label>
                                <input type="number" min="0" step="0.1" x-model="annual_salary" class="form-control" @input="calculateSalary()" oninput="validity.valid||(value='');" placeholder="Enter Annual Salary" name="annual_salary" id="annualSalary" x-bind:readonly="payroll_type === 'hourly'">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table border-end">
                                <thead>
                                <tr>
                                    <th>{{ __('index.salary_component') }}</th>
                                    <th>{{ __('index.calculation_type') }}</th>
                                    <th x-show="payment_type === 'weekly'">{{ __('index.weekly_amount') }} ({{ $currency }})</th>
                                    <th x-show="payment_type === 'monthly'">{{ __('index.monthly_amount') }} ({{ $currency }})</th>
                                    <th>{{ __('index.annual_amount') }} ({{ $currency }})</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="5"> <h4>{{ __('index.earnings') }}</h4></td>
                                </tr>
                                <tr>
                                    <td>{{ __('index.basic_salary') }}</td>
                                    <td>
                                        <div style="display: flex;">
                                            <input type="number" min="0" step="0.1" max="100" class="form-control" @input="calculateSalary()" x-model="basic_salary_value" name="basic_salary_value" id="basicSalaryValue" style="width: 60%;">
                                            <select class="form-control" x-model="basic_salary_type" @change="calculateSalary()" name="basic_salary_type" style="width: 57%;">
                                                <option value="{{ $percentType }}">% of Employee Income (E.I)</option>
                                                <option value="{{ $fixedType }}">{{ ucfirst($fixedType) }}</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td x-show="payment_type === 'weekly'">
                                        <input type="number" readonly x-model="weekly_basic_salary" class="form-control" name="weekly_basic_salary" id="weeklyBasicSalary">
                                    </td>
                                    <td x-show="payment_type === 'monthly'">
                                        <input type="number" readonly x-model="monthly_basic_salary" class="form-control" name="monthly_basic_salary" id="monthlyBasicSalary">
                                    </td>
                                    <td>
                                        <input type="number" readonly class="form-control" x-model="annual_basic_salary" name="annual_basic_salary" id="annualBasicSalary">
                                    </td>
                                </tr>
                                <template x-for="(income, index) in incomes" :key="index">
                                    <tr>
                                        <td x-text="income.name"></td>
                                        <td>
                                            <div style="display: flex;">
                                                <input style="text-align:center; border:none; background: inherit;" type="text" readonly min="0" step="0.1" class="form-control" x-model="income.value_type" name="value_type">
                                                <input style="text-align:center; border:none; background: inherit;" type="number" readonly min="0" step="0.1" class="form-control" x-show="income.value_type !== 'fixed'" x-model="income.annual_component_value" name="annual_component_value">
                                            </div>
                                        </td>
                                        <td x-show="payment_type === 'weekly'">
                                            <input x-bind:style="income.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="income.value_type !== 'adjustable'" x-model="income.weekly" class="form-control" :name="income.name+'_week_value'">
                                        </td>
                                        <td x-show="payment_type === 'monthly'">
                                            <input x-bind:style="income.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="income.value_type !== 'adjustable'" x-model="income.monthly" class="form-control" :name="income.name+'_month_value'">
                                        </td>
                                        <td>
                                            <input x-bind:style="income.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="income.value_type !== 'adjustable'" class="form-control" x-model="income.annual" :name="income.name+'_annual_value'">
                                        </td>
                                    </tr>
                                </template>
                                <tr>
                                    <td>{{ __('index.fixed_allowance') }}</td>
                                    <td>{{ __('index.fixed_allowance') }}</td>
                                    <td x-show="payment_type === 'weekly'">
                                        <input style="border:none; background: inherit;" class="form-control" type="number" readonly x-model="weekly_fixed_allowance" name="weekly_fixed_allowance" id="weeklyFixedAllowance">
                                    </td>
                                    <td x-show="payment_type === 'monthly'">
                                        <input style="border:none; background: inherit;" class="form-control" type="number" readonly x-model="monthly_fixed_allowance" name="monthly_fixed_allowance" id="monthlyFixedAllowance">
                                    </td>
                                    <td>
                                        <input style="border:none; background: inherit;" class="form-control" type="number" readonly x-model="annual_fixed_allowance" name="annual_fixed_allowance" id="annualFixedAllowance">
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">{{ __('index.total') }}</th>
                                    <th x-show="payment_type === 'weekly'">
                                        <input style="border:none; background: inherit;" class="form-control" type="number" readonly x-model="weekly_total" name="weekly_total" id="weeklyTotal">
                                    </th>
                                    <th x-show="payment_type === 'monthly'">
                                        <input style="border:none; background: inherit;" class="form-control" type="number" readonly x-model="monthly_total" name="monthly_total" id="monthlyTotal">
                                    </th>
                                    <th>
                                        <input style="border:none; background: inherit;" class="form-control" type="number" readonly x-model="annual_total" name="annual_total" id="annualTotal">
                                    </th>
                                </tr>
                                <tr>
                                    <td colspan="5"> <h4>{{ __('index.deductions') }}</h4></td>
                                </tr>
                                <template x-for="(deduction, index) in deductions" :key="index">
                                    <tr>
                                        <td x-text="deduction.name"></td>
                                        <td>
                                            <div style="display: flex;">
                                                <input style="text-align:center; border:none; background: inherit;" type="text" readonly min="0" step="0.1" class="form-control" x-model="deduction.value_type" name="value_type">
                                                <input style="text-align:center; border:none; background: inherit;" type="number" readonly min="0" step="0.1" class="form-control" x-show="deduction.value_type !== 'fixed'" x-model="deduction.annual_component_value" name="annual_component_value">
                                            </div>
                                        </td>
                                        <td x-show="payment_type === 'weekly'">
                                            <input x-bind:style="deduction.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="deduction.value_type !== 'adjustable'" x-model="deduction.weekly" class="form-control" :name="deduction.name+'_week_value'">
                                        </td>
                                        <td x-show="payment_type === 'monthly'">
                                            <input x-bind:style="deduction.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="deduction.value_type !== 'adjustable'" x-model="deduction.monthly" class="form-control" :name="deduction.name+'_month_value'">
                                        </td>
                                        <td>
                                            <input x-bind:style="deduction.value_type === 'adjustable' ? 'text-align:center; border:1px solid #ccc; background: white;' : 'text-align:center; border:none; background: inherit;'" type="number" x-bind:readonly="deduction.value_type !== 'adjustable'" class="form-control" x-model="deduction.annual" :name="deduction.name+'_annual_value'">
                                        </td>
                                    </tr>
                                </template>
                                <tr>
                                    <td colspan="2">{{ __('index.total') }}</td>
                                    <td x-show="payment_type === 'weekly'">{{ $currency }} <span x-text="total_weekly_deduction"></span></td>
                                    <td x-show="payment_type === 'monthly'">{{ $currency }} <span x-text="total_monthly_deduction"></span></td>
                                    <td>{{ $currency }} <span x-text="total_annual_deduction"></span></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="2">{{ __('index.net_total') }}</th>
                                    <th x-show="payment_type === 'weekly'">{{ $currency }} <span x-text="net_weekly_salary"></span></th>
                                    <th x-show="payment_type === 'monthly'">{{ $currency }} <span x-text="net_monthly_salary"></span></th>
                                    <th>{{ $currency }} <span x-text="net_annual_salary"></span></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

            </div>
            <div class="card-footer">
                <button class="btn btn-primary submit-fn" type="submit">{{ __('index.update') }}</button>
            </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.payroll.employeeSalary.common.scripts')
    <script src="{{asset('assets/js/salary_calculation.js')}}"></script>

@endsection

