<div class="card overflow-hidden position-sticky top-0 mb-4">
    <ul class="nav payroll-sidebar-menu">
        @canany(['list_leave_type','access_admin_leave'])
            <li class="nav-item {{request()->routeIs('admin.leaves.*') ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.leaves.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.leaves.index')}}">
                    {{ __('index.leave_types') }}
                </a>
            </li>
        @endcanany
        @canany(['list_leave_request','access_admin_leave'])

            <li class="nav-item {{ request()->routeIs('admin.leave-request.*')  ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{ request()->routeIs('admin.leave-request.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.leave-request.index')}}">
                    {{ __('index.leave_request') }}
                </a>
            </li>
        @endcanany
{{--        @can('time_leave_list')--}}
{{--            <li class="nav-item {{request()->routeIs('admin.time-leave-request.*')  ? 'bg-danger' : '' }} w-100"--}}
{{--                style="border-bottom: 1px solid #ede7e7;">--}}
{{--                <a class="nav-link {{request()->routeIs('admin.time-leave-request.*') ? 'text-white' : 'text-black' }}"--}}
{{--                   href="{{ route('admin.time-leave-request.index')}}">--}}
{{--                    {{ __('index.time_leave_request') }}--}}
{{--                </a>--}}
{{--            </li>--}}
{{--        @endcan--}}
        @can('list_leave_approval')
            <li class="nav-item {{request()->routeIs('admin.leave-approval.*')  ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.leave-approval.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.leave-approval.index')}}">
                    {{ __('index.leave_approval') }}
                </a>
            </li>
        @endcan
    </ul>
</div>
