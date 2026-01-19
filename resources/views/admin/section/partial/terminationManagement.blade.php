@canany(['termination_type_list','list_termination'])
    <li class="nav-item {{ request()->routeIs('admin.termination-types.*') || request()->routeIs('admin.termination.*')
                        ? 'active' : '' }} ">
        <a class="nav-link" data-bs-toggle="collapse" href="#termination" data-href="#" role="button" aria-expanded="false" aria-controls="termination">
            <i class="link-icon" data-feather="x-circle"></i>
            <span class="link-title">{{ __('index.termination_management') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ request()->routeIs('admin.termination-types.*') || request()->routeIs('admin.termination.*')
                   ?'' : 'collapse'  }}" id="termination">
            <ul class="nav sub-menu">

                @can('list_type')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.termination-types.index')}}"
                            data-href="{{route('admin.termination-types.index')}}"
                            class="nav-link {{ request()->routeIs('admin.termination-types.*') ? 'active' : '' }}">{{ __('index.termination_types') }}</a>
                    </li>
                @endcan

                @can('list_termination')
                    <li class="nav-item">
                        <a href="{{route('admin.termination.index')}}"
                           data-href="{{route('admin.termination.index')}}"
                           class="nav-link {{ request()->routeIs('admin.termination.*') ? 'active' : '' }}">{{ __('index.termination') }}</a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcanany
