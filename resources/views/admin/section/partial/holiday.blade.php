

@can('list_holiday')
    <li class="nav-item {{ request()->routeIs('admin.holidays.*')  ? 'active' : '' }}">
        <a
            href="{{ route('admin.holidays.index') }}"
            data-href="{{ route('admin.holidays.index') }}"
            class="nav-link">
            <i class="link-icon" data-feather="credit-card"></i>
            <span class="link-title">{{ __('index.holidays') }}</span>
        </a>
    </li>
@endcan
