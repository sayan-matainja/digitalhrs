@if(\App\Helpers\AppHelper::checkSuperAdmin())
    <li class="nav-item  {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <a class="nav-link" data-bs-toggle="collapse"
           href="#user-management"
           data-href="#"
           role="button" aria-expanded="false" aria-controls="settings">
            <i class="link-icon" data-feather="user"></i>
            <span class="link-title"> {{ __('index.user_management') }} </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ request()->routeIs('admin.users.*') ? '' : 'collapse'  }} " id="user-management">

            <ul class="nav sub-menu">
                <li class="nav-item">
                    <a
                        href="{{route('admin.users.index')}}"
                        data-href="{{route('admin.users.index')}}"
                        class="nav-link {{request()->routeIs('admin.users.*') ? 'active' : ''}}">{{ __('index.users') }}</a>
                </li>
            </ul>
        </div>
    </li>
@endif
