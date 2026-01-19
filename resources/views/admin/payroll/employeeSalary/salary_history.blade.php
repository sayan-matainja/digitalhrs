@extends('layouts.master')

@section('title',__('index.salary_increment'))

@section('action',__('index.log'))

@section('button')
    <div class="float-end">
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
                <h6 class="card-title mb-0">{{ucfirst($employeeDetail->name)}} {{ __('index.salary_increment_log') }}</h6>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">{{ __('index.salary_revised_on') }}</th>
                            <th class="text-center">{{ __('index.increment_by') }}</th>
                            <th class="text-center">{{ __('index.increment_value') }} ({{ $currency }})</th>
                            <th class="text-center">{{ __('index.new_salary') }} ({{$currency}})</th>
                            <th class="text-center">{{ __('index.old_salary') }} ({{$currency}})</th>
                            <th class="text-center">{{ __('index.applicable_from') }}</th>
                            <th class="text-center">{{ __('index.remark') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($salaryReviseLists as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td class="text-center">{{ \App\Helpers\AppHelper::timeLeaverequestDate($value->salary_revised_on) }}</td>
                                <td class="text-center">{{ $value->increment_percent.'%' }}</td>
                                <td class="text-center">{{ $value->increment_amount }}</td>
                                <td class="text-center">{{ number_format($value->revised_salary) }}</td>
                                <td class="text-center">{{ number_format($value->base_salary) }}</td>
                                <td class="text-center">{{ \App\Helpers\AppHelper::timeLeaverequestDate($value->date_from) }}</td>
                                <td class="text-center">
                                    <a href="#"
                                       id="showRemark"
                                       data-remark="{{$value->remark}} ">
                                        <i class="link-icon" data-feather="eye"></i>
                                    </a>
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

        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                    </div>
                    <div class="modal-body">
                        <strong>{{ __('index.remark') }}:</strong> <p class="remark"> </p>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    <script>
        $('body').on('click','#showRemark', function (e) {
            e.preventDefault();
            let remark = $(this).data('remark');
            $('.modal-title').html(`{{__('index.salary_increment_remark')}}`);
            $('.remark').text(remark);
            $('#addslider').modal('show');
        }).trigger("change");
    </script>
@endsection







