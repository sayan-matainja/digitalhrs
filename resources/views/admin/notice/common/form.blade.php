<div class="row">

    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-6 col-md-6 mb-4">
            <label for="branch_id" class="form-label">@lang('index.branch') <span style="color: red">*</span></label>
            <select class="form-select" id="branch_id" name="branch_id">
                <option  {{!isset($noticeDetail) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
                </option>
                @if(isset($companyDetail))
                    @foreach($companyDetail->branches()->get() as $key => $branch)
                        <option value="{{$branch->id}}"
                            {{ (isset($noticeDetail) && ($noticeDetail->branch_id ) == $branch->id) ? 'selected': '' }}>
                            {{ucfirst($branch->name)}}</option>
                    @endforeach
                @endif
            </select>
        </div>
    @endif

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="title" class="form-label">@lang('index.notice_title') <span style="color: red">*</span></label>
        <input type="text" class="form-control" name="title" value="{{ old('title', $noticeDetail->title ?? '') }}" required>
    </div>

    <div class="col-lg-12">
        <div class="row">
         <div class="col-lg-6 mb-4">
            <div class="form_desc">
                <label for="description" class="form-label">@lang('index.notice_description') <span style="color: red">*</span></label>
                <textarea class="form-control" name="description" id="tinymceExample" rows="7">{!! old('description', $noticeDetail->description ?? '') !!}</textarea>
            </div>
         </div>
         <!-- Receiver: Department & Employee -->
         <div class="col-lg-6">
            <!-- Departments -->
            <div class="form_desc mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">@lang('index.department') <span style="color: red">*</span></label>
                    <div class="check_box">
                        <input type="checkbox" id="select_all_departments"> <label for="select_all_departments">@lang('index.all_department')</label>
                    </div>
                </div>
                <select class="form-select" id="department_id" name="receiver[department][]" multiple></select>
            </div>
            <!-- Employees -->
            <div class="form_desc mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">@lang('index.notice_receiver') <span style="color: red">*</span></label>
                    <div class="check_box">
                        <input type="checkbox" id="select_all_employees"> <label for="select_all_employees">@lang('index.all_employees')</label>
                    </div>
                </div>
                <select class="form-select" id="employee_id" name="receiver[employee][]" multiple></select>
            </div>
         </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <label for="is_active" class="form-label">@lang('index.status') <span style="color: red">*</span></label>
        <select class="form-select" name="is_active" required>
            <option value="" disabled>@lang('index.select_status')</option>
            <option value="1" {{ old('is_active', $noticeDetail->is_active ?? '') == 1 ? 'selected' : '' }}>@lang('index.active')</option>
            <option value="0" {{ old('is_active', $noticeDetail->is_active ?? '') == 0 ? 'selected' : '' }}>@lang('index.inactive')</option>
        </select>
    </div>
    <input type="hidden" readonly id="notification" name="notification" value="0">


        <div class="text-center text-md-start border-top pt-4">
            <button type="submit" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($noticeDetail)?  __('index.update'): __('index.create')}}
            </button>

            <button type="submit" id="withNotification" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($noticeDetail)?  __('index.update_send'): __('index.create_send')}}
            </button>
        </div>
</div>
