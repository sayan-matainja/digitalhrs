<div class="row">

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label"> {{ __('index.company_name') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" value="{{ ( $companyDetail ? $companyDetail->name: '' )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="owner" class="form-label">{{ __('index.company_owner') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="owner" name="owner_name" value="{{ ($companyDetail? $companyDetail->owner_name: old('name') )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="address" class="form-label"> {{ __('index.address') }} <span style="color: red">*</span> </label>
        <input type="text" class="form-control" id="address" name="address" value="{{ ($companyDetail? $companyDetail->address: old('address') )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="email" class="form-label">{{ __('index.email_address') }} <span style="color: red">*</span></label>
        <input type="email" class="form-control" id="address" name="email" value="{{ ($companyDetail? $companyDetail->email: old('email') )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="number" class="form-label">{{ __('index.phone_no') }} <span style="color: red">*</span></label>
        <input type="number" class="form-control" id="phone" name="phone" value="{{ ($companyDetail? $companyDetail->phone: old('phone') )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="website" class="form-label"> {{ __('index.website_url') }}</label>
        <input type="url" class="form-control" id="website" name="website_url" value="{{ ($companyDetail? $companyDetail->website_url: old('website_url') )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 mb-4">
        <label for="weekend" class="form-label"> {{ __('index.check_office_off_days') }}  </label><br>
        @foreach(\App\Helpers\AttendanceHelper::WEEK_DAY_IN_NEPALI as $key => $value)
            <input type="checkbox" id="{{ \App\Helpers\AppHelper::ifDateInBsEnabled() ? $value['np'] : $value['en'] }}" name="weekend[]" value="{{$key}}"
            @if($companyDetail && !is_null($companyDetail->weekend))
                @foreach($companyDetail->weekend as $i => $datum)
                    {{ $datum == $key ? 'checked' : '' }}
                    @endforeach
                @endif
            >
            <label for="weekends"> {{ $value['en'] }}</label><br>
        @endforeach
    </div>
    <div class="col-lg-4 col-md-6 mb-4 mt-2">
        <input type="checkbox"  id="count_holiday_weekend" value="1" name="count_holiday_weekend" {{ ( $companyDetail && $companyDetail->count_holiday_weekend == 1 ? 'checked' : '' )}}> {{ __('index.count_holiday_weekend') }}
    </div>


    <div class="col-lg-4 col-md-6 mb-4">
        <label for="upload" class="form-label">{{ __('index.upload_logo') }}</label>
        <input class="form-control" type="file" id="upload" name="logo" >
        @if(($companyDetail && $companyDetail->logo))
            <img  src="{{asset(\App\Models\Company::UPLOAD_PATH.$companyDetail->logo)}}"
                  alt=""  style="object-fit: contain" class="mt-3 ht-150 wd-150"
            >
        @endif
    </div>


    @canany(['create_company','edit_company'])
        <div class="col-lg-12 mb-4 text-start">
            <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ $companyDetail ? __('index.update') : __('index.save') }} {{ __('index.company') }}</button>
        </div>
    @endcanany
</div>
