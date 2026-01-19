
@canany(['list_office_time'])
    <li class="nav-item  {{ request()->routeIs('admin.office-times.*')  ? 'active' : '' }}    ">
        <a class="nav-link"   data-href="#" data-bs-toggle="collapse" href="#shiftManagement" role="button" aria-expanded="false" aria-controls="shiftManagment">
            <i class="link-icon" data-feather="watch"></i>
            <span class="link-title"> {{ __('index.shift_management') }} </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ request()->routeIs('admin.office-times.*') ?'' : 'collapse'  }} " id="shiftManagement">
             <ul class="nav sub-menu">

                 @can('list_office_time')
                    <li class="nav-item">
                        <a href="{{route('admin.office-times.index')}}"
                           data-href="{{route('admin.office-times.index')}}"
                           class="nav-link {{request()->routeIs('admin.office-times.*') ? 'active' : ''}}">{{ __('index.office_time') }} </a>
                    </li>
                 @endcan

            </ul>
        </div>
    </li>
@endcanany
