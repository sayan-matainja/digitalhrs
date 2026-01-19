@extends('layouts.master')

@section('title', __('index.logout_requests'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.logout_requests') }}</li>
            </ol>
        </nav>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.logout_request_filter')  }}</h6>
            </div>

            <form class="forms-sample card-body pb-0" action="{{ route('admin.logout-requests.index') }}" method="get">
                <div class="row align-items-center">

                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  selected  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterData['branch_id']) && $filterData['branch_id']  == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif


                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option  selected  disabled>{{ __('index.select_department') }}
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option  selected  disabled>{{ __('index.select_employee') }}
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 d-md-flex gap-2">
                        <button type="submit" class="btn btn-block btn-success mb-4">{{ __('index.filter') }}</button>

                        <a class="btn btn-block btn-primary  mb-4"
                           href="{{ route('admin.logout-requests.index') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.logout_requests') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee_name') }}</th>
                            <th>{{ __('index.logout_request_status') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($logoutRequests as $key => $value)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td><strong>{{ removeSpecialChars($value->name) }}</strong></td>
                                <td>
                                    <button class="btn btn-primary btn-xs acceptLogoutRequest"
                                            data-href="{{ route('admin.logout-requests.accept', $value->id) }}">
                                        {{ __('index.take_action') }}
                                    </button>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.acceptLogoutRequest').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '{{ __('index.confirm_accept_logout_request') }}',
                    showDenyButton: true,
                    confirmButtonText: `{{ __('index.yes') }}`,
                    denyButtonText: `{{ __('index.no') }}`,
                    padding: '10px 50px 10px 50px',
                    // width:'500px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })
        });

    </script>
    @include('admin.attendance.common.filter_scripts')
@endsection
