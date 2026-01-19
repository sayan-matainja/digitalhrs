@extends('layouts.master')

@section('title',__('index.award'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.awards.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.awardManagement.awardDetail.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="name" class="form-label">{{ __('index.branch') }} </label>
                        <input type="text" class="form-control"
                               id="name"
                               value="{{$awardDetail->branch?->name}}"
                               readonly >
                    </div>

                     <div class="col-lg-4 col-md-6 mb-4">
                        <label for="name" class="form-label">{{ __('index.department') }} </label>
                        <input type="text" class="form-control"
                               id="name"
                               value="{{$awardDetail->department?->dept_name}}"
                               readonly >
                    </div>

                     <div class="col-lg-4 col-md-6 mb-4">
                        <label for="name" class="form-label">{{ __('index.employee_name') }} </label>
                        <input type="text" class="form-control"
                               id="name"
                               value="{{$awardDetail->employee?->name}}"
                               readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="type" class="form-label">{{ __('index.award_name') }}</label>
                        <input type="text"
                               class="form-control"
                               id="name"
                               value="{{$awardDetail->type?->title}}"
                               readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="awardCode" class="form-label">{{ __('index.gift_item') }}</label>
                        <input type="text" class="form-control"
                               id="awardCode"
                               value="{{$awardDetail->gift_item}}"
                              readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="awardCode" class="form-label">{{ __('index.award_base') }}</label>
                        <input type="text" class="form-control"
                               id="awardCode"
                               value="{{$awardDetail->award_base}}"
                               readonly >
                    </div>
{{--                    @php--}}
{{--                       if($awardDetail->award_base == \App\Enum\AwardBaseEnum::yearly->value){--}}
{{--                           $awardDate = date('Y', strtotime($awardDetail->awarded_date)) ;--}}
{{--                       }elseif($awardDetail->award_base == \App\Enum\AwardBaseEnum::monthly->value){--}}
{{--                          $awardDate = date('F Y', strtotime($awardDetail->awarded_date)) ;--}}
{{--                       }else{--}}
{{--                           $awardDate =  date('Y-m-d', strtotime($awardDetail->awarded_date)) ;--}}
{{--                       }--}}
{{--                    @endphp--}}

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="purchased_date" class="form-label">{{ __('index.awarded_date') }}</label>
                        <input type="text"
                               class="form-control"
                               value="{{ \App\Helpers\AppHelper::formatDateForView($awardDetail->awarded_date) }}"
                               readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="warranty_available" class="form-label">{{ __('index.awarded_by') }}</label>
                        <input type="text"
                               class="form-control"
                               id="warranty_available"
                               value="{{$awardDetail->awarded_by}}"
                               readonly >
                    </div>


                    <div class="col-lg-6 mb-4">
                        <label for="note" class="form-label">{{ __('index.award_description') }}</label>
                        <div class="rounded p-3"  style="background-color: #e9ecef">
                            {!!  $awardDetail->award_description !!}
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label for="note" class="form-label">{{ __('index.gift_description') }}</label>
                        <div class="rounded p-3"  style="background-color: #e9ecef">
                            {!!  $awardDetail->gift_description !!}
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 mb-4">
                        <label for="warranty_available" class="form-label">{{ __('index.reward_code') }}</label>
                        <input type="text"
                               class="form-control"
                               id="warranty_available"
                               value="{{$awardDetail->reward_code}}"
                               readonly >
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label for="image" class="form-label d-block ">{{ __('index.attachment') }} </label>
                        <img id="image-preview" class="rounded p-4"
                             src="{{  asset(\App\Models\Award::UPLOAD_PATH.$awardDetail->attachment)}}"
                             style="object-fit: contain; background-color: #e9ecef; height:200px;"
                        >
                    </div>


                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.awardManagement.awardDetail.common.scripts')
@endsection

