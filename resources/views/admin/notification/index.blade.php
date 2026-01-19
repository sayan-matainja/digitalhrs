@extends('layouts.master')
@section('title',__('index.notifications'))
@section('action',__('index.lists'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.notification.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.notification_lists')</h6>
            </div>
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{route('admin.notifications.index')}}" method="get">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-8 mb-4">
                            <select class="form-select" name="type" id="type">
                                <option value="" {{!isset($filterParameters['type']) ? 'selected': ''}}   >@lang('index.all_types')</option>
                                @foreach(\App\Models\Notification::TYPES as  $value)
                                    <option value="{{$value}}" {{ (isset($filterParameters['type']) && $value == $filterParameters['type'] ) ?'selected':'' }} >
                                        {{ucfirst($value)}} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 mb-4">
                            <button type="submit" class="btn btn-block btn-primary">@lang('index.filter')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.title')</th>
                            <th>@lang('index.published_date')</th>
                            <th class="text-center">@lang('index.type')</th>

                            @can('notification')
                                <th class="text-center">@lang('index.description')</th>
                            @endcan

                            <th class="text-center">@lang('index.status')</th>

                            @can('notification')
                                <th class="text-center">@lang('index.action')</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        @forelse($notifications as $key => $value)
                            <tr>
                                <td>{{(($notifications->currentPage()- 1 ) * (\App\Models\Notification::RECORDS_PER_PAGE) + (++$key))}}</td>
                                <td>{{removeSpecialChars($value->title)}}</td>
                                <td>{{  convertDateTimeFormat($value->notification_publish_date) ?? 'Not published yet'}}</td>
                                <td class="text-center">{{  ucfirst($value->type)}}</td>

                                @can('notification')
                                    <td class="text-center">
                                        <a href=""
                                           id="showNotificationDescription"
                                           data-href="{{route('admin.notifications.show',$value->id)}}"
                                           data-id="{{ $value->id }}" title="@lang('index.show_detail')">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                @endcan

                                <td class="text-center">
                                    <label class="switch mb-0">
                                        <input class="toggleStatus" href="{{route('admin.notifications.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @can('notification')
                                    <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">

                                            @if($value->type == 'general')
                                                <li class="me-2">
                                                    <a href="{{route('admin.notifications.edit',$value->id)}}" title="@lang('index.edit') ">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endif

                                            <li class="me-2">
                                                <a class="deleteNotification"
                                                   data-href="{{route('admin.notifications.delete',$value->id)}}" title="@lang('index.delete')">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>


{{--                                        @can('send_notification')--}}
{{--                                            @if($value->type == 'general')--}}
{{--                                                <li >--}}
{{--                                                    <a class="sendNotification"--}}
{{--                                                       data-href="{{route('admin.notifications.send-notification',$value->id)}}" title="send notification">--}}
{{--                                                        <button class="btn btn-primary btn-xs">send notification</button>--}}
{{--                                                    </a>--}}
{{--                                                </li>--}}
{{--                                            @endif--}}
{{--                                        @endcan--}}

                                    </ul>
                                </td>
                                @endcan


                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>@lang('index.no_records_found')</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            {{$notifications->appends($_GET)->links()}}
        </div>
    </section>

    @include('admin.notification.show')
@endsection

@section('scripts')

    @include('admin.notification.common.scripts')
@endsection






