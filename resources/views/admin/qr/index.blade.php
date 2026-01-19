
@extends('layouts.master')

@section('title',__('index.qr'))
@section('styles')
    <style>
        .qr > svg {
            height: 100px;
            width: 100px;
        }
    </style>
@endsection
@section('button')
    @can('create_qr')
        <a href="{{ route('admin.qr.create')}}">
            <button class="btn btn-primary add_qr">
                <i class="link-icon" data-feather="plus"></i>@lang('index.add_qr')
            </button>
        </a>
    @endcan
@endsection
@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')
        @include('admin.qr.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.qr_filter')  }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.qr.index') }}" method="get">
                <div class="row align-items-center">

                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-4 col-md-6 mb-4">
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


                    <div class="col-lg-4 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option  selected  disabled>{{ __('index.select_department') }}
                            </option>
                        </select>
                    </div>


                    <div class="col-lg-2 col-md-6 d-md-flex">
                        <button type="submit" class="btn btn-block btn-success me-md-2 me-0 mb-md-4 mb-2">{{ __('index.filter') }}</button>

                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4"
                           href="{{ route('admin.qr.index') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.qr_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.branch')</th>
                            <th>@lang('index.title')</th>
                            <th>@lang('index.qr_image')</th>
                            <th class="text-center">@lang('index.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        @forelse($qrData as $qr)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $qr?->branch?->name }}</td>
                                <td>{{ $qr->title }}</td>
                                <td class="qr_code">
                                    <div class="qr">{!! $qr->qr_code !!}</div>
                                </td>

                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        <li class="me-2">
                                            <a href="{{route('admin.qr.print',$qr->id)}}" target="_blank" class="text-success" title="@lang('index.print')">
                                                <i class="link-icon" data-feather="printer"></i>
                                            </a>
                                        </li>
                                        @can('edit_qr')
                                            <li class="me-2">
                                                <a href="{{route('admin.qr.edit',$qr->id)}}" class="text-warning" title="@lang('index.edit') ">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('delete_qr')
                                            <li class="me-2">
                                                <a class="deleteQR"
                                                   data-href="{{route('admin.qr.destroy',$qr->id)}}" title="@lang('index.delete')">
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
                                    <p class="text-center"><b>@lang('index.no_records_found')</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

{{--        <div class="dataTables_paginate mt-3">--}}
{{--            {{$qr->appends($_GET)->links()}}--}}
{{--        </div>--}}



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


            $('.deleteQR').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '@lang('index.delete_confirmation')',
                    showDenyButton: true,
                    confirmButtonText: `@lang('index.yes')`,
                    denyButtonText: `@lang('index.no')`,

                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })



            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
            const branchId = "{{ $filterData['branch_id'] ?? null }}";
            const departmentId = "{{ $filterData['department_id'] ?? '' }}";


            const loadDepartments = async (selectedBranchId) => {

                if (!selectedBranchId) return;

                try {
                    $('#department_id').empty().append('<option selected disabled>{{ __("index.select_department") }}</option>');

                    const response = await $.ajax({
                        type: 'GET',
                        url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                    });

                    if (!response || !response.data || response.data.length === 0) {
                        $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                        return;
                    }


                    response.data.forEach(data => {
                        $('#department_id').append(`<option value="${data.id}" ${data.id == departmentId ? 'selected' : ''}>${data.dept_name}</option>`);
                    });
                } catch (error) {
                    $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                }
            };


            const initializeDropdowns = async () => {
                let selectedBranchId;

                if (isAdmin) {
                    selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;

                    $('#branch_id').on('change', async () => {
                        const newBranchId = $('#branch_id').val();
                        await loadDepartments(newBranchId);
                    });

                    // Trigger initial load if branch is selected
                    if (selectedBranchId) {
                        $('#branch_id').trigger('change');
                    }
                } else {
                    selectedBranchId = defaultBranchId;
                    if (selectedBranchId) {
                        await loadDepartments(selectedBranchId);
                    }
                }

            };

            // Initialize everything
            initializeDropdowns();
        });
    </script>
@endsection






