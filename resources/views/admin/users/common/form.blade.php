
<div class="mb-2"><small>{!! __('index.all_fields_required') !!}</small></div>
<style>
    .is-invalid {
        border-color: red !important;
    }

    .is-invalid + .error-message {
        display: block;
        color: red !important;
    }

    .error-message {
        display: none;
        color: red !important;
    }
</style>
<div class="card mb-4">
    <div class="card-body pb-3">
        <div class="profile-detail">
            <div class="row">

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="name" class="form-label"> {{ __('index.name') }} <span style="color: red">*</span></label>
                    <input type="text" class="form-control"
                           id="name"
                           name="name"
                           value="{{ ( isset($userDetail) ? $userDetail->name: old('name') )}}" autocomplete="off"
                           placeholder="{{ __('index.enter_name') }}" required>
                </div>


                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="email" class="form-label">{{ __('index.email') }} <span style="color: red">*</span></label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="{{ ( isset($userDetail) ? $userDetail->email: old('email') )}}" required
                           autocomplete="off" placeholder="{{ __('index.enter_email') }}">
                </div>

                <div class="col-lg-4 mb-3">
                    <label for="avatar" class="form-label">{{ __('index.upload_avatar') }} </label>
                    <input class="form-control"
                           type="file"
                           id="avatar"
                           name="avatar"
                           accept="image/*"
                           value="{{ isset($userDetail) ? $userDetail->avatar: old('avatar') }}">

                    <img class="mt-2 rounded {{(isset($userDetail) && $userDetail->avatar) ? '': 'd-none'}}"
                         id="image-preview"
                         src="{{ (isset($userDetail) && $userDetail->avatar) ? asset(\App\Models\Admin::AVATAR_UPLOAD_PATH.$userDetail->avatar) : ''}}"
                         style="object-fit: contain"
                         width="100"
                         height="100"
                    >
                </div>
                <div class="col-lg-4 mb-3">
                    <label for="username" class="form-label">{{ __('index.username') }} <span style="color: red">*</span></label>
                    <input type="text" class="form-control" id="username" name="username"
                        value="{{ ( isset($userDetail) ? $userDetail->username: old('username') )}}"
                        required
                        autocomplete="off" placeholder="{{ __('index.enter_username') }}">
                </div>
                @if(!isset($userDetail))
                    <div class="col-lg-4 mb-3">
                        <label for="password" class="form-label">{{ __('index.password') }} <span style="color: red">*</span></label>
                        <input type="password" class="form-control" id="password" name="password"
                            value="{{old('password')}}" autocomplete="off" placeholder="{{ __('index.enter_password') }}" required>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary">
    <i class="link-icon" data-feather="plus"></i> {{isset($userDetail)? __('index.update_user'):__('index.create_user')}}
</button>
