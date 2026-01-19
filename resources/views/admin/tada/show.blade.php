@php use App\Models\TadaAttachment; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title',__('index.tada'))

@section('action',__('index.tada_detail'))

@section("styles")
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet"/>
@endsection

<?php
$status = [
    'pending' => 'info',
    'accepted' => 'success',
    'rejected' => 'danger',
];
?>

@section('button')
    <div class="breadcrumb-button float-md-end d-md-flex align-items-center">
        @can('edit_tada')
            @if(AppHelper::checkSuperAdmin() || ($tadaDetail->is_settled == 0))
                <a href="{{route('admin.tadas.edit',$tadaDetail->id)}}">
                    <button class="btn btn-success d-md-flex align-items-center me-2"><i class="link-icon me-1"
                                                                                         data-feather="edit"></i>{{ __('index.edit_tada') }}
                    </button>
                </a>
            @endif
        @endcan

        @can('create_attachment')
            @if(AppHelper::checkSuperAdmin() || ($tadaDetail->is_settled == 0))
                <a href="{{route('admin.tadas.attachment.create',$tadaDetail->id)}}">
                    <button class="btn btn-secondary d-md-flex align-items-center me-2"><i class="link-icon me-1"
                                                                                           data-feather="clipboard"></i>{{ __('index.upload_attachments') }}
                    </button>
                </a>
            @endif
        @endcan

        @can('edit_tada')
            @if(AppHelper::checkSuperAdmin() || ($tadaDetail->is_settled == 0))
                <button class="btn btn-primary d-md-flex align-items-center me-2"
                        id="updateStatus"
                        data-id="{{ $tadaDetail->id }}"
                        data-status="{{($tadaDetail->status)}}"
                        data-title="{{ucfirst($tadaDetail->title)}}"
                        data-reason="{{($tadaDetail->remark)}}"
                        data-action="{{route('admin.tadas.update-status', $tadaDetail->id)}}"
                >
                    <i class="link-icon me-1" data-feather="edit"></i>{{ __('index.update_status') }}</button>
            @endif
        @endcan

        <a href="{{route('admin.tadas.index')}}">
            <button class="btn btn-primary d-md-flex align-items-center"><i class="link-icon"
                                                                            data-feather="arrow-left"></i>{{ __('index.back') }}
            </button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.tada.common.breadcrumb')
        <div class="row position-relative">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="mb-2">{{ucfirst($tadaDetail->title)}}</h3>
                    </div>

                    <div class="card-body">
                        {!!  $tadaDetail->description!!}

                        @if(isset($attachments) && count($attachments) > 0 )
                            <div class="border-top mt-3 pt-3">
                                <h6 class="mb-2">{{ __('index.uploaded_attachment') }} </h6>
                                <div class="row">
                                    @foreach($attachments as $key => $data)
                                        @if(in_array(pathinfo(asset(TadaAttachment::ATTACHMENT_UPLOAD_PATH.$data->attachment), PATHINFO_EXTENSION),['jpeg','png','jpg'])  )
                                            <div class="col-lg-3 mb-2">
                                                <div class="uploaded-image">
                                                    <a href="{{ asset(TadaAttachment::ATTACHMENT_UPLOAD_PATH.$data->attachment) }}"
                                                       data-lightbox="image-1" data-title="{{$data->attachment}}">
                                                        <img class="w-100" style=""
                                                             src="{{ asset(TadaAttachment::ATTACHMENT_UPLOAD_PATH.$data->attachment) }}"
                                                             alt="document images">
                                                    </a>
                                                    <p>{{$data->attachment}}</p>
                                                    @can('delete_attachment')
                                                        @if(AppHelper::checkSuperAdmin() || ($tadaDetail->is_settled == 0))
                                                            <a class="documentDelete" id="delete" data-title="Image"
                                                               href="{{route('admin.tadas.attachment-delete',$data->id)}}">
                                                                <i class="link-icon remove-image" data-feather="x"></i>
                                                            </a>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </div>
                                        @else
                                            <div class="uploaded-files">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-1">
                                                        <div class="file-icon">
                                                            <i class="link-icon" data-feather="file-text"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-10">
                                                        <a target="_blank"
                                                           href="{{asset(TadaAttachment::ATTACHMENT_UPLOAD_PATH.$data->attachment)}}">
                                                            {{asset(TadaAttachment::ATTACHMENT_UPLOAD_PATH.$data->attachment)}}
                                                        </a>
                                                    </div>

                                                    @can('delete_attachment')
                                                        <div class="col-lg-1">
                                                            <a class="delete" data-title="attachment file"
                                                               data-href="{{route('admin.tadas.attachment-delete',$data->id)}}">
                                                                <i class="link-icon remove-files" data-feather="x"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 sidebar-list position-relative">
                <div class="position-sticky top-0">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>{{ __('index.tada_summary') }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-border">
                                <tbody>
                                <tr>
                                    <td>{{ __('index.total_expense') }}:</td>
                                    <td class="text-end">
                                        {{number_format($tadaDetail->total_expense)}}
                                    </td>
                                </tr>

                                <tr>
                                    <td>{{ __('index.employee') }}:</td>
                                    <td class="text-end text-success">{{ucfirst($tadaDetail->employeeDetail->name)}}</td>
                                </tr>

                                <tr>
                                    <td>{{ __('index.submitted_date') }}:</td>
                                    <td class="text-end text-danger">{{ AppHelper::formatDateForView($tadaDetail->created_at)}}</td>
                                </tr>

                                <tr>
                                    <td>{{ __('index.verified_by') }}:</td>
                                    <td class="text-end text-success">
                                        {{$tadaDetail->verifiedBy ? ucfirst($tadaDetail->verifiedBy->name) : 'Admin'}}
                                    </td>
                                </tr>

                                <tr>
                                    <td>{{ __('index.verified_date') }}:</td>
                                    <td class="text-end text-danger">{{ $tadaDetail->verifiedBy ? AppHelper::formatDateForView($tadaDetail->updated_at)  : 'N/A'}}</td>
                                </tr>

                                <tr>
                                    <td>{{ __('index.status') }}:</td>
                                    <td class="text-end">
                                            <span
                                                class="btn btn-{{$status[$tadaDetail->status]}} cursor-default btn-xs">
                                                {{ucfirst($tadaDetail->status)}}
                                            </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>{{ __('index.remark') }}</td>
                                    <td class="text-end">
                                        <span class="text-end text-muted"> {{$tadaDetail->remark ?? 'N/A'}}</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.tada.update_status_form')

    </section>
@endsection

@section('scripts')
    @include('admin.tada.common.scripts')
@endsection
