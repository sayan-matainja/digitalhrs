@can('list_notice')
    <li class="nav-item {{ request()->routeIs('admin.notices.*')  ? 'active' : '' }}">
        <a
            href="{{ route('admin.notices.index') }}"
            data-href="{{ route('admin.notices.index') }}"
            class="nav-link">
            <i class="link-icon" data-feather="calendar"></i>
            <span class="link-title">{{ __('index.notice') }}</span>
        </a>
    </li>
@endcan
