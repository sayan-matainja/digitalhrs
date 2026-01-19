@can('list_content')
    <li class="nav-item">
        <a href="{{ route('admin.static-page-contents.index') }}"
           data-href="{{ route('admin.static-page-contents.index') }}" class="nav-link">
            <i class="link-icon" data-feather="book"></i>
            <span class="link-title">{{__('index.content_management')}}</span>
        </a>
    </li>
@endcan
