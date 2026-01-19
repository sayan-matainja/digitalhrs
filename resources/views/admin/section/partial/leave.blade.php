
@canany(['list_leave_type','list_leave_request','access_admin_leave','list_leave_approval','time_leave_list'])
    <li class="nav-item {{ request()->routeIs('admin.leaves.*') ||
                            request()->routeIs('admin.time-leave-request.*') ||
                            request()->routeIs('admin.leave-approval.*') ||
                            request()->routeIs('admin.leave-setting.*') ||
                            request()->routeIs('admin.leaveBalance.*') ||
                        request()->routeIs('admin.leave-request.*')
                        ? 'active' : '' }} ">
        <a class="nav-link" data-bs-toggle="collapse" href="#leaveMenu" data-href="#" role="button" aria-expanded="false"
           aria-controls="leaveMenu">
            <i class="link-icon" data-feather="pause-circle"></i>
            <span class="link-title">{{ __('index.leave') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{  request()->routeIs('admin.leaves.*') ||
                            request()->routeIs('admin.time-leave-request.*') ||
                            request()->routeIs('admin.leave-approval.*') ||
                            request()->routeIs('admin.leave-setting.*') ||
                            request()->routeIs('admin.leaveBalance.*') ||
                        request()->routeIs('admin.leave-request.*')
                   ?'' : 'collapse'  }}" id="leaveMenu">
            <ul class="nav sub-menu">

                @canany(['list_leave_type','access_admin_leave'])
                    <li class="nav-item {{ request()->routeIs('admin.leaves.*')
                        ? 'active' : '' }}">
                        <a href="{{ route('admin.leaves.index') }}"
                           data-href="{{ route('admin.leaves.index') }}"
                           class="nav-link {{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">{{ __('index.leave_types') }}</a>
                    </li>
                @endcanany
                @canany(['list_leave_request','access_admin_leave','time_leave_list'])
                    <li class="nav-item {{ request()->routeIs('admin.leave-request.*') || request()->routeIs('admin.time-leave-request.*')
                        ? 'active' : '' }}">
                        <a href="{{ route('admin.leave-request.index') }}"
                           data-href="{{ route('admin.leave-request.index') }}"
                           class="nav-link {{ request()->routeIs('admin.leave-request.*') || request()->routeIs('admin.time-leave-request.*') ? 'active' : '' }}">{{ __('index.leave_request') }}</a>
                    </li>
                @endcanany
{{--                    @can('time_leave_list')--}}
{{--                    <li class="nav-item {{ request()->routeIs('admin.time-leave-request.*')--}}
{{--                        ? 'active' : '' }}">--}}
{{--                        <a href="{{ route('admin.time-leave-request.index') }}"--}}
{{--                           data-href="{{ route('admin.time-leave-request.index') }}"--}}
{{--                           class="nav-link {{ request()->routeIs('admin.time-leave-request.*') ? 'active' : '' }}">{{ __('index.time_leave_request') }}</a>--}}
{{--                    </li>--}}
{{--                @endcan--}}
                @can('list_leave_approval')
                    <li class="nav-item {{ request()->routeIs('admin.leave-approval.*')
                        ? 'active' : '' }}">
                        <a href="{{ route('admin.leave-approval.index') }}"
                           data-href="{{ route('admin.leave-approval.index') }}"
                           class="nav-link {{ request()->routeIs('admin.leave-approval.*') ? 'active' : '' }}">{{ __('index.leave_approval') }}</a>
                    </li>
                @endcan
                @can('leave_balance')
                    <li class="nav-item {{ request()->routeIs('admin.leaveBalance.*')
                        ? 'active' : '' }}">
                        <a href="{{ route('admin.leaveBalance.index') }}"
                           data-href="{{ route('admin.leaveBalance.index') }}"
                           class="nav-link {{ request()->routeIs('admin.leaveBalance.*') ? 'active' : '' }}">{{ __('index.leave_balance') }}</a>
                    </li>
                @endcan



            </ul>
        </div>
    </li>
@endcanany

