<div class="row">
    <div class="col-lg-6 mb-3">
        <label for="name" class="form-label">Name <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name"
               name="name"
               value="{{ ( isset($generalSettingDetail) ? $generalSettingDetail->name: old('name') )}}"
               autocomplete="off"
               placeholder="">
    </div>

    <div class="col-lg-6 mb-3">
        <label for="exampleFormControlSelect1" class="form-label">Type</label>
        <select class="form-select" id="type" required name="type">
            <option value="" {{ isset($generalSettingDetail) ? '':'selected'}} disabled></option>
            @foreach(\App\Enum\GeneralSettingEnum::cases() as $key => $enum)
                <option value="{{$enum->value}}" {{ isset($generalSettingDetail) && $generalSettingDetail->value == $enum->value ? 'selected':''}} > {{$enum->name}}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 mb-3">
        <label for="" class="form-label">key</label>
        <select class="form-select" id="key" required name="key">
            <option value="" {{ isset($generalSettingDetail) ? '':'selected'}} disabled>Select General Setting Key</option>
            @foreach(\App\Models\GeneralSetting::GENERAL_SETTING_KEY as $value)
                <option value="{{$value}}" {{ isset($generalSettingDetail) && $generalSettingDetail->value == $value ? 'selected':''}} > {{removeSpecialChar($value)}}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 mb-3 " >
        <label for="leave_allocated" class="form-label">Value</label>
        <input type="text" class="form-control"
               id="value"
               name="value"
               value="{{ isset($generalSettingDetail)? $generalSettingDetail->value: old('value') }}"
               autocomplete="off" >
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{isset($generalSettingDetail)? 'Update':'Create'}} General Settings</button>
    </div>
</div>


