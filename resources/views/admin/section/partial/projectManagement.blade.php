{{--@can('view_project_list')--}}
{{--    <li class="nav-item {{ request()->routeIs('admin.projects.*')  ? 'active' : '' }}">--}}
{{--        <a--}}
{{--            href="{{ route('admin.projects.index') }}"--}}
{{--            data-href="{{ route('admin.projects.index') }}"--}}
{{--            class="nav-link">--}}
{{--            <i class="link-icon" data-feather="box"></i>--}}
{{--            <span class="link-title">{{__('index.project_management')}}</span>--}}
{{--        </a>--}}
{{--</li>--}}
{{--@endcan--}}


@canany(['view_project_list','view_task_list','view_client_list'])
    <li class="nav-item {{ request()->routeIs('admin.projects.*') || request()->routeIs('admin.clients.*') || request()->routeIs('admin.tasks.*')
                        ? 'active' : '' }} ">
        <a class="nav-link" data-bs-toggle="collapse" href="#projectMenu" data-href="#" role="button" aria-expanded="false"
           aria-controls="projectMenu">
            <i class="link-icon" data-feather="layout"></i>
            <span class="link-title">{{ __('index.project_management') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ request()->routeIs('admin.projects.*') || request()->routeIs('admin.clients.*') || request()->routeIs('admin.tasks.*')
                   ?'' : 'collapse'  }}" id="projectMenu">
            <ul class="nav sub-menu">

                @can('view_project_list')
                    <li class="nav-item {{ request()->routeIs('admin.projects.*')
                        ? 'active' : '' }}">
                        <a href="{{ route('admin.projects.index') }}"
                           data-href="{{ route('admin.projects.index') }}"
                           class="nav-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">{{ __('index.project') }}</a>
                    </li>
                @endcan
                @can('view_task_list')
                    <li class="nav-item {{ request()->routeIs('admin.tasks.*')
                        ? 'active' : '' }}">
                        <a href="{{ route('admin.tasks.index') }}"
                           data-href="{{ route('admin.tasks.index') }}"
                           class="nav-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">{{ __('index.tasks') }}</a>
                    </li>
                @endcan
                @can('view_client_list')
                    <li class="nav-item {{ request()->routeIs('admin.clients.*')
                        ? 'active' : '' }}">
                        <a href="{{ route('admin.clients.index') }}"
                           data-href="{{ route('admin.clients.index') }}"
                           class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">{{ __('index.clients') }}</a>
                    </li>
                @endcan

            </ul>
        </div>
    </li>
@endcanany

