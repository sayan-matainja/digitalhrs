@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title',__('index.transfer'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.transfer.index')}}">
            <button class="btn btn-sm btn-primary"><i class="link-icon"
                                                      data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.transfer.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <table class="table table-striped table-responsive">
                        <tbody>
                        <!-- Employee Name at Top -->
                        <tr>
                            <th class="w-30">{{ __('index.employee') }}</th>
                            <td colspan="2">{{ $transferDetail?->employee?->name }}</td>
                        </tr>

                        <!-- Section Header -->
                        <tr>
                            <th class="w-30">{{ __('index.transfer_details') }}</th>
                            <th>{{ __('index.from') }}</th>
                            <th>{{ __('index.to') }}</th>
                        </tr>

                        <!-- Comparison Data -->
                        <tr>
                            <th class="w-30">{{ __('index.branch') }}</th>
                            <td>{{ $transferDetail->oldBranch?->name }}</td>
                            <td>{{ $transferDetail->branch?->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.department') }}</th>
                            <td>{{ $transferDetail?->oldDepartment?->dept_name }}</td>
                            <td>{{ $transferDetail?->department?->dept_name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.post') }}</th>
                            <td>{{ $transferDetail?->oldPost?->post_name }}</td>
                            <td>{{ $transferDetail?->post?->post_name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.supervisor') }}</th>
                            <td>{{ $transferDetail?->oldSupervisor?->name }}</td>
                            <td>{{ $transferDetail?->supervisor?->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.office_time') }}</th>
                            <td>{{ $transferDetail?->oldOfficeTime?->opening_time .' - '.$transferDetail?->oldOfficeTime?->closing_time  }}</td>
                            <td>{{ $transferDetail?->officeTime?->opening_time .' - '.$transferDetail?->officeTime?->closing_time }}</td>
                        </tr>

                        <!-- Additional Information at Bottom -->
                        <tr>
                            <th class="w-30">{{ __('index.transfer_date') }}</th>
                            <td colspan="2">{{ AppHelper::formatDateForView($transferDetail->transfer_date) }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.description') }}</th>
                            <td colspan="2">{!! $transferDetail->description !!}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.created_by') }}</th>
                            <td colspan="2">{{ $transferDetail->createdBy->name ?? 'Admin' }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.updated_by') }}</th>
                            <td colspan="2">{{ $transferDetail->updatedBy->name ?? 'Admin' }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

