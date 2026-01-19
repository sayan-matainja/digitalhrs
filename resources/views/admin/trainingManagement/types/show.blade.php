@extends('layouts.master')

@section('title',__('index.training_types'))

@section('action',$trainingType->title)

@section('button')
    <div class="float-end">
        <a href="{{route('admin.training-types.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.trainingManagement.types.common.breadcrumb')

        <div class="card support-main">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.department') }}</th>
                            <th>{{ __('index.employees') }}</th>
                            <th>{{ __('index.trainer') }}</th>
                            <th>{{ __('index.training_date') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                        </tr>
                        </thead>
                        <?php
                        $color = [
                            \App\Enum\TrainingStatusEnum::completed->value => 'primary',
                            \App\Enum\TrainingStatusEnum::ongoing->value => 'success',
                            \App\Enum\TrainingStatusEnum::pending->value => 'secondary',
                            \App\Enum\TrainingStatusEnum::cancelled->value => 'warning',
                        ];


                        ?>
                        <tbody>
                        @forelse($trainingType->trainings as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>
                                    <ul class="mb-0 ps-0 list-unstyled">
                                        @foreach($value->trainingDepartment as $training)
                                            <li>{{ $training?->department?->dept_name }}</li>
                                        @endforeach

                                    </ul>
                                </td>
                                <td>
                                    <ul class="mb-0 ps-0 list-unstyled">
                                        @foreach($value->employeeTraining as $training)
                                            <li>{{ $training?->employee?->name }}</li>
                                        @endforeach

                                    </ul>
                                </td>
                                <td>
                                    <ul class="mb-0 ps-0 list-unstyled">
                                        @foreach($value->trainingInstructor as $training)
                                            <li>{{ $training?->trainer?->name ?? $training?->trainer?->employee?->name }}</li>
                                        @endforeach

                                    </ul>
                                </td>
                                <td>
                                    {{\App\Helpers\AppHelper::formatDateForView($value->start_date)}}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                        {{ ucfirst($value->status) }}
                                    </button>
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
    </section>
@endsection


