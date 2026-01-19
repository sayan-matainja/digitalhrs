<nav class="page-breadcrumb d-flex align-items-center justify-content-between">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.branch.index')}}">{{ __('index.branch_section') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('index.branches') }}</li>
    </ol>

    @yield('button')
</nav>
