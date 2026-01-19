
<style>
    .img-wrap {
        position: relative;
        display: inline-block;
        font-size: 0;
    }
    .img-wrap .close {
        position: absolute;
        top: 2px;
        right: 2px;
        z-index: 100;
        background-color: #FFF;
        padding: 5px 2px 2px;
        color: #000;
        font-weight: bold;
        cursor: pointer;
        opacity: .5;
        text-align: center;
        font-size: 30px;
        line-height: 20px;
        border-radius: 50%;
    }
    .img-wrap:hover .close {
        opacity: 1;
    }
</style>

<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option  {{!isset($eventDetail) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
            </option>
            @if(isset($companyDetail))
                @foreach($companyDetail->branches()->get() as $key => $branch)
                    <option value="{{$branch->id}}"
                        {{ ((isset($eventDetail) && ($eventDetail->branch_id ) == $branch->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $branch->id)) ? 'selected': '' }}>
                        {{ucfirst($branch->name)}}</option>
                @endforeach
            @endif
        </select>
    </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="title" class="form-label"> {{ __('index.event_title') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="title" name="title" required value="{{ ( isset( $eventDetail) ?  $eventDetail->title: old('title') )}}"
               autocomplete="off" placeholder="{{ __('index.enter_event_title') }}">
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="host" class="form-label"> {{ __('index.event_host') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="host" name="host" required value="{{ ( isset( $eventDetail) ?  $eventDetail->host: old('host') )}}"
               autocomplete="off" placeholder="{{ __('index.event_host') }}">
    </div>
    <div class="col-lg-4 mb-4 mb-3">
        <label for="location" class="form-label">{{ __('index.event_location') }}  <span style="color: red">*</span> </label>
        <input type="text" class="form-control" id="location" name="location" required value="{{ ( isset( $eventDetail) ?  $eventDetail->location: old('location') )}}"
               autocomplete="off" placeholder="{{ __('index.event_location') }}">
    </div>
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-lg-6 col-md-6 event-date-time mb-3">
                        <label for="start_date" class="form-label">@lang('index.event_start_date') <span style="color: red">*</span> </label>
                        @if($isBsEnabled)
                            <input type="text" class="form-control nepali_date" id="start_date" name="start_date" required value="{{ ( isset( $eventDetail) ?  \App\Helpers\AppHelper::taskDate($eventDetail->start_date): old('start_date') )}}"
                                autocomplete="off" >
                        @else
                            <input type="date" class="form-control" name="start_date" required value="{{ ( isset( $eventDetail) ?  $eventDetail->start_date: old('start_date') )}}"
                                autocomplete="off" >
                        @endif
                    </div>
                    <div class="col-lg-6 col-md-6 event-date-time mb-3">
                        <label for="end_date" class="form-label">@lang('index.event_end_date') </label>
                        @if($isBsEnabled)
                            <input type="text" class="form-control nepali_date" id="end_date" name="end_date"  value="{{ ( isset( $eventDetail->end_date) ?  \App\Helpers\AppHelper::taskDate($eventDetail->end_date): old('end_date') )}}"
                                autocomplete="off" >
                        @else
                            <input type="date" class="form-control" name="end_date" value="{{ ( isset( $eventDetail) ?  $eventDetail->end_date: old('end_date') )}}"
                                autocomplete="off" >
                        @endif
                    </div>
                    <div class="col-lg-6 col-md-6 event-date-time mb-3">
                        <label for="start_time" class="form-label">@lang('index.event_start_time') <span style="color: red">*</span> </label>
                        <input type="time" class="form-control" id="start_time" name="start_time" required value="{{ ( isset( $eventDetail) ? $eventDetail->start_time : old('start_time') )}}"
                            autocomplete="off" >
                    </div>
                    <div class="col-lg-6 col-md-6 event-date-time mb-3">
                        <label for="end_time" class="form-label">@lang('index.event_end_time') <span style="color: red">*</span> </label>
                        <input type="time" class="form-control" id="end_time" name="end_time" required value="{{ ( isset( $eventDetail) ?$eventDetail->end_time : old('end_time') )}}"
                            autocomplete="off" >
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <label for="description" class="form-label">{{ __('index.description') }} <span style="color: red">*</span></label>
                <textarea class="form-control" minlength="10" name="description" id="description"  rows="6">{!! ( isset( $eventDetail) ?  $eventDetail->description: old('description') ) !!} </textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="image" class="form-label">{{ __('index.upload_attachment') }}</label>
        <input class="form-control" type="file" accept="image/png, image/jpeg,image/jpg, image/svg,"   id="image" name="attachment" />

        @if(isset($eventDetail) && $eventDetail->attachment)
            <div class="img-wrap mt-3" style="object-fit: contain">
                <span class="close removeImage" data-href="{{route('admin.event.remove-image',$eventDetail->id)}}">&times;</span>
                <img   src="{{asset(\App\Models\Event::UPLOAD_PATH.$eventDetail->attachment)}}"
                    alt="" width="200"
                    height="200">
            </div>
        @endif

    </div>

    <div class="col-lg-6 col-md-6 mb-3">
        <label for="validationCustom01" class="form-label d-block">{{ __('index.background_color') }}</label>
        <input type="color" class="form-control form-control-color" name="background_color" id="exampleColorInput" value="{{ isset($eventDetail) ? $eventDetail->background_color : old('background_color') }}" title="Choose your color" />
    </div>

        <div class="col-lg-6 mb-3">
            <div class="input-contain d-flex align-items-center justify-content-between mb-2">
                <label for="department" class="form-label mb-0">@lang('index.departments') <span style="color: red">*</span></label>
            </div>
            <select class="form-select" id="department_id" name="department_id[]" multiple="multiple" required>
                <option value="select_all">{{ __('index.select_all') }}</option>
            </select>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="input-contain d-flex align-items-center justify-content-between mb-2">
                <label for="employee" class="form-label mb-0">@lang('index.employee') <span style="color: red">*</span></label>
            </div>
            <select class="form-select" id="employee_id" name="employee_id[]" multiple="multiple" required>
                <option value="select_all">{{ __('index.select_all') }}</option>
            </select>
        </div>

    <input type="hidden" readonly id="eventNotification" name="notification" value="0">
    <div class="text-center text-md-start border-top pt-4 pb-2">
        <button type="submit" class="btn btn-primary mb-2">{{isset($eventDetail) ? __('index.update') : __('index.create')}}</button>
        <button type="submit" id="withEventNotification" class="btn btn-primary mb-2">
            <i class="link-icon" data-feather="plus"></i>
            {{isset($eventDetail)?  __('index.update_send'): __('index.create_send')}}
        </button>
    </div>

</div>








