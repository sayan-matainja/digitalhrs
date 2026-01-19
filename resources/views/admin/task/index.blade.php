@php use App\Helpers\PMHelper; @endphp
@php use App\Models\Task; @endphp
@php use Illuminate\Support\Str; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')
@section('title',__('index.tasks'))
@section('action',__('index.lists'))

@section('button')
    @can('create_task')
        <a href="{{ route('admin.tasks.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>@lang('index.create_tasks')
            </button>
        </a>
    @endcan
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.task.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.task_filter')</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{route('admin.tasks.index')}}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <!-- <label for="branchFilter" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label> -->
                            <select class="form-select" id="branch_id" name="branch_id">

                                <option selected disabled>{{ __('index.select_branch') }}</option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{  $filterParameters['branch_id'] == $branch->id  ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="col-md-12 form-select" id="project" name="project_id">
                            <option value="" {{!isset($filterParameters['project_id']) ? 'selected':''}}></option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" id="taskName" name="task_id">
                            <option
                                value="" {{!isset($filterParameters['task_id']) ? 'selected':'' }}>@lang('index.search_by_task_name')</option>

                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" id="status" name="status">
                            <option value="">@lang('index.search_by_status')</option>
                            @foreach(Task::STATUS as $value)
                                <option value="{{$value}}" {{$filterParameters['status'] == $value ? 'selected':''}}>
                                    {{(PMHelper::STATUS[$value])}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" id="priority" name="priority">
                            <option value="">@lang('index.search_by_priority')</option>
                            @foreach(Task::PRIORITY as $value)
                                <option
                                    value="{{$value}}" {{$filterParameters['priority'] == $value ? 'selected':''}}> {{ucfirst($value)}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" id="taskMember" name="assigned_member[]" multiple>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <button type="submit" class="btn btn-block btn-success me-2">@lang('index.filter')</button>

                        <a class="btn btn-block btn-danger"
                           href="{{route('admin.tasks.index')}}">@lang('index.reset')</a>
                    </div>
                </div>
            </form>
        </div>


            <?php
            $status = [
                'in_progress' => 'primary',
                'not_started' => 'primary',
                'on_hold' => 'info',
                'cancelled' => 'danger',
                'completed' => 'success',
                'expired'=>'warning'
            ]
            ?>
        <div class="project-card">
            <div class="row">
                @forelse($tasks as $key => $value)
                    <div class="col-xxl-3 col-xl-4 d-flex mb-4">
                        <div class="card p-4 w-100">
                            <div class="title-section d-flex align-items-center justify-content-between mb-2">
                                <div class="title-section-inner d-flex align-items-center justify-content-between">
                                    <div class="title-section-heading">
                                        <h5 class="mb-1">
                                            <a href="{{route('admin.tasks.show',$value->id)}}">
                                                {{ ucfirst(Str::limit($value->name, 40, $end='...')) }}
                                            </a>
                                        </h5>
                                        <p class="small">
                                            <b>@lang('index.project'):</b>
                                            <a href="{{route('admin.projects.show',$value->project_id)}}"
                                               class="text-muted">{{ucfirst($value?->project?->name)}}</a>
                                        </p>
                                    </div>

                                </div>

                                @canany(['edit_task','show_task_detail','delete_task'])
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle p-0" data-bs-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                            <i class="link-icon" data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" style="">

                                            @can('edit_task')
                                                <a href="{{route('admin.tasks.edit',$value->id)}}" class="d-block py-1">
                                                    <i class="link-icon me-2"
                                                       data-feather="edit"></i> @lang('index.edit')
                                                </a>
                                            @endcan

                                            @can('show_task_detail')
                                                <a href="{{route('admin.tasks.show',$value->id)}}" class="d-block py-1">
                                                    <i class="link-icon me-2"
                                                       data-feather="eye"></i> @lang('index.view')
                                                </a>
                                            @endcan

                                            @can('delete_task')
                                                <a data-href="{{route('admin.tasks.delete',$value->id)}}"
                                                   class="delete d-block py-1">
                                                    <i class="link-icon me-2"
                                                       data-feather="delete"></i> @lang('index.delete')
                                                </a>
                                            @endcan

                                        </div>
                                    </div>
                                @endcanany
                            </div>
                            <div class="badge-section mb-2">
                               <span class="badge bg-{{ $value->taskRemainingDaysToComplete() > 3 ? 'success' : ((($value->taskRemainingDaysToComplete() > 0) && ($value->taskRemainingDaysToComplete() <=3)) ? 'warning text-dark' : 'danger') }} text-end d-inline-block float-end">
                                    {{ $value->taskRemainingDaysToComplete() }}/{{ $value->taskDuration() > 0 ? $value->taskDuration() : 0 }} @lang('index.days')
                                </span>
                            </div>

                            <div class="progress mb-2">
                                <div class="progress-bar color2 rounded"
                                     role="progressbar"
                                     style="{{ AppHelper::getProgressBarStyle(($value->taskChecklists->count() > 0) ? $value->getTaskProgressInPercentage(): (($value->status === 'completed') ? 100 : 0)) }}"
                                     aria-valuenow="25"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <span>{{( (($value->taskChecklists->count() > 0) && ($value->status === 'completed')) ? $value->getTaskProgressInPercentage() : (($value->status === 'completed') ? 100 : 0))}} %</span>
                                </div>
                            </div>

                            <div class="date-section d-flex justify-content-between align-items-center">
                                <div class="date-item">
                                    <p class="text-success"><i class="link-icon" data-feather="calendar"></i>
                                        {{AppHelper::formatDateForView($value->start_date)}} -
                                        <span
                                            class="text-danger">{{AppHelper::formatDateForView($value->end_date)}} </span>
                                    </p>
                                </div>


                                <div class="member-listed w-25 float-end text-end">
                                    <label class="switch">

                                        <input class="toggleStatus"
                                               href="{{route('admin.tasks.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}  {{ ($value->status == 'expired' || $value->taskRemainingDaysToComplete() <= 0) ? 'disabled' : ''}}>
                                        <span class="slider round"></span>

                                    </label>
                                </div>

                            </div>

                        </div>
                    </div>
                @empty

                @endforelse
            </div>
        </div>

        <div class="row">
            <div class="dataTables_paginate">
                {{$tasks->appends($_GET)->links()}}
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    @include('admin.task.common.scripts')

@endsection






