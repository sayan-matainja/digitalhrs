@extends('layouts.master')
@section('title', __('index.feature_control'))
@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.feature_control') }}</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.feature_control') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>{{ __('index.sn') }}</th>
                            <th>{{ __('index.name') }}</th>
                            <th class="text-center">{{ __('index.group') }}</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($features as $feature)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ ucfirst($feature->name) }}</strong> </td>
                                <td class="text-center"><strong>{{ ucfirst($feature->group) }}</strong></td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="{{ route('admin.feature.toggle-status', $feature->id) }}"
                                               type="checkbox" {{ $feature->status == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
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

        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="exampleModalLabel">{{ __('index.export_table_data') }}</h5>
                    </div>
                    <div class="modal-body">
                        <a href="{{ route('admin.leave-type-export') }}">
                            <button class="btn btn-secondary btn-sm">{{ __('index.leave_types') }}</button>
                        </a>
                        <a href="{{ route('admin.leave-request-export') }}">
                            <button class="btn btn-success btn-sm">{{ __('index.leave_requests') }}</button>
                        </a>
                        <a href="{{ route('admin.employee-lists-export') }}">
                            <button class="btn btn-warning btn-sm">{{ __('index.employee_lists') }}</button>
                        </a>
                        <a href="{{ route('admin.attendance-lists-export') }}">
                            <button class="btn btn-danger btn-sm">{{ __('index.attendances') }}</button>
                        </a>
                    </div>
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

            $('.toggleStatus').change(function (event) {
                event.preventDefault();
                var status = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: '{{ __('index.confirm_change_status') }}',
                    showDenyButton: true,
                    confirmButtonText: `{{ __('index.yes') }}`,
                    denyButtonText: `{{ __('index.no') }}`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    } else if (result.isDenied) {
                        (status === 0) ? $(this).prop('checked', true) : $(this).prop('checked', false)
                    }
                })
            })
        });
    </script>
@endsection
