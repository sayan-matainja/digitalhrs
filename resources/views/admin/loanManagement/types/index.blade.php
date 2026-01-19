@extends('layouts.master')
@section('title',__('index.loan_types'))
@section('action',__('index.lists'))

@section('button')
    @can('create_type')
        <button class="btn btn-primary create-loanType mb-3">
            <i class="link-icon" data-feather="plus"></i> {{ __('index.add_loan_types') }}
        </button>
    @endcan
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.loanManagement.types.common.breadcrumb')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.loan_type_filter')</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.loan-types.index') }}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch" name="branch_id">
                                <option  {{ !isset($filterParameters['branch_id']) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif

                    <div class="col-lg-3 col-md-6 mb-4">
                        <input type="text" class="form-control" placeholder="@lang('index.type')" name="type" id="title" value="{{ $filterParameters['type'] }}">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">@lang('index.filter')</button>
                            <a class="btn btn-block btn-primary" href="{{ route('admin.loan-types.index') }}">@lang('index.reset')</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.loan_type_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.name') }}</th>
                            <th class="text-center">{{ __('index.minimum_amount') }}</th>
                            <th class="text-center">{{ __('index.maximum_amount') }}</th>
                            <th class="text-center">{{ __('index.interest_rate') }}</th>
                            <th class="text-center">{{ __('index.interest_type') }}</th>
                            <th class="text-center">{{ __('index.payment_terms') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['show_type','edit_type','delete_type'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($LoanTypeLists as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{ucfirst($value->name)}}</td>
                                <td class="text-center">{{ number_format($value->minimum_amount, 2) }}</td>
                                <td class="text-center">{{ number_format($value->maximum_amount, 2) }}</td>
                                <td class="text-center">{{ number_format($value->interest_rate, 2) }}%</td>
                                <td class="text-center">{{ ucfirst(str_replace('_', ' ', $value->interest_type)) }}</td>
                                <td class="text-center">{{ $value->term }}</td>

                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{route('admin.loan-types.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('show_type')
                                            <li class="me-2">
                                                <a class="view-loanType" data-id="{{ $value->id }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('edit_type')
                                            <li class="me-2">
                                                <a class="edit-loanType"  data-id="{{ $value->id }}" data-href="{{ route('admin.loan-types.edit', $value->id) }}">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('delete_type')
                                            <li>
                                                <a class="delete"
                                                   data-href="{{route('admin.loan-types.delete',$value->id)}}" title="{{ __('index.delete') }}">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- loan type create/edit modal -->
    <div class="modal fade" id="loanTypeModal" tabindex="-1" aria-labelledby="loanTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title" id="loanTypeModalLabel">{{ __('index.add_loan_types') }}</h5>
                </div>
                <div class="modal-body pb-0">
                    <form id="loanTypeForm" class="forms-sample" enctype="multipart/form-data" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">

                        <div class="row align-items-center">
                            @if(!isset(auth()->user()->branch_id))
                                <div class="col-lg-6 mb-4">
                                    <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                    <select class="form-select" id="branch_id" name="branch_id" required>
                                        <option selected disabled>{{ __('index.select_branch') }}</option>
                                        @if(isset($companyDetail))
                                            @foreach($companyDetail->branches()->get() as $key => $branch)
                                                <option value="{{$branch->id}}">{{ucfirst($branch->name)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif

                            <div class="col-lg-6 mb-3">
                                <label for="name" class="form-label">{{ __('index.name') }}<span style="color: red">*</span></label>
                                <input type="text" class="form-control" id="name"
                                       required
                                       name="name"
                                       value="{{ old('name') }}"
                                       autocomplete="off"
                                       placeholder="Enter Loan Type Name"
                                >
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="minimum_amount" class="form-label">{{ __('index.minimum_amount') }}<span style="color: red">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="minimum_amount"
                                       required
                                       name="minimum_amount"
                                       value="{{ old('minimum_amount') }}"
                                       autocomplete="off"
                                       placeholder="Enter Minimum Amount"
                                >
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="maximum_amount" class="form-label">{{ __('index.maximum_amount') }}<span style="color: red">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="maximum_amount"
                                       required
                                       name="maximum_amount"
                                       value="{{ old('maximum_amount') }}"
                                       autocomplete="off"
                                       placeholder="Enter Maximum Amount"
                                >
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="interest_rate" class="form-label">{{ __('index.interest_rate') }} %<span style="color: red">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="interest_rate"
                                       required
                                       name="interest_rate"
                                       value="{{ old('interest_rate') }}"
                                       autocomplete="off"
                                       placeholder="Enter Interest Rate"
                                >
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="interest_type" class="form-label">{{ __('index.interest_type') }}<span style="color: red">*</span></label>
                                <select class="form-select" id="interest_type" name="interest_type" required>
                                    <option value="" selected disabled>{{ __('index.select_interest_type') }}</option>
                                    @foreach($interestTypes as $type)
                                        <option value="{{ $type->value }}" {{ old('interest_type') == $type->value ? 'selected' : '' }}>
                                            {{ ucfirst($type->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="term" class="form-label">{{ __('index.payment_terms') }}<span style="color: red">*</span></label>
                                <input type="number" min="1" class="form-control" id="term"
                                       required
                                       name="term"
                                       value="{{ old('term') }}"
                                       autocomplete="off"
                                       placeholder="Enter Term in Months"
                                >
                            </div>

                            <div class="col-lg-12 mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="link-icon" data-feather="plus"></i> <span id="submitButtonText">{{ __('index.save') }}</span>
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- loan type show modal -->
    <div class="modal fade" id="showLoanTypeModal" tabindex="-1" aria-labelledby="showLoanTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title" id="showLoanTypeModalLabel">{{ __('index.view_loan_type') }}</h5>
                </div>
                <div class="modal-body">
                <div class="row gy-3">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-6">
                            <label class="form-label mb-1">{{ __('index.branch') }}</label>
                            <p class="form-controls fw-bold" id="showBranch">--</p>
                        </div>
                    @endif
                    <div class="col-lg-6">
                        <label class="form-label mb-1">{{ __('index.name') }}</label>
                        <p class="form-controls fw-bold" id="showName">--</p>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label mb-1">{{ __('index.minimum_amount') }}</label>
                        <p class="form-controls fw-bold" id="showMinAmount">--</p>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label mb-1">{{ __('index.maximum_amount') }}</label>
                        <p class="form-controls fw-bold" id="showMaxAmount">--</p>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label mb-1">{{ __('index.interest_rate') }}</label>
                        <p class="form-controls fw-bold" id="showInterestRate">--</p>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label mb-1">{{ __('index.interest_type') }}</label>
                        <p class="form-controls fw-bold" id="showInterestType">--</p>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label mb-1">{{ __('index.payment_terms') }}</label>
                        <p class="form-controls fw-bold" id="showTerm">--</p>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label mb-1">{{ __('index.status') }}</label>
                        <p class="form-controls fw-bold" id="showStatus">--</p>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')

    @include('admin.loanManagement.types.common.scripts')
@endsection
