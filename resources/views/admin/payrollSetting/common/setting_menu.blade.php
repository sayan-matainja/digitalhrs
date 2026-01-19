

<div class="card overflow-hidden position-sticky top-0 mb-4">
    <ul class="nav payroll-sidebar-menu">
        @can('salary_component')
            <li class="nav-item {{request()->routeIs('admin.salary-components.*') ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.salary-components.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.salary-components.index')}}">
                    {{ __('index.salary_component') }}
                </a>
            </li>
        @endcan
        @can('salary_group')
            <li class="nav-item {{ request()->routeIs('admin.salary-groups.*')  ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{ request()->routeIs('admin.salary-groups.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.salary-groups.index')}}">
                    {{ __('index.salary_group') }}
                </a>
            </li>
        @endcan
        @can('ssf')
            <li class="nav-item {{request()->routeIs('admin.ssf.*') ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.ssf.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.ssf.index')}}">
                    {{ __('index.ssf') }}
                </a>
            </li>
        @endcan
         @can('pf')
            <li class="nav-item {{request()->routeIs('admin.pf.*') ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.pf.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.pf.index')}}">
                    {{ __('index.pf') }}
                </a>
            </li>
        @endcan
        @can('bonus')
            <li class="nav-item {{request()->routeIs('admin.bonus.*') ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.bonus.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.bonus.index')}}">
                    Bonus
                </a>
            </li>
        @endcan
        @can('salary_tds')
            <li class="nav-item {{request()->routeIs('admin.salary-tds.*')  ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.salary-tds.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.salary-tds.index')}}">
                    {{ __('index.salary_tds') }}
                </a>
            </li>
        @endcan
        @can('advance_salary_limit')
            <li class="nav-item {{request()->routeIs('admin.advance-salaries.setting')  ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.advance-salaries.setting') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.advance-salaries.setting')}}">
                    {{ __('index.advance_salary') }}
                </a>
            </li>
        @endcan
        @can('overtime_setting')
            <li class="nav-item {{request()->routeIs('admin.overtime.*')  ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.overtime.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.overtime.index')}}">
                    {{ __('index.overtime') }}
                </a>
            </li>
        @endcan
        @can('undertime_setting')
            <li class="nav-item {{request()->routeIs('admin.under-time.*')  ? 'bg-danger' : '' }} w-100"
                style="border-bottom: 1px solid #ede7e7;">
                <a class="nav-link {{request()->routeIs('admin.under-time.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.under-time.create')}}">
                    {{ __('index.undertime') }}
                </a>
            </li>
        @endcan
        @can('payment_method')
            <li class="nav-item {{request()->routeIs('admin.payment-methods.*')  ? 'bg-danger' : '' }} w-100" style="">
                <a class="nav-link {{request()->routeIs('admin.payment-methods.*') ? 'text-white' : 'text-black' }}"
                   href="{{ route('admin.payment-methods.index')}}">
                    {{ __('index.payment_method') }}
                </a>
            </li>

        @endcan


    </ul>
</div>
