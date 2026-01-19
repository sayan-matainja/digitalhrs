@php use App\Helpers\AttendanceHelper; @endphp
@php use App\Models\Client; @endphp
@php use App\Models\User; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title',__('index.digital_hr_dashboard'))

<?php
$attendanceDetail = (AppHelper::employeeTodayAttendanceDetail());

$multipleEntries = count($attendanceDetail);
$firstAttendance = $attendanceDetail->first();
$lastAttendance = $attendanceDetail->last();

$checkInAt = $firstAttendance['check_in_at'] ?? '';
$checkOutAt = $lastAttendance['check_out_at'] ?? '';
$attendanceDate = $lastAttendance['attendance_date'] ?? '';
$viewCheckIn = $checkInAt ? AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $checkInAt) : '-:-:-';
$viewCheckOut = $checkOutAt ? AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $checkOutAt) : '-:-:-';
?>

@section('nav-head',__('index.welcome').' : ' .ucfirst($dashboardDetail?->company_name) )

@section('styles')
    <style>
        #clockContainer {
            background: url({{asset('assets/images/clock.png') }}) no-repeat;
            background-size: 100%;
        }

        .alert {
            display: flex;
            align-items: center;
        }

        .scrolling-message {
            display: inline-block;
            white-space: nowrap;
            position: absolute;
            animation: scroll-left 10s linear infinite;
        }

        @keyframes scroll-left {
            0% {
                transform: translateX(100%);
            }
            100% {
                transform: translateX(-100%);
            }
        }
    </style>
@endsection

@section('main-content')

    <section class="content">
        <?php
        $projectPriority = [
            'low' => 'info',
            'medium' => 'warning',
            'high' => 'primary',
            'urgent' => 'primary'
        ];
        ?>

        <div id="flashAttendanceMessage" class="d-none">
            <div class="alert alert-danger errorStartWorking">
                <p class="errorStartWorkingMessage"></p>
            </div>

            <div class="alert alert-danger errorStopWorking">
                <p class="errorStopWorkingMessage"></p>
            </div>

            <div class="alert alert-success successStartWorking">
                <p class="successStartWorkingMessage"></p>
            </div>

            <div class="alert alert-success successStopWorking">
                <p class="successStopWorkingMessage"></p>
            </div>
        </div>

        <div id="loader" style="display:none;">
            <div class="loading">
                <div class="loading-content"></div>
            </div>
        </div>

        <div class="row">
            @can('attendance_summary')
                <div class=" {{ isset(auth()->user()->id) ? 'col-xxl-9 col-xl-8': 'col-xxl-12 col-xl-12'  }} d-flex">
                    <div class="row">
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_departments') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_departments)}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="layers"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_employees') }}</h6>
                                    </div>

                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_employee)}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="users"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_holidays') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_holidays) ?? 0}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="umbrella"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.paid_leaves') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_paid_leaves) ?? 0}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="file-text"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.on_leave_today') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_on_leave) ?? 0}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="file-minus"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.pending_leave_requests') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{ number_format($dashboardDetail?->total_pending_leave_requests) ?? 0}}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="twitch"> </i>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_check_in_today') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-flex">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_checked_in_employee) ?? 0 }}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="log-in"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-4 mb-4 d-flex">
                            <div class="card w-100">
                                <div class="card-body text-md-start text-center">
                                    <div class="d-md-flex justify-content-between align-items-baseline mb-3">
                                        <h6 class="card-title mb-2 mb-md-0">{{ __('index.total_check_out_today') }}</h6>
                                    </div>
                                    <div class="row align-items-center d-md-fle">
                                        <div class="col-lg-6 col-md-6">
                                            <h3>{{number_format($dashboardDetail?->total_checked_out_employee) ?? 0 }}</h3>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                            <i class="link-icon" data-feather="log-out"> </i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endcan
            @if(auth()->user())
                @can('allow_attendance')
                    <div class="col-xxl-3 col-xl-4 mb-4 d-flex">
                        <div class="card w-100">
                            <div class="card-body text-center clock-display">
                                <div id="clockContainer" class="mb-2">
                                    <div id="hour"></div>
                                    <div id="minute"></div>
                                    <div id="second"></div>
                                </div>

                                <p id="date"
                                   class="text-primary fw-bolder mb-2"> {{ AppHelper::getCurrentDate() }}</p>

                                <div class="punch-btn mb-2 d-flex align-items-center justify-content-around">
                                    @if($multipleAttendance > 1)
                                        @if($multipleEntries < $multipleAttendance || ($lastAttendance->check_in_at && !$lastAttendance->check_out_at))

                                            @if((!isset($firstAttendance->check_in_at) && !isset($firstAttendance->check_out_at)) || ($lastAttendance->check_in_at && $lastAttendance->check_out_at))
                                                <button href="{{route('admin.dashboard.takeAttendance','checkIn')}}"
                                                        class="btn btn-lg btn-danger "
                                                        id="startWorkingBtn"
                                                        data-audio="{{asset('assets/audio/beep.mp3')}}"
                                                >
                                                    {{ __('index.punch_in') }}
                                                </button>

                                            @elseif(($firstAttendance->check_in_at && !$firstAttendance->check_out_at) || ($lastAttendancess->check_in_at && !$lastAttendance->check_out_at))
                                                <button href="{{route('admin.dashboard.takeAttendance','checkOut')}}"
                                                        class="btn btn-lg btn-danger"
                                                        id="stopWorkingBtn"
                                                        data-audio="{{asset('assets/audio/beep.mp3')}}"
                                                >
                                                    {{ __('index.punch_out') }}
                                                </button>
                                            @endif
                                        @endif
                                    @else
                                        <button href="{{route('admin.dashboard.takeAttendance','checkIn')}}"
                                                class="btn btn-lg btn-danger  {{ $checkInAt ? 'd-none' : ''}}"
                                                id="startWorkingBtn" data-audio="{{asset('assets/audio/beep.mp3')}}"
                                        >
                                            {{ __('index.punch_in') }}
                                        </button>
                                        <button href="{{route('admin.dashboard.takeAttendance','checkOut')}}"
                                                class="btn btn-lg btn-danger {{ $checkOutAt ? 'd-none' : ''}}"
                                                id="stopWorkingBtn" data-audio="{{asset('assets/audio/beep.mp3')}}"
                                        >
                                            {{ __('index.punch_out') }}
                                        </button>
                                    @endif
                                </div>

                                <div class="check-text d-flex align-items-center justify-content-around">
                                    <span>{{ __('index.check_in_at') }}<p class="text-success fw-bold h5"
                                                                          id="checkInTime">{{$viewCheckIn}} </p></span>
                                    <span>{{ __('index.check_out_at') }}<p class="text-danger fw-bold h5"
                                                                           id="checkOutTime">{{$viewCheckOut}}  </p></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
            @endif

        </div>
        @canany(['project_detail','client_detail'])
            @can('project_detail')
                <div class="projectManagement">
                    <h4 class="mb-4">{{ __('index.project_management') }} </h4>
                    <div class="row">
                        <div class="col-xxl-6 col-xl-6 d-flex mb-4">
                            <div class="card card-table flex-fill">
                                <div class="card-header">
                                    <h3 class="card-title mb-0">{{ __('index.projects_detail') }}</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="projectChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-6 col-xl-6 d-flex">
                            <div class="row">
                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.total_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['total_projects'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.pending_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['not_started'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.on_hold_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['on_hold'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.in_progress_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['in_progress'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.finished_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['completed'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-4 col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body text-md-start text-center">
                                            <h6 class="card-title mb-2">{{ __('index.cancelled_projects') }}</h6>
                                            <div class="row align-items-center d-md-flex">
                                                <div class="col-lg-6 col-md-6">
                                                    <h3>{{number_format($projectCardDetail['cancelled'])}}</h3>
                                                </div>
                                                <div class="col-lg-6 col-md-6 text-md-end dash-icon mt-md-0 mt-2">
                                                    <i class="link-icon" data-feather="layers"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <div class="row">
                @can('client_detail')
                    <div class="col-xxl-8 col-xl-8 mb-4 d-flex">
                        <div class="card card-table flex-fill">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">{{ __('index.top_clients') }}</h3>
                                <a href="{{route('admin.clients.index')}}">{{ __('index.view_all_clients') }}</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table custom-table mb-0">
                                        <thead>
                                        <tr>
                                            <th>{{ __('index.name') }}</th>
                                            <th class="text-center">{{ __('index.email') }}</th>
                                            <th class="text-center">{{ __('index.contact') }}</th>
                                            <th class="text-center">{{ __('index.project') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($topClients as $key => $client)
                                            <tr>
                                                <td class="table-avatar w-35">

                                                    <a href="{{route('admin.clients.show',$client->id)}}"
                                                       class="avatar">
                                                        <img alt=""
                                                             src="{{asset(Client::UPLOAD_PATH.$client->avatar)}}">
                                                        <span class="ms-1">{{ucfirst($client->name)}}</span>
                                                    </a>

                                                </td>
                                                <td class="text-center">{{$client->email}}</td>
                                                <td class="text-center">
                                                    {{$client->contact_no}}
                                                </td>

                                                <td class="text-center">
                                                    {{$client->project_count}}
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
                    </div>
                @endcan

                @can('project_detail')
                    <div class="col-xxl-4 col-xl-4 mb-4 d-flex">
                        <div class="card card-table flex-fill">
                            <div class="card-header text-center">
                                <h3 class="card-title mb-0">{{ __('index.task_details') }}</h3>
                            </div>
                            <div class="card-body text-center">
                                <canvas id="tasksChart"></canvas>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>

            @can('project_detail')
                <div class="card card-table flex-fill">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">{{ __('index.recent_projects') }}</h3>
                        <a href="{{route('admin.projects.index')}}">{{ __('index.view_all_projects') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table custom-table mb-0">
                                <thead>
                                <tr>
                                    <th class="w-25">{{ __('index.title') }}</th>
                                    <th class="text-center">{{ __('index.date_start') }}</th>
                                    <th class="text-center">{{ __('index.deadline') }}</th>
                                    <th class="text-center">{{ __('index.leader') }}</th>
                                    <th class="text-center">{{ __('index.completion') }}</th>
                                    <th class="text-center">{{ __('index.priority') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($recentProjects as $key => $project)
                                    <tr>
                                        <td class="w-25">
                                            <a href="{{route('admin.projects.show',$project->id)}}">{{ucfirst($project->name)}} </a>
                                        </td>
                                        <td class="text-center">{{AppHelper::formatDateForView($project->start_date)}}</td>
                                        <td class="text-center">
                                            {{AppHelper::formatDateForView($project->deadline)}}
                                        </td>

                                        <td class="member-listed text-center">
                                            @forelse($project->projectLeaders as $key => $leader)

                                                <button type="button" class="p-0 border-0 bg-transparent ms-n3 "
                                                        disabled data-toggle="tooltip" data-placement="top"
                                                        title="{{ $leader->user ? ucfirst($leader->user->name) : 'Project Leader' }}">
                                                    <img class="rounded-circle" style="object-fit: cover"
                                                         src="{{ $leader->user ? asset(User::AVATAR_UPLOAD_PATH.$leader->user->avatar):
                                                                    asset('assets/images/img.png')
                                                        }}"
                                                         alt="profile">
                                                </button>

                                            @empty

                                            @endforelse
                                        </td>
                                        <td class="text-center">
                                            <div class="progress">
                                                <div class="progress-bar color2 rounded"
                                                     role="progressbar"
                                                     style="{{AppHelper::getProgressBarStyle($project->getProjectProgressInPercentage())}}"
                                                     aria-valuenow="25"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                    <span>{{($project->getProjectProgressInPercentage())}} %</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                                    <span
                                                        class="btn btn-{{$projectPriority[$project->priority]}} btn-xs cursor-default">
                                                            {{ucfirst($project->priority)}}
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
            @endcan
        @endcanany
    </section>
@endsection

<script src="{{asset('assets/vendors/chartjs/Chart.min.js')}}"></script>

@section('scripts')
    <script>
        let translatedStrings = @json(__('index'));
    </script>
    @include('admin.dashboard_scripts')
@endsection









