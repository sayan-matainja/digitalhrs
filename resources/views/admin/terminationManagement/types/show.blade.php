@extends('layouts.master')

@section('title',__('index.training_types'))

@section('action',$terminationType->title)

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
                            <th>{{ __('index.employee') }}</th>
                            <th class="text-center">{{ __('index.notice_date') }}</th>
                            <th class="text-center">{{ __('index.termination_date') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $color = [
                            \App\Enum\TerminationStatusEnum::approved->value => 'success',
                            \App\Enum\TerminationStatusEnum::onReview->value => 'info',
                            \App\Enum\TerminationStatusEnum::pending->value => 'secondary',
                            \App\Enum\TerminationStatusEnum::cancelled->value => 'warning',
                        ];


                        ?>
                        @forelse($terminationType->terminations as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{ $value?->employee?->name }}</td>
                                <td class="text-center">{{ $value->notice_date }}</td>
                                <td class="text-center">{{ $value->termination_date }}</td>
                                <td class="text-center">

                                    <span class="btn btn-{{ $color[$value->status] }} btn-xs">
                                        {{ ucfirst($value->status) }}
                                    </span>


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


