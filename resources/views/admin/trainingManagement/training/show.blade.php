@extends('layouts.master')

@section('title',__('index.training'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.training.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.trainingManagement.training.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <table class="table table-striped table-responsive">
                        <tbody>

                        <tr>
                            <th class="w-30">{{ __('index.training_for') }}</th>
                            <td>{{ $trainingDetail->trainingType?->title }}</td>
                        </tr>


                        <tr>
                            <th class="w-30">{{ __('index.branch') }}</th>
                            <td>{{ $trainingDetail->branch?->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.department') }}</th>
                            <td>
                                <ul class="mb-0 ps-0 list-unstyled">
                                    @forelse($trainingDetail->trainingDepartment as $detail)
                                        <li>{{ $detail?->department?->dept_name }}</li>
                                    @empty
                                    @endforelse
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.employee_name') }}</th>
                            <td>
                                <ul class="mb-0 ps-0 list-unstyled">
                                    @forelse($trainingDetail->employeeTraining as $detail)
                                        <li>{{ $detail?->employee?->name }}</li>
                                    @empty
                                    @endforelse
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.date') }}</th>
                            <td>
                                {{ isset($trainingDetail->end_date)
                                    ? \App\Helpers\AppHelper::formatDateForView($trainingDetail->start_date) . ' - ' . \App\Helpers\AppHelper::formatDateForView($trainingDetail->end_date)
                                    : \App\Helpers\AppHelper::formatDateForView($trainingDetail->start_date) }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.time') }}</th>
                            <td>
                                {{ \App\Helpers\AppHelper::convertLeaveTimeFormat($trainingDetail->start_time) . ' - ' . \App\Helpers\AppHelper::convertLeaveTimeFormat($trainingDetail->end_time) }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.cost') }}</th>
                            <td>
                                {{ $trainingDetail->cost }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.venue') }}</th>
                            <td>
                                {{ $trainingDetail->venue }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.description') }}</th>
                            <td>
                                {!! $trainingDetail->description !!}
                            </td>
                        </tr>

                        <tr>
                            <th class="w-30">{{ __('index.certificate') }}</th>
                            <td>
                                <img id="image-preview" class="rounded"
                                     src="{{ asset(\App\Models\Training::UPLOAD_PATH . $trainingDetail->certificate) }}"
                                     style="object-fit: contain; height:100px; width:auto;">
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.created_by') }}</th>
                            <td>{{ $trainingDetail->createdBy->name ?? 'Admin' }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.updated_by') }}</th>
                            <td>{{ $trainingDetail->updatedBy->name ?? 'Admin' }}</td>
                        </tr>
                        <tr>
                            <th> {{ __('index.trainer') }}</th>
                            <td>
                                <ul class="mb-0 ps-0 list-unstyled">
                                    @forelse($trainingDetail->trainingInstructor as $instructor)
                                        <li>{{ $instructor->trainer->name ??  $instructor?->trainer?->employee?->name }} ({{ $instructor?->trainer?->trainer_type }})</li>
                                    @empty
                                    @endforelse
                                </ul>
                            </td>


                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.trainingManagement.training.common.scripts')
@endsection

