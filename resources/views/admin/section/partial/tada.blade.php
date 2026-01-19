@can('view_tada_list')
    <li class="nav-item {{ request()->routeIs('admin.tadas.*')  ? 'active' : '' }}">
        <a
            href="{{ route('admin.tadas.index') }}"
            data-href="{{ route('admin.tadas.index') }}"
            class="nav-link">
            <i class="link-icon" data-feather="file-text"></i>
            <span class="link-title">{{ __('index.tada') }}</span>
        </a>
    </li>
@endcan
