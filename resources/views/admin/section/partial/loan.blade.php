@canany(['loan_type_list','list_loan','repayment_list','request_loan','loan_setting'])
    <li class="nav-item  {{
                    request()->routeIs('admin.loan-types.*') ||
                    request()->routeIs('admin.loan.*') ||
                    request()->routeIs('admin.loan-request.*') ||
                    request()->routeIs('admin.request-settlement.*') ||
                    request()->routeIs('admin.loan-repayment.*') ||
                    request()->routeIs('admin.loanSetting.*') ||
                    request()->routeIs('emi-calculator')

                ? 'active' : ''
            }}"
    >
        <a class="nav-link" data-bs-toggle="collapse"
           href="#loan"
           data-href="#"
           role="button" aria-expanded="false" aria-controls="settings">
            <i class="link-icon" data-feather="gift"></i>
            <span class="link-title"> {{ __('index.loan_management') }} </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{
                request()->routeIs('admin.loan-types.*') ||
                request()->routeIs('admin.loan.*') ||
                request()->routeIs('admin.loan-request.*') ||
                request()->routeIs('admin.request-settlement.*') ||
                request()->routeIs('admin.loan-repayment.*')||
                request()->routeIs('admin.loanSetting.*') ||
                request()->routeIs('emi-calculator')
               ? '' : 'collapse'  }} " id="loan">

            <ul class="nav sub-menu">
                @can('loan_type_list')
                    <li class="nav-item">
                        <a href="{{ route('admin.loan-types.index') }}"
                           data-href="{{ route('admin.loan-types.index') }}"
                           class="nav-link  {{ request()->routeIs('admin.loan-types.*') ? 'active':'' }}">{{ __('index.loan_type') }}</a>
                    </li>
                @endcan
                @can('list_loan')
                    <li class="nav-item">
                        <a href="{{ route('admin.loan.index') }}"
                           data-href="{{ route('admin.loan.index') }}"
                           class="nav-link  {{ request()->routeIs('admin.loan.*') ? 'active':'' }}">{{ __('index.loan_list') }}</a>
                    </li>
                @endcan
                @can('repayment_list')
                    <li class="nav-item">
                        <a href="{{ route('admin.loan-repayment.list') }}"
                           data-href="{{ route('admin.loan-repayment.list') }}"
                           class="nav-link  {{ request()->routeIs('admin.loan-repayment.*') ? 'active':'' }}">{{ __('index.loan_repayment') }}</a>
                    </li>
                @endcan

                @if(!auth('admin')->check() && auth()->check())
                    @can('request_loan')
                        <li class="nav-item">
                            <a href="{{ route('admin.loan-request.index') }}"
                               data-href="{{ route('admin.loan-request.index') }}"
                               class="nav-link  {{ request()->routeIs('admin.loan-request.*') ? 'active':'' }}">{{ __('index.request_loan') }}</a>
                        </li>
                    @endcan
                @endif


                <li class="nav-item">
                    <a href="{{ route('emi-calculator') }}"
                       data-href="{{ route('emi-calculator') }}"
                       class="nav-link  {{ request()->routeIs('emi-calculator') ? 'active':'' }}">{{ __('index.emi_calculator') }}</a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.request-settlement.index') }}"
                       data-href="{{ route('admin.request-settlement.index') }}"
                       class="nav-link  {{ request()->routeIs('admin.request-settlement.*') ? 'active':'' }}">{{ __('index.settlement_request_list') }}</a>
                </li>
                @if(!auth('admin')->check() && auth()->check())
                    <li class="nav-item">
                        <a href="{{ route('admin.settlementRequest.index') }}"
                           data-href="{{ route('admin.settlementRequest.index') }}"
                           class="nav-link  {{ request()->routeIs('admin.settlementRequest.*') ? 'active':'' }}">{{ __('index.request_settlement') }}</a>
                    </li>
                @endif

                @can('loan_setting')
                    <li class="nav-item">
                        <a href="{{ route('admin.loanSetting.index') }}"
                           data-href="{{ route('admin.loanSetting.index') }}"
                           class="nav-link  {{ request()->routeIs('admin.loanSetting.*') ? 'active':'' }}">{{ __('index.setting') }}</a>
                    </li>
                @endcan



            </ul>
        </div>
    </li>
@endcanany

