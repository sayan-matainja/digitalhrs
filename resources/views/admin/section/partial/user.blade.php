
@canany(['list_employee','list_logout_request'])
    <li class="nav-item  {{
                           request()->routeIs('admin.employees.*') ||
                           request()->routeIs('admin.employee.log') ||
                           request()->routeIs('admin.card.*') ||
                           request()->routeIs('admin.logout-requests.*')
                        ? 'active' : ''
                        }}   ">
        <a data-href="#"
           class="nav-link"
           data-bs-toggle="collapse"
           href="#employee_management"
           role="button"
           aria-expanded="false"
           aria-controls="company">
            <i class="link-icon" data-feather="users"></i>
            <span class="link-title">{{ __('index.employee_management') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>

        <div class="{{
                         request()->routeIs('admin.employees.*') ||
                         request()->routeIs('admin.employee.log') ||
                          request()->routeIs('admin.card.*') ||
                            request()->routeIs('admin.logout-requests.*')?'' : 'collapse'  }}"  id="employee_management">
            <ul class="nav sub-menu">
                @can('list_employee')
                    <li class="nav-item">
                        <a href="{{route('admin.employees.index')}}"
                           data-href="{{route('admin.employees.index')}}"
                           class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : ''}}">{{ __('index.employees') }}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.employee.log')}}"
                           data-href="{{route('admin.employee.log')}}"
                           class="nav-link {{request()->routeIs('admin.employee.log') ? 'active' : ''}}"> Location Logs</a>
                    </li>
                @endcan

                @can('list_logout_request')
                    <li class="nav-item">
                        <a href="{{route('admin.logout-requests.index')}}"
                           data-href="{{route('admin.logout-requests.index')}}"
                           class="nav-link {{request()->routeIs('admin.logout-requests.*') ? 'active' : ''}}">{{ __('index.logout_requests') }}</a>
                    </li>
                @endcan
                @can('card_template')
                    <li class="nav-item">
                        <a href="{{route('admin.card.template-list')}}"
                           data-href="{{route('admin.card.template-list')}}"
                           class="nav-link {{request()->routeIs('admin.card.*') ? 'active' : ''}}">{{ __('index.card_template') }}</a>
                    </li>
                @endcan


            </ul>
        </div>
    </li>
@endcanany



