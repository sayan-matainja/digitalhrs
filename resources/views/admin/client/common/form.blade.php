<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option value="{{ $value->id }}" {{ (isset($clientDetail) && $clientDetail->branch_id == $value->id) ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.client_name') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" required
               value="{{ isset($clientDetail) ? $clientDetail->name : old('name') }}"
               autocomplete="off" placeholder="{{ __('index.enter_client_name') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="email" class="form-label">{{ __('index.client_email') }} <span style="color: red">*</span></label>
        <input type="email" class="form-control" id="email" name="email" required
               value="{{ isset($clientDetail) ? $clientDetail->email : old('email') }}"
               autocomplete="off" placeholder="{{ __('index.enter_client_email') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="contact_no" class="form-label">{{ __('index.client_contact') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="contact_no" name="contact_no" required
               value="{{ isset($clientDetail) ? $clientDetail->contact_no : old('contact_no') }}"
               autocomplete="off" placeholder="{{ __('index.enter_contact_number') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="address" class="form-label">{{ __('index.client_address') }}</label>
        <input type="text" class="form-control" id="address" name="address"
               value="{{ isset($clientDetail) ? $clientDetail->address : old('address') }}"
               autocomplete="off" placeholder="{{ __('index.enter_client_address') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="country" class="form-label">{{ __('index.client_country') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="country" name="country" required
               value="{{ isset($clientDetail) ? $clientDetail->country : old('country') }}"
               autocomplete="off" placeholder="{{ __('index.enter_country') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="avatar" class="form-label">{{ __('index.upload_profile') }} <span style="color: red">*</span></label>
        <input class="form-control" type="file" id="avatar" name="avatar"
               value="{{ isset($clientDetail) ? $clientDetail->avatar : old('avatar') }}" {{ isset($clientDetail) ? '' : 'required' }}>
        @if(isset($clientDetail) && $clientDetail->avatar)
            <img class="mt-3" src="{{ asset(\App\Models\Client::UPLOAD_PATH . $clientDetail->avatar) }}"
                 alt="" width="200" style="object-fit: contain" height="200">
        @endif
    </div>

    <div class="col-lg col-md-6 mb-4 mt-md-4 text-start">
        <button type="submit" class="btn btn-primary">
            <i class="link-icon" data-feather="{{ isset($clientDetail) ? 'edit-2' : 'plus' }}"></i>
            {{ isset($clientDetail) ? __('index.update') : __('index.create') }}
        </button>
    </div>
</div>
