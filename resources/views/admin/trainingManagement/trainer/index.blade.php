@extends('layouts.master')

@section('title',__('index.trainer'))

@section('action',__('index.lists'))

@section('button')
    @can('create_trainer')
        <a href="{{ route('admin.trainers.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_trainer') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.trainingManagement.trainer.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.trainer_filter')</h6>
            </div>
            <form class="forms-sample card-body pb-0" id="filter_form" action="{{ route('admin.trainers.index') }}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  {{ !isset($filterParameters['branch_id']) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="trainer_type" name="trainer_type" >
                                <option selected  disabled>{{ __('index.select_trainer_type') }}</option>
                                @foreach($trainerTypes as $key =>  $value)
                                    <option value="{{$value->value}}" {{ isset($filterParameters['trainer_type']) && ($filterParameters['trainer_type'] ) == $value->value ? 'selected': '' }}>
                                        {{ucfirst($value->name)}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4 internalTrainer {{ isset($filterParameters['trainer_type']) && ($filterParameters['trainer_type'] == \App\Enum\TrainerTypeEnum::internal->value) ? '' : 'd-none' }}">
                            <select class="form-select" id="department_id" name="department_id">
                                <option  selected disabled>{{ __('index.select_department') }}</option>

                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4 internalTrainer {{ isset($filterParameters['trainer_type']) && ($filterParameters['trainer_type'] == \App\Enum\TrainerTypeEnum::internal->value) ? '' : 'd-none' }}">
                            <select class="form-select" id="employee_id" name="employee_id" >
                                <option  selected disabled>{{ __('index.select_employee') }}</option>

                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4 externalTrainer {{ isset($filterParameters['trainer_type']) && ($filterParameters['trainer_type'] == \App\Enum\TrainerTypeEnum::external->value) ? '' : 'd-none' }}">
                            <input type="text" class="form-control"
                                   id="name"
                                   name="name"
                                   value="{{ $filterParameters['name'] }}"
                                   autocomplete="off"
                                   placeholder="{{ __('index.name') }}">
                        </div>



                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">@lang('index.filter')</button>
                            <a class="btn btn-block btn-primary" href="{{ route('admin.trainers.index') }}">@lang('index.reset')</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.trainer_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.trainer_type') }}</th>
                            <th>{{ __('index.name') }}</th>
                            <th>{{ __('index.email') }}</th>
                            <th>{{ __('index.phone') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['show_trainer','delete_trainer','update_trainer'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($trainerLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                     <td>{{ ucfirst($value->trainer_type) }}</td>
                                     <td>{{ $value->trainer_type == \App\Enum\TrainerTypeEnum::internal->value ? $value->employee?->name : $value->name }}</td>
                                     <td>{{ $value->trainer_type == \App\Enum\TrainerTypeEnum::internal->value ? $value->employee?->email : $value->email }}</td>
                                     <td>{{ $value->trainer_type == \App\Enum\TrainerTypeEnum::internal->value ? $value->employee?->phone : $value->contact_number }}</td>
                                     <td class="text-center">
                                         <label class="switch">
                                             <input class="toggleStatus" href="{{route('admin.trainers.toggle-status',$value->id)}}"
                                                    type="checkbox" {{($value->status) == 1 ?'checked':''}}>
                                             <span class="slider round"></span>
                                         </label>
                                     </td>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_trainer')
                                                <li class="me-2">
                                                    <a href="{{route('admin.trainers.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_trainer')

                                                <li class="me-2">
                                                    <a href="javascript:void(0)"
                                                       onclick="showTrainerDetails('{{ route('admin.trainers.show',$value->id) }}')"
                                                       class="d-flex pb-1 align-items-center" title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon me-2" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_trainer')
                                                <li>
                                                    <a class="delete"
                                                       data-title="{{$value->name}} Award Detail"
                                                       data-href="{{route('admin.trainers.delete',$value->id)}}"
                                                       title="{{ __('index.delete') }}">
                                                        <i class="link-icon"  data-feather="delete"></i>
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

        <div class="dataTables_paginate mt-3">
            {{$trainerLists->appends($_GET)->links()}}
        </div>
    </section>

    @include('admin.trainingManagement.trainer.show')
@endsection

@section('scripts')
    @include('admin.trainingManagement.trainer.common.scripts', [
        'internal' => \App\Enum\TrainerTypeEnum::internal->value,
        'external' => \App\Enum\TrainerTypeEnum::external->value
    ])


    <script>
        function showTrainerDetails(url) {
            $.get(url, function (response) {
                if (response && response.data) {
                    const data = response.data;

                    // Title
                    $('.trainerTitle').html("Trainer Detail");

                    // Populate data
                    $('.name').text(data.name ?? '');
                    $('.email').text(data.email ?? '');
                    $('.phone').text(data.phone ?? '');
                    $('.address').text(data.address ?? '');
                    $('.type').text(data.type ?? '');
                    $('.branchName').text(data.branchName ?? '');

                    // Show department only if internal
                    if (data.departmentName) {
                        $('.departmentName').text(data.departmentName);
                        $('.department-row').show();
                    } else {
                        $('.department-row').hide();
                    }

                    // Show expertise only if external
                    if (data.expertise) {
                        $('.expertise').text(data.expertise);
                        $('.expertise-row').show();
                    } else {
                        $('.expertise-row').hide();
                    }

                    // Open Trainer Modal
                    const modal = new bootstrap.Modal(document.getElementById('trainerDetail'));
                    modal.show();
                }
            }).fail(function () {
                alert('Error loading trainer details.');
            });
        }
    </script>

@endsection

