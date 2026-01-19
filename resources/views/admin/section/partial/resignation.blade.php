@can(['list_resignation'])
    <li class="nav-item {{ request()->routeIs('admin.resignation.*')
                        ? 'active' : '' }} ">
        <a class="nav-link" href="{{ route('admin.resignation.index') }}" aria-expanded="false">
            <i class="link-icon" data-feather="user-minus"></i>
            <span class="link-title">{{ __('index.resignation') }}</span>
        </a>
    </li>
@endcan
