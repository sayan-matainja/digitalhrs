@can(['list_warning'])
    <li class="nav-item {{ request()->routeIs('admin.warning.*')
                        ? 'active' : '' }} ">
        <a class="nav-link" href="{{ route('admin.warning.index') }}" aria-expanded="false">
            <i class="link-icon" data-feather="alert-triangle"></i>
            <span class="link-title">{{ __('index.warning') }}</span>
        </a>
    </li>
@endcan
