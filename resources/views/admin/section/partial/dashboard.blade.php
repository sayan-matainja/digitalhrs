<li class="nav-item {{request()->routeIs('admin.dashboard.*')  ? 'active' : '' }} " >
    <a href="{{route('admin.dashboard')}}"
       data-href="{{route('admin.dashboard')}}"
       class="nav-link">
        <i class="link-icon" data-feather="box"></i>
        <span class="link-title">{{ __('index.dashboard') }}</span>
    </a>
</li>
