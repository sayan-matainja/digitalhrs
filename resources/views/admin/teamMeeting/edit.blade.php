
@extends('layouts.master')

@section('title',__('index.team_meeting'))

@section('action',__('index.edit'))

@section('button')
    <a href="{{route('admin.team-meetings.index')}}" >
        <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{__('index.back')}}</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.teamMeeting.common.breadcrumb')

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form id="notification" class="forms-sample" action="{{route('admin.team-meetings.update',$teamMeetingDetail->id)}}" enctype="multipart/form-data"  method="post">
                            @method('PUT')
                            @csrf
                            @include('admin.teamMeeting.common.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')

    @include('admin.teamMeeting.common.scripts')

@endsection

