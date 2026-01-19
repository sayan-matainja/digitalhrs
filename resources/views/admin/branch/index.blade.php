@extends('layouts.master')

@section('title', __('index.title_branch'))
@section('button')
    @can('create_branch')
        <a href="{{ route('admin.branch.create') }}">
            <button class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ __('index.add_branch') }}</button>
        </a>
    @endcan

@endsection
@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')
        @include('admin.branch.common.breadcrumb', ['title' => __('index.branch')])
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.branch_lists') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.branch.index') }}" method="get">
                <div class="row align-items-center">

                    <div class="col-lg-4 col-md-4 mb-4">
                        <input type="text" placeholder="{{ __('index.search_by_branch_name') }}" name="name" value="{{($filterParameters['name'])}}" class="form-control">
                    </div>

                    <div class="col-lg-4 col-md-4 mb-4">
                        <select class="form-select form-select-lg" name="per_page">
                            <option value="10" {{($filterParameters['per_page']) == 10 ? 'selected': ''}}>10</option>
                            <option value="25" {{($filterParameters['per_page']) == 25 ? 'selected': ''}}>25</option>
                            <option value="50" {{($filterParameters['per_page']) == 50 ? 'selected': ''}}>50</option>
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-3 d-flex">
                        <button type="submit" class="btn btn-block btn-success me-2 mb-4">{{ __('index.filter') }}</button>

                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4" href="{{route('admin.branch.index')}}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.branch_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.branch_name') }}</th>
                            <th>{{ __('index.address') }}</th>
                            <th class="text-center">{{ __('index.phone') }}</th>
                            <th class="text-center">{{ __('index.total_employee') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @can(['edit_branch','delete_branch'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($branches as $key => $value)
                            <tr>
                                <td>{{(($branches->currentPage()- 1 ) * (\App\Models\Branch::RECORDS_PER_PAGE) + (++$key))}}</td>
                                <td>{{ucfirst($value->name)}}</td>
                                <td>{{$value->address}}</td>
                                <td class="text-center">{{$value->phone}}</td>
                                <td class="text-center">{{$value->employees_count }}</td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{route('admin.branch.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['edit_branch','delete_branch'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('edit_branch')
                                                <li class="me-2">
                                                    <a href="{{route('admin.branch.edit',$value->id)}}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_branch')
                                                <li>
                                                    <a class="deleteBranch" data-href="{{route('admin.branch.delete',$value->id)}}"><i class="link-icon"  data-feather="delete"></i></a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </td>
                                @endcanany
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

        <div class="dataTables_paginate">
            {{$branches->appends($_GET)->links()}}
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

            $('.toggleStatus').change(function (event) {
                event.preventDefault();
                var status = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: '{{ __('index.are_you_sure_change_status') }}',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }else if (result.isDenied) {
                        (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            })

            $('.deleteBranch').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '{{ __('index.are_you_sure_delete_branch') }}',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })


        });

    </script>
@endsection
