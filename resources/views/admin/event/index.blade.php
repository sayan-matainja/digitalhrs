@php use App\Enum\EventStatusEnum;use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title',__('index.event'))


@section('action',$isBsEnabled ? __('index.list') : __('index.event_calendar'))
@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.css" rel="stylesheet"/>
    <style>
        .fc-event {
            cursor: pointer;
        }
    </style>
@endsection
@section('button')
    @can('create_event')
        <a href="{{ route('admin.event.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{__('index.create_event')}}
            </button>
        </a>
    @endcan
@endsection


@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.event.common.breadcrumb')


        @if($isBsEnabled)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">@lang('index.event_filter')</h6>
                </div>
                <form class="forms-sample card-body pb-0" id="filter_form" action="{{ route('admin.event.index') }}" method="get">

                    <div class="row align-items-center">
                        @if(!isset(auth()->user()->branch_id))
                            <div class="col-lg-3 col-md-6 mb-4">
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option selected disabled>{{ __('index.select_branch') }}
                                    </option>
                                    @if(isset($companyDetail))
                                        @foreach($companyDetail->branches()->get() as $key => $branch)
                                            <option value="{{$branch->id}}"
                                                {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id']  == $branch->id) ? 'selected': '' }}>
                                                {{ucfirst($branch->name)}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @endif


                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" multiple name="department_id[]" id="department_id">
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" multiple name="employee_id[]" id="employee_id">
                            </select>
                        </div>


                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text" id="nepali-datepicker-from"
                                   name="start_date"
                                   value="{{ $filterParameters['start_date'] ?? '' }}"
                                   placeholder="mm/dd/yyyy"
                                   class="form-control nepali_date"/>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text" id="nepali-datepicker-from"
                                   name="end_date"
                                   value="{{ $filterParameters['end_date'] ?? '' }}"
                                   placeholder="mm/dd/yyyy"
                                   class="form-control nepali_date"/>
                        </div>


                        <div class="col-lg-2 col-md-6 mb-4">
                            <div class="d-flex">
                                <button type="submit"
                                        class="btn btn-block btn-success me-2">@lang('index.filter')</button>
                                <a class="btn btn-block btn-primary"
                                   href="{{ route('admin.event.index') }}">@lang('index.reset')</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card support-main">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('index.event_list') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('index.title') }}</th>
                                <th>{{ __('index.host') }}</th>
                                <th>{{ __('index.location') }}</th>
                                <th>{{ __('index.date') }}</th>
                                <th class="text-center">{{ __('index.status') }}</th>
                                @canany(['show_event','delete_event','update_event'])
                                    <th class="text-center">{{ __('index.action') }}</th>
                                @endcanany
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                $color = [
                                    EventStatusEnum::completed->value => 'primary',
                                    EventStatusEnum::ongoing->value => 'success',
                                    EventStatusEnum::pending->value => 'secondary',
                                    EventStatusEnum::cancelled->value => 'warning',
                                ];

                                ?>
                            @forelse($events as $key => $value)

                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{ $value->title }}</td>
                                    <td>{{ $value->host ?? __('index.not_available') }}</td>
                                    <td>{{ $value->location }}</td>
                                    <td>
                                        @if(is_null($value->end_date) || strtotime($value->start_date) == strtotime($value->end_date))

                                            {{ AppHelper::formatDateForView($value->start_date) }}
                                        @else
                                            {{ AppHelper::formatDateForView($value->start_date) }}
                                            - {{ AppHelper::formatDateForView($value->end_date) }}
                                        @endif

                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                            {{ ucfirst($value->status) }}
                                        </button>
                                    </td>

                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('edit_event')
                                                @if($value->status !== EventStatusEnum::completed->value)
                                                    <li class="me-2">
                                                        <a href="{{route('admin.event.edit',$value->id)}}"
                                                           title="{{__('index.edit_event_detail')}} "
                                                           class="d-flex pb-1 align-items-center">
                                                            <i class="link-icon" data-feather="edit"></i>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endcan

                                            @can('show_event')
                                                <li class="me-2">
                                                    <a href="javascript:void(0)"
                                                       onclick="showEventDetails('{{ route('admin.event.show',$value->id) }}')"
                                                       class="d-flex pb-1 align-items-center"
                                                       title="{{ __('index.show_event') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_event')
                                                <li>
                                                    <a
                                                        data-href="{{route('admin.event.delete',$value->id)}}"
                                                        title="{{__('index.delete_event')}}"
                                                        class="d-flex align-items-center delete">
                                                        <i class="link-icon" data-feather="delete"></i>
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
                {{ $events->appends($_GET)->links() }}
            </div>
        @else
            <div class="row">
                <div class="col-md-8">
                    <div id="calendar"></div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="pill" href="#isUpcoming">Upcoming
                                        Events</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="pill" href="#isPast">Past Events</a>
                                </li>
                            </ul>

                            <div class="tab-content mt-4">
                                <div id="isUpcoming" class="tab-pane active">
                                    <div class="event-group-item p-0">
                                        @foreach($upcomingEvents as $event)
                                            <div class="event-list border-bottom pb-3 mb-3">
                                                <div class="d-flex justify-content-between w-100 mb-2">
                                                    <h5>
                                                        <a href="javascript:void(0)" onclick="showEventDetails('{{ route('admin.event.show',$event->id) }}')" style="color: {{ $event->background_color ?? 'inherit' }}">
                                                            {{ $event->title }}
                                                        </a>
                                                    </h5>

                                                    <div class="btn-group card-option">
                                                        <button type="button" class="btn p-0" data-bs-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                            <i class="link-icon" data-feather="more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end p-2" style="">

                                                            @can('edit_event')
                                                                <a href="{{route('admin.event.edit',$event->id)}}"
                                                                   title="{{__('index.edit_event_detail')}} "
                                                                   class="d-flex pb-1 align-items-center">
                                                                    <i class="link-icon me-2" data-feather="edit"></i>
                                                                    Edit
                                                                </a>
                                                            @endcan

                                                            @can('show_event')
                                                                <a href="javascript:void(0)"
                                                                   onclick="showEventDetails('{{ route('admin.event.show',$event->id) }}')"
                                                                   class="d-flex pb-1 align-items-center">
                                                                    <i class="link-icon me-2" data-feather="eye"></i>
                                                                    View
                                                                </a>
                                                            @endcan

                                                            @can('delete_event')
                                                                <a
                                                                    data-href="{{route('admin.event.delete',$event->id)}}"
                                                                    title="{{__('index.delete_event')}}"
                                                                    class="d-flex align-items-center delete">
                                                                    <i class="link-icon me-2" data-feather="delete"></i>
                                                                    Delete
                                                                </a>
                                                            @endcan

                                                        </div>
                                                    </div>
                                                </div>

                                                <p>

                                                    @if(is_null($event->end_date) || strtotime($event->start_date) == strtotime($event->end_date))

                                                        Date: {{ AppHelper::formatDateForView($event->start_date) }}
                                                    @else

                                                        Date
                                                        : {{ AppHelper::formatDateForView($event->start_date) }}
                                                        - {{ AppHelper::formatDateForView($event->end_date) }}
                                                    @endif
                                                    <br>
                                                    Time
                                                    : {{ AppHelper::convertLeaveTimeFormat($event->start_time) }}
                                                    - {{ AppHelper::convertLeaveTimeFormat($event->end_time) }}


                                                </p>


                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                                <div id="isPast" class="tab-pane fade">
                                    <div class="event-group-item p-0">
                                        @foreach($pastEvents as $event)
                                            <div class="event-list border-bottom pb-3 mb-3">
                                                <div class="d-flex justify-content-between w-100 mb-2">
                                                    <h5>
                                                        <a href="javascript:void(0)" onclick="showEventDetails('{{ route('admin.event.show',$event->id) }}')" style="color: {{ $event->background_color ?? 'inherit' }}">
                                                            {{ $event->title }}
                                                        </a>
                                                    </h5>

                                                    <div class="btn-group card-option">
                                                        <button type="button" class="btn p-0" data-bs-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                            <i class="link-icon" data-feather="more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end p-2" style="">

                                                            @can('edit_event')
                                                                <a href="{{route('admin.event.edit',$event->id)}}"
                                                                   title="{{__('index.edit_event_detail')}} "
                                                                   class="d-flex pb-1 align-items-center">
                                                                    <i class="link-icon me-2" data-feather="edit"></i>
                                                                    Edit
                                                                </a>
                                                            @endcan

                                                            @can('show_event')
                                                                <a href="javascript:void(0)"
                                                                   onclick="showEventDetails('{{ route('admin.event.show',$event->id) }}')"
                                                                   class="d-flex pb-1 align-items-center">
                                                                    <i class="link-icon me-2" data-feather="eye"></i>
                                                                    View
                                                                </a>
                                                            @endcan

                                                            @can('delete_event')
                                                                <a
                                                                    data-href="{{route('admin.event.delete',$event->id)}}"
                                                                    title="{{__('index.delete_event')}}"
                                                                    class="d-flex align-items-center delete">
                                                                    <i class="link-icon me-2" data-feather="delete"></i>
                                                                    Delete
                                                                </a>
                                                            @endcan

                                                        </div>
                                                    </div>
                                                </div>

                                                <p>

                                                    @if(is_null($event->end_date) || strtotime($event->start_date) == strtotime($event->end_date))

                                                        Date: {{ AppHelper::formatDateForView($event->start_date) }}
                                                        <br>
                                                        Time: {{ AppHelper::convertLeaveTimeFormat($event->start_time) }}
                                                        - {{ AppHelper::convertLeaveTimeFormat($event->end_time) }}
                                                    @else

                                                        Date
                                                        : {{ AppHelper::formatDateForView($event->start_date) }}
                                                        - {{ AppHelper::formatDateForView($event->end_date) }}
                                                        <br>
                                                        Time
                                                        : {{ AppHelper::convertLeaveTimeFormat($event->start_time) }}
                                                        | {{ AppHelper::convertLeaveTimeFormat($event->end_time) }}
                                                    @endif

                                                </p>


                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        @endif

    </section>

    @include('admin.event.show')
@endsection

@section('scripts')
    @include('admin.event.common.scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let calendarEl = document.getElementById('calendar');
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                slotMinTime: '8:00:00',
                slotMaxTime: '19:00:00',
                events: @json($events),
                eventDisplay: 'block',
                eventColor: function (eventInfo) {
                    return eventInfo.event.extendedProps.color || eventInfo.event.color;
                },
                eventClick: function (info) {
                    const eventId = info.event.id;
                    const url = "{{ route('admin.event.show', ':id') }}".replace(':id', eventId);

                    showEventDetails(url);
                }
            });
            calendar.render();
        });


        function showEventDetails(url) {
            $.get(url, function (response) {
                if (response && response.data) {
                    const data = response.data;
                    let time = data.start_time + ' - ' + data.end_time;
                    let date = data.end_date !== '' ? data.start_date + ' - ' + data.end_date : data.start_date;
                    $('.meetingTitle').html('Event Detail');
                    $('.title').text(data.title);
                    $('.start_date').text(date);
                    $('.end_date').text(time);
                    $('.venue').text(data.location);
                    $('.description').text(data.description);
                    $('.creator').text(data.creator);
                    $('.host').text(data.host);

                    if (data.attachment) {
                        $('.image').attr('src', data.attachment).show();
                    } else {
                        $('.image').hide();
                    }

                    const modal = new bootstrap.Modal(document.getElementById('eventDetail'));
                    modal.show();
                }
            }).fail(function (xhr, status, error) {
                // Handle error
                alert('Error loading event details. Please try again.');
                console.error('Error:', error);
            });
        }

    </script>
@endsection






