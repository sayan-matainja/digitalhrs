<nav class="page-breadcrumb d-flex align-items-center justify-content-between mb-0">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.projects.index')}}">{{ __('index.projects') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">@yield('action')</li>
    </ol>

    @yield('button')

</nav>
