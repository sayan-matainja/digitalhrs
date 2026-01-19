

<div class="row">

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="company_id" class="form-label">{{ __('index.company_name') }} <span style="color: red">*</span></label>
        <select class="form-select" id="company_id" name="company_id" required>
            <option selected value="{{ isset($companyDetail) ? $companyDetail->id : '' }}" >{{ isset($companyDetail) ? $companyDetail->name : ''}}</option>
        </select>
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="title" class="form-label">{{ __('index.content_title') }}  <span style="color: red">*</span> </label>
        <input type="text" class="form-control" id="title" name="title" required value="{{ ( isset($companyContentDetail) ? $companyContentDetail->title: old('title') )}}" autocomplete="off" placeholder="{{ __('index.enter_content_title') }}">
    </div>

    <div class="col-lg-6 mb-4">
        <label for="description" class="form-label">{{ __('index.description') }} <span style="color: red">*</span></label>
        <textarea class="form-control" name="description"  id="tinymceExample" rows="4">{{ ( isset($companyContentDetail) ? $companyContentDetail->description: old('description') )}}</textarea>
    </div>

    <div class="col-lg-6">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <label for="content_type" class="form-label">{{ __('index.content_type') }}<span style="color: red">*</span></label>
                <select class="form-select" id="exampleFormControlSelect1" name="content_type" required>
                    <option value=""  {{isset($companyContentDetail) ? '':'selected'}}>{{ __('index.select_content_type') }}</option>
                    @foreach(\App\Models\CompanyContentManagement::CONTENT_TYPE as $value)
                        <option value="{{ $value }}" {{ (isset($companyContentDetail) && ($companyContentDetail->content_type ) == $value) ? 'selected':old('category') }} >{{ removeSpecialChars($value) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-12 mb-4">
                <label for="exampleFormControlSelect1" class="form-label">{{ __('index.status') }} <span style="color: red">*</span></label>
                <select class="form-select" id="exampleFormControlSelect1" name="is_active" required>
                    <option value="" {{isset($companyContentDetail) ? '':'selected'}} >{{ __('index.select_status') }}</option>
                    <option value="1" {{ isset($companyContentDetail) && ($companyContentDetail->is_active ) == 1 ? 'selected': old('is_active') }}>{{ __('index.active') }}</option>
                    <option value="0" {{ isset($companyContentDetail) && ($companyContentDetail->is_active ) == 0 ? 'selected': old('is_active') }}>{{ __('index.inactive') }}</option>
                </select>
            </div>
        </div>
    </div>



    <div class="col-lg-12">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{isset($companyContentDetail)? __('index.update'):__('index.create')}} {{ __('index.content') }}</button>
    </div>
</div>







