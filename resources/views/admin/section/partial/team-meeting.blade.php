
@can('list_team_meeting')
    <li class="nav-item {{ request()->routeIs('admin.team-meetings.*')  ? 'active' : '' }}">
        <a
            href="{{ route('admin.team-meetings.index') }}"
            data-href="{{ route('admin.team-meetings.index') }}"
            class="nav-link">
            <i class="link-icon" data-feather="globe"></i>
            <span class="link-title">{{ __('index.team_meeting') }}</span>
        </a>
    </li>
@endcan
