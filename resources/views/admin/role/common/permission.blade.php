


<ul class="nav nav-tabs" id="myTab" role="tablist">
    @foreach($permissionGroupTypeList as $key => $permissionGroupType)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $key == 0 ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-{{ $permissionGroupType->slug }}" type="button" role="tab" aria-controls="tab-{{ $permissionGroupType->slug }}" aria-selected="{{ $key == 0 ? 'true' : 'false' }}" id="{{$permissionGroupType->slug}}">
                {{$permissionGroupType->name}} @lang('index.permissions')
            </button>
        </li>
    @endforeach
</ul>
<div class="tab-content mt-4 px-4" id="myTabContent">
    @foreach($permissionGroupTypeList as $key => $permissionGroupType)
        @php
            $permissionModules = $permissionGroupTypeList->where('slug', $permissionGroupType->slug)->first();
        @endphp

        <div class="tab-pane fade {{ $key == 0 ? 'show active' : '' }}" id="tab-{{ $permissionGroupType->slug }}" role="tabpanel" aria-labelledby="{{ $permissionGroupType->slug }}">
            <div class="row mb-2 {{ $permissionModules->slug }}">
                @foreach($permissionModules->permissionGroups as $key=>$value)

                @php
                    $collectionArray = $value->getPermission->pluck('id')->toArray();

                    $checkAll = '';

                    if(count($role_permission) > 0){
                        $diff = array_diff($collectionArray, $role_permission);

                         if (empty($diff)) {
                           $checkAll = 'checked';
                         }
                    }

                @endphp
                <div class="col-lg-12">
                    <div class="group-checkbox border-bottom pb-3 mb-4 w-100">
                        <div class="title-ch mb-2">
                            <h5 style="color:#e82e5f;">{{$value->name}} @lang('index.module'):</h5>
                        </div>
                        <div class="head-checkbox d-flex align-items-center gap-3 flex-wrap">

                            <div class="checkAll">
                                <label class="label-ch lh-1">
                                    <input class="js-check-all" type="checkbox" name=""
                                           data-check-all="website" {{ $checkAll }}>
                                    <span class="text fw-bold">@lang('index.check_all')</span>
                                </label>
                            </div>
                            <ul class="js-check-all-target list-ch d-flex align-items-center justify-content-start gap-3 p-0 flex-wrap" data-check-all="website">
                                @foreach($value->getPermission as $keys => $permission)
                                    @php
                                        $checked='';
                                        if(count($role_permission) > 0){
                                            if(in_array($permission->id,$role_permission)){
                                                $checked = "checked = 'checked'";
                                            }
                                        }
                                    @endphp
                                    <li class="item">
                                        <label class="label lh-1">
                                            <input class="module_checkbox"
                                                   type="checkbox"
                                                   id="{{$permission->permission_key}}"
                                                   name="permission_value[]"
                                                   value="{{$permission->id}}"
                                                {{$checked}}>
                                            <span class="text">{{$permission->name}}
                                    </span>
                                            @if($permission->permission_key == 'access_admin_leave')
                                                <p class="grant_leave">
                                                    {{ __('index.admin_permission_msg') }}

                                                </p>
                                            @endif
                                        </label>

                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<div class="text-start">
    <button type="submit" class="btn btn-success btn-md">
        {{$isEdit ? __('index.update'): __('index.save') }}
    </button>
</div>

