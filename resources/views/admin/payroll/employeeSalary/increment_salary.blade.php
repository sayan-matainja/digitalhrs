@extends('layouts.master')
@section('title',__('index.salary_increment'))
@section('action',__('index.increment'))
@section('button')
    <div class="float-end">
        <a href="{{route('admin.employee-salaries.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{__('index.back')}}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.employeeSalary.common.breadcrumb')

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ucfirst($employeeDetail?->name)}}: {{ __('index.salary_increment') }}</h6>
            </div>
            <div class="card-body">
                <form class="forms-sample" action="{{route('admin.employee-salaries.updated-salary-store')}}" method="POST">
                    @csrf
                    <input type="hidden" class="form-control"
                           readonly
                           required
                           name="employee_id"
                           value="{{$employeeDetail->id}}" >

                    <div class="row" x-data="calculateSalaryIncrement('{{$employeeSalary->annual_salary}}')">
                        <div class="col-lg-6 mb-3">
                            <label for="current_salary" class="form-label">{{ __('index.current_salary') }}({{\App\Helpers\AppHelper::getCompanyPaymentCurrencySymbol()}}.)<span style="color: red">*</span></label>
                            <input type="number"
                                   class="form-control"
                                   id="current_salary"
                                   name="current_salary"
                                   value="{{$employeeSalary->annual_salary}}"
                                   readonly >
                        </div>

                        <div class="col-lg-6 mb-3">
                            <label for="increment_percent" class="form-label">{{ __('index.annual_increment_percent') }}<span style="color: red">*</span></label>
                            <input type="number"
                                   class="form-control"
                                   id="increment_percent"
                                   min="0"
                                   step="0.01"
                                   required
                                   @input="calculatePercent()"
                                   name="increment_percent"
                                   x-model="increment_percent"
                                   value="{{old('increment_percent')}}" >
                        </div>

                        <div class="col-lg-6 mb-3">
                            <label for="increment_amount" class="form-label">{{ __('index.annual_increment_amount') }}<span style="color: red">*</span></label>
                            <input type="number"
                                   class="form-control"
                                   id="increment_amount"
                                   min="0"
                                   step="0.01"
                                   required
                                   @input="calculateAmount()"
                                   name="increment_amount"
                                   x-model="increment_amount"
                                   value="{{old('increment_amount')}}" >
                            <span id="increment-amount-error" style="color: red;"></span>

                        </div>

                        <div class="col-lg-6 mb-3">
                            <label for="revised_salary" class="form-label">{{ __('index.revised_salary_label') }}({{\App\Helpers\AppHelper::getCompanyPaymentCurrencySymbol()}}.)<span style="color: red">*</span></label>
                            <input type="number"
                                   class="form-control"
                                   id="revised_salary"
                                   name="revised_salary"
                                   x-model="revised_salary"
                                   value="{{old('revised_salary')}}"
                                   readonly >
                        </div>

                        {{--                        <div class="col-lg-6 mb-3">--}}
                        {{--                            <label for="fiscal_year_id " class="form-label">{{ __('index.fiscal_year_label') }}<span style="color: red">*</span></label>--}}
                        {{--                            <select class="form-select" id="fiscal_year_id" name="fiscal_year_id" required>--}}
                        {{--                                <option value="" {{isset($fiscalYearDetail) ? '': 'selected'}}  disabled>{{ __('index.select_fiscal_year') }}</option>--}}

                        {{--                                <option value="{{$fiscalYear->id}}"--}}
                        {{--                                        data-start-date="{{ \App\Helpers\AppHelper::timeLeaverequestDate($fiscalYear->start_date) }}"--}}
                        {{--                                        data-end-date="{{ \App\Helpers\AppHelper::timeLeaverequestDate($fiscalYear->end_date) }}"--}}
                        {{--                                    {{ isset($fiscalYearDetail) && ($fiscalYearDetail->year ) == $fiscalYear->id || old('year') == $fiscalYear->id ? 'selected': '' }}>--}}
                        {{--                                    {{ $fiscalYear->year }}--}}
                        {{--                                </option>--}}

                        {{--                            </select>--}}
                        {{--                        </div>--}}
                        <div class="col-lg-6 mb-3">
                            <label for="date_from" class="form-label">{{ __('index.applicable_from_label') }}</label>

                            <input type="date"
                                   class="form-control"
                                   id="startDate"
                                   name="date_from"
                                   value="{{old('date_from')}}"
                            >


                        </div>
                        {{--                        <div class="col-lg-6 mb-3">--}}
                        {{--                            <label for="date_to" class="form-label"> {{ __('index.applicable_to_label') }}</label>--}}
                        {{--                            <input type="@if($dateInBs) text @else date @endif"--}}
                        {{--                                   class="form-control"--}}
                        {{--                                   id="date_to"--}}
                        {{--                                   name="date_to"--}}
                        {{--                                   value="{{old('date_to')}}"--}}
                        {{--                            >--}}
                        {{--                        </div>--}}

                        <div class="col-lg-6 mb-3">
                            <label for="note" class="form-label">{{ __('index.remark') }}</label>
                            <textarea class="form-control"
                                      name="remark"
                                      id="tinymceExample">{{old('remark')}}</textarea>
                        </div>

                        <div class="text-md-start">
                            <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="edit-2"></i>
                                {{ __('index.submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.payroll.employeeSalary.common.scripts')

    <script>
        function calculateSalaryIncrement(annualSalary){
            return{
                increment_percent: 0,
                increment_amount: 0,
                revised_salary: 0,
                calculatePercent(){
                    this.increment_amount = (Number(this.increment_percent) /100) * Number(annualSalary);
                    this.increment_amount = this.increment_amount.toFixed(2);

                    this.revised_salary = Number(this.increment_amount) + Number(annualSalary);
                    this.revised_salary = this.revised_salary.toFixed(2);
                },
                calculateAmount(){

                    this.increment_percent = (Number(this.increment_amount) * 100) / Number(annualSalary);
                    this.increment_percent = this.increment_percent.toFixed(2);


                    this.revised_salary = Number(this.increment_amount) + Number(annualSalary);
                    this.revised_salary = this.revised_salary.toFixed(2);
                }
            }
        }


        $('#date_from').nepaliDatePicker({
            language: "english",
            dateFormat: "YYYY-MM-DD",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });



    </script>
@endsection
