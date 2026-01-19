
@canany(['view_query_list'])
    <li class="nav-item {{ request()->routeIs('admin.supports.*')  ? 'active' : '' }}">
        <a
            href="{{ route('admin.supports.index') }}"
            data-href="{{ route('admin.supports.index') }}"
            class="nav-link">
            <i class="link-icon" data-feather="help-circle"></i>
            <span class="link-title">{{ __('index.support') }}</span>
        </a>
    </li>
@endcan
