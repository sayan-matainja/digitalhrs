@canany(['award_type_list','award_list','termination_type_list','list_termination','list_promotion','list_transfer'])
    <li class="nav-item {{ request()->routeIs('admin.award-types.*') || request()->routeIs('admin.awards.*') || request()->routeIs('admin.holidays.*') || request()->routeIs('admin.termination-types.*')
 || request()->routeIs('admin.termination.*') || request()->routeIs('admin.resignation.*') || request()->routeIs('admin.warning.*') || request()->routeIs('admin.complaint.*')|| request()->routeIs('admin.promotion.*')
                      || request()->routeIs('admin.transfer.*')  ? 'active' : '' }} ">
        <a class="nav-link" data-bs-toggle="collapse" href="#awards" data-href="#" role="button" aria-expanded="false"
           aria-controls="awards">
            <i class="link-icon" data-feather="user-plus"></i>
            <span class="link-title">{{ __('index.hr_admin_setup') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ request()->routeIs('admin.award-types.*') || request()->routeIs('admin.awards.*') || request()->routeIs('admin.holidays.*') || request()->routeIs('admin.termination-types.*')
 || request()->routeIs('admin.termination.*') || request()->routeIs('admin.resignation.*') || request()->routeIs('admin.warning.*') || request()->routeIs('admin.complaint.*')|| request()->routeIs('admin.promotion.*')
                || request()->routeIs('admin.transfer.*')   ?'' : 'collapse'  }}" id="awards">
            <ul class="nav sub-menu">
                @can('award_list')
                    <li class="nav-item {{ request()->routeIs('admin.awards.*') || request()->routeIs('admin.award-types.*')
                        ? 'active' : '' }}">
                        <a href="{{route('admin.awards.index')}}"
                           data-href="{{route('admin.awards.index')}}"
                           class="nav-link {{ request()->routeIs('admin.awards.*') || request()->routeIs('admin.award-types.*') ? 'active' : '' }}">{{ __('index.awards') }}</a>
                    </li>
                @endcan
                @can('list_termination')
                    <li class="nav-item {{ request()->routeIs('admin.termination.*') || request()->routeIs('admin.termination-types.*')
                        ? 'active' : '' }} ">
                        <a href="{{route('admin.termination.index')}}"
                           data-href="{{route('admin.termination.index')}}"
                           class="nav-link {{ request()->routeIs('admin.termination.*') || request()->routeIs('admin.termination-types.*') ? 'active' : '' }}">{{ __('index.termination') }}</a>
                    </li>
                @endcan
                @can('list_resignation')
                    <li class="nav-item {{ request()->routeIs('admin.resignation.*')
                        ? 'active' : '' }} ">
                        <a class="nav-link {{ request()->routeIs('admin.resignation.*') ? 'active' : '' }}"
                           href="{{ route('admin.resignation.index') }}"
                           data-href="{{ route('admin.resignation.index') }}">
                            {{ __('index.resignation') }}
                        </a>
                    </li>
                @endcan
                @can('list_warning')
                    <li class="nav-item {{ request()->routeIs('admin.warning.*')
                        ? 'active' : '' }} ">
                        <a class="nav-link {{ request()->routeIs('admin.warning.*') ? 'active' : '' }}"
                           href="{{ route('admin.warning.index') }}"
                           data-href="{{ route('admin.warning.index') }}">
                            {{ __('index.warning') }}
                        </a>
                    </li>
                @endcan
                @can('list_complaint')
                    <li class="nav-item {{ request()->routeIs('admin.complaint.*')
                        ? 'active' : '' }} ">
                        <a class="nav-link {{ request()->routeIs('admin.complaint.*') ? 'active' : '' }}"
                           href="{{ route('admin.complaint.index') }}"
                           data-href="{{ route('admin.complaint.index') }}">
                            {{ __('index.complaint') }}
                        </a>
                    </li>
                @endcan
                @can('list_promotion')
                    <li class="nav-item {{ request()->routeIs('admin.promotion.*')
                        ? 'active' : '' }} ">
                        <a class="nav-link {{ request()->routeIs('admin.promotion.*') ? 'active' : '' }}"
                           data-href="{{ route('admin.promotion.index') }}"
                           href="{{ route('admin.promotion.index') }}">
                            {{ __('index.promotion') }}
                        </a>
                    </li>
                @endcan
                @can('list_transfer')
                    <li class="nav-item {{ request()->routeIs('admin.transfer.*')
                        ? 'active' : '' }} ">
                        <a class="nav-link {{ request()->routeIs('admin.transfer.*') ? 'active' : '' }}"
                           data-href="{{ route('admin.transfer.index') }}"
                           href="{{ route('admin.transfer.index') }}"
                        >
                            {{ __('index.transfer') }}
                        </a>
                    </li>
                @endcan
                @can('list_holiday')
                    <li class="nav-item {{ request()->routeIs('admin.holidays.*')  ? 'active' : '' }}">
                        <a
                            href="{{ route('admin.holidays.index') }}"
                            data-href="{{ route('admin.holidays.index') }}"
                            class="nav-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                            {{ __('index.holidays') }}
                        </a>
                    </li>
                @endcan

            </ul>
        </div>
    </li>
@endcanany
