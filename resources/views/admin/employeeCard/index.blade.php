@extends('layouts.master')

@section('title', __('index.card_template'))
@section('button')
    @can('create_branch')
        <a href="{{ route('admin.card.create-template') }}">
            <button class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ __('index.add_template') }}</button>
        </a>
    @endcan
@endsection
@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')
        @include('admin.employeeCard.common.breadcrumb', ['title' => __('index.card_template')])
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.template_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.title') }}</th>
                            <th>{{ __('index.orientation') }}</th>
                            <th class="text-center">{{ __('index.is_active') }}</th>
                            <th class="text-center">{{ __('index.is_default') }}</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($settings as $key => $value)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ ucfirst($value->title) }}</td>
                                <td>{{ ucfirst($value->orientation) }}</td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{route('admin.card.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="makeDefault" href="{{route('admin.card.make-default',$value->id)}}"
                                               type="checkbox" {{($value->is_default) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                <li class="me-2">
                                                    <a href="{{route('admin.card.show-template',$value->id)}}" target="_blank" title="Preview">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                                <li class="me-2">
                                                    <a href="{{route('admin.card.edit-template',$value->id)}}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>

                                            @if($value->is_default == 0)
                                                <li>
                                                    <a class="deleteTemplate" data-href="{{route('admin.card.delete-template',$value->id)}}"><i class="link-icon"  data-feather="delete"></i></a>
                                                </li>
                                            @endif
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

        <div class="dataTables_paginate">
            {{$settings->appends($_GET)->links()}}
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
            $('.makeDefault').change(function (event) {
                event.preventDefault();
                var status = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: '{{ __('index.are_you_sure_make_default') }}',
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

            $('.deleteTemplate').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '{{ __('index.are_you_sure_delete_template') }}',
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
