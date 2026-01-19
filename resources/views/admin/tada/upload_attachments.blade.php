
@extends('layouts.master')

@section('title',__('index.tada_attachment'))

@section('action',__('index.upload_tada_attachment'))

@section('button')
    <div class="float-end">
        <a href="{{route('admin.tadas.show',$tadaId)}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{__('index.back')}}</button>
        </a>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{asset('assets/css/imageuploadify.min.css')}}">
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.tada.common.breadcrumb')

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card ">
                    <div class="card-header">
                        <h5 class="text-muted">{{ __('index.upload_tada_attachment') }}</h5>
                    </div>
                    <div class="card-body">
                        <form id="tadaAttachment" class="forms-sample" action="{{route('admin.tadas.attachment.store')}}"
                              enctype="multipart/form-data"
                              method="POST"
                        >
                            @csrf
                            <div class="row">
                                <input type="hidden" value="{{$tadaId}}" readonly name="tada_id" >
                                <div class="mb-3 col-12">
                                    <div>
                                        <input id="image-uploadify" type="file" name="attachments[]"
                                               accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xls,.txt,.zip" multiple />
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mb-3">
                                <button type="submit" class="btn btn-primary">{{__('index.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    <script src="{{asset('assets/js/imageuploadify.min.js')}}"></script>

    <script>
        $(document).ready(function () {
            $("#image-uploadify").imageuploadify();
        });
    </script>

@endsection



