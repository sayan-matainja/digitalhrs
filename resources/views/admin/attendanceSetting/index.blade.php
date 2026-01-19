@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title',__('index.settings'))



@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">@lang('index.dashboard')</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.attendance-settings.index')}}">@lang('index.attendance_settings')</a></li>
            </ol>
        </nav>


        <div class="card mb-4">
            <div class="card-header">

                <h5>{{ __('index.attendance_settings') }}</h5>

            </div>
            <div class="card-body">



                <div class="table-responsive">
                    <form class="forms-sample" id="attendanceSettingForm" action="{{ route('admin.attendance-settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <table id="dataTableExample" class="table">
                            <tbody>
                            @forelse($attendanceSettings as $key => $datum)
                                <tr>
                                    <td>
                                        {{ ucfirst(__('seeder.' . $datum->slug)) }} <span style="color: red">*</span>
                                    </td>
                                    <td>
                                        @if($datum->slug == 'attendance_method')
                                            <select class="form-select" id="attendanceMethod" name="attendance_method[]" multiple>
                                                @foreach(\App\Models\AttendanceSetting::ATTENDANCE_METHOD as $enum)
                                                    <option value="{{ $enum }}" {{ in_array($enum, $datum->values ?? []) ? 'selected' : '' }}> {{ ucfirst($enum) }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($datum->value === null && $datum->values === null)
                                            <label class="switch">
                                                <input class="toggleStatus" data-href="{{ route('admin.attendance-settings.toggle-status', $datum->id) }}" type="checkbox" {{ $datum->status == 1 ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        @else
                                            <input type="number" class="form-control" min="1" oninput="validity.valid||(value='');" name="attendance_limit" value="{{ $datum->value }}" autocomplete="off">
                                        @endif
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
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="link-icon" data-feather="plus"></i> @lang('index.update')
                        </button>
                    </form>

                </div>



            </div>
        </div>

    </section>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            $("#attendanceMethod").select2();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.toggleStatus').change(function (event) {
                event.preventDefault();
                let input = $(this);
                let checked = input.prop('checked');
                let href = input.data('href');
                Swal.fire({
                    title: '@lang('index.change_status_confirm')',
                    showDenyButton: true,
                    confirmButtonText: `@lang('index.yes')`,
                    denyButtonText: `@lang('index.no')`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'GET',
                            url: href,
                            dataType: 'json',
                            success: function (data) {
                                if (data.success) {
                                    Swal.fire('Success', data.message.toString(), 'success');
                                } else {
                                    Swal.fire('Error', data.message.toString(), 'error');
                                    input.prop('checked', !checked);
                                }
                            },
                            error: function (xhr, status, error) {
                                let msg = xhr.responseJSON?.message || error || 'An error occurred';
                                Swal.fire('Error', msg.toString(), 'error');
                                input.prop('checked', !checked);
                            }
                        });
                    } else if (result.isDenied) {
                        input.prop('checked', !checked);
                    }
                });
            });

            $('#attendanceSettingForm').submit(function (event) {
                event.preventDefault();
                let form = $(this);

                $.ajax({
                    type: 'PUT',
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                confirmButtonColor: '#3085d6'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Something went wrong.',
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            let errors = '';
                            let err = xhr.responseJSON.errors;
                            for (let key in err) {
                                if (err.hasOwnProperty(key)) {
                                    errors += err[key][0] + '\n';
                                }
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errors,
                                confirmButtonColor: '#d33'
                            });
                        } else {
                            // Other errors
                            let msg = xhr.responseJSON?.message || 'An error occurred.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg,
                                confirmButtonColor: '#d33'
                            });
                        }
                    }
                });
            });

        });
    </script>
@endsection





