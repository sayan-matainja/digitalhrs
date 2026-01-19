

<div class="row">

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="company_id" class="form-label">@lang('index.company_name') <span style="color: red">*</span></label>
        <select class="form-select" id="company_id" name="company_id" required>
            <option selected value="{{ isset($companyDetail) ? $companyDetail->id : '' }}" >{{ isset($companyDetail) ? $companyDetail->name : ''}}</option>
        </select>
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="title" class="form-label"> @lang('index.notification_title') <span style="color: red">*</span> </label>
        <input type="text" class="form-control" id="title" name="title" required value="{{ ( isset($notificationDetail) ? $notificationDetail->title: old('title') )}}"
               autocomplete="off" placeholder="@lang('index.enter_notification_title')">
    </div>



    <div class="col-lg-12 mb-4">
        <label for="description" class="form-label">@lang('index.notification_description') <span style="color: red">*</span></label>
        <textarea class="form-control" name="description" id=""  rows="6">{{ ( isset($notificationDetail) ? $notificationDetail->description: old('description') )}}</textarea>
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="is_active" class="form-label">@lang('index.status')</label>
        <select class="form-select" id="is_active" name="is_active" required>
            <option value="" {{isset($notificationDetail) ? '':'selected'}} >@lang('index.select_status')</option>
            <option value="1" {{ isset($notificationDetail) && ($notificationDetail->is_active ) == 1 ? 'selected': old('is_active') }}>@lang('index.active')</option>
            <option value="0" {{ isset($notificationDetail) && ($notificationDetail->is_active ) == 0 ? 'selected': old('is_active') }}>@lang('index.inactive')</option>
        </select>
    </div>

    <div class="text-start col-lg-6 col-md-6 mb-4 mt-md-4">
        <button type="submit" class="btn btn-primary"> @lang('index.send_notification')</button>
    </div>

</div>







