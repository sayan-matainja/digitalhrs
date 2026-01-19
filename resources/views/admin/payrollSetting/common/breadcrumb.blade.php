<nav class="page-breadcrumb d-flex align-items-center justify-content-between">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
        <li class="breadcrumb-item">{{ __('index.payroll_setting') }}</li>
        <li class="breadcrumb-item active" aria-current="page">@yield('page')</li>
        <li class="breadcrumb-item active" aria-current="page">@yield('sub_page')</li>
    </ol>
</nav>
