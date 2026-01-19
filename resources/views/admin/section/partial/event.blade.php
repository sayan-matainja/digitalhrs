
@can('list_event')
    <li class="nav-item {{ request()->routeIs('admin.event.*')  ? 'active' : '' }}">
        <a
            href="{{ route('admin.event.index') }}"
            data-href="{{ route('admin.event.index') }}"
            class="nav-link">
            <i class="link-icon" data-feather="tv"></i>
            <span class="link-title">{{ __('index.event') }}</span>
        </a>
    </li>
@endcan
