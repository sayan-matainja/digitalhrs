@canany(['award_type_list','award_list'])
    <li class="nav-item {{ request()->routeIs('admin.award-types.*') || request()->routeIs('admin.awards.*')
                        ? 'active' : '' }} ">
        <a class="nav-link" data-bs-toggle="collapse" href="#awards" data-href="#" role="button" aria-expanded="false" aria-controls="awards">
            <i class="link-icon" data-feather="award"></i>
            <span class="link-title">{{ __('index.award_management') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ request()->routeIs('admin.award-types.*') || request()->routeIs('admin.awards.*')
                   ?'' : 'collapse'  }}" id="awards">
            <ul class="nav sub-menu">

                @can('award_type_list')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.award-types.index')}}"
                            data-href="{{route('admin.award-types.index')}}"
                            class="nav-link {{ request()->routeIs('admin.award-types.*') ? 'active' : '' }}">{{ __('index.award_types') }}</a>
                    </li>
                @endcan

                @can('award_list')
                    <li class="nav-item">
                        <a href="{{route('admin.awards.index')}}"
                           data-href="{{route('admin.awards.index')}}"
                           class="nav-link {{ request()->routeIs('admin.awards.*') ? 'active' : '' }}">{{ __('index.awards') }}</a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcanany
