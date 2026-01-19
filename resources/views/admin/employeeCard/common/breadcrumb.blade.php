<nav class="page-breadcrumb d-flex align-items-center justify-content-between">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.card.template-list')}}">{{ __('index.card_template') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('index.lists') }}</li>
    </ol>

    @yield('button')
</nav>
