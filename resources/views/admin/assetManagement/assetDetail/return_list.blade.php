@php use App\Models\Asset; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.assets'))

@section('action', __('index.lists'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.assets.index')}}">{{ __('index.asset_return') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.lists') }}</li>
            </ol>
        </nav>


        <div class="card mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs gap-1" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="return-tab" data-bs-toggle="tab" href="#return" role="tab" aria-controls="return" aria-selected="true">{{ __('index.return') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="maintenance-tab" data-bs-toggle="tab" href="#maintenance" role="tab" aria-controls="maintenance" aria-selected="false">{{ __('index.maintenance') }}</a>
                    </li>
                </ul>
            </div>
            <div class="card-body pb-0">
                <div class="tab-content">
                    <!-- repair Tab -->
                    <div class="tab-pane fade show active mb-4" id="return" role="tabpanel" aria-labelledby="return-tab">
                        <div class="table-responsive">
                            <table id="returnTable" class="table">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>{{  __('index.employee') }}</th>
                                    <th class="text-center">{{  __('index.asset') }}</th>
                                    <th class="text-center">{{  __('index.returned_date') }}</th>
                                    <th class="text-center">{{  __('index.return_condition') }}</th>
                                    <th class="text-center">{{  __('index.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($returnLists as $key => $value)
                                    <tr>
                                        <td class="text-center">{{++$key}}</td>
                                        <td>{{ucfirst($value->user?->name)}}</td>
                                        <td class="text-center">
                                            <a href="javascript:void(0)" title="{{  __('index.asset_detail') }}"
                                               onclick="showAssetDetails('{{ route('admin.assets.show',$value->asset_id) }}')">
                                                {{ucfirst($value->asset?->name)}}
                                            </a>

                                        </td>

                                        @php

                                            $assignedDate = new DateTime($value->assigned_date);
                                            $returnedDate = !empty($value->returned_date) ? new DateTime($value->returned_date) : new DateTime();
                                            $daysDifference = $assignedDate->diff($returnedDate)->days + 1;
                                        @endphp
                                        <td class="text-center">
                                            {{ isset($value->returned_date) ? AppHelper::formatDateForView($value->returned_date) : '' }}
                                            {{ ($daysDifference > 0) ? '(Used for:'. $daysDifference .'days)' : '' }}
                                        </td>

                                        <td class="text-center">
                                            {{ ucfirst($value->return_condition) }}
                                        </td>

                                        <td>
                                            <a href="javascript:void(0)" title="{{  __('index.show_detail') }}"
                                               class="d-flex align-items-center justify-content-center show-assignment"
                                               data-asset="{{ $value->asset->name }}"
                                               data-employee="{{ $value->user->name }}"
                                               data-notes="{!! $value->notes !!} "
                                               data-status="{{ ucfirst($value->status) }}"
                                               data-assigned_date="{{ AppHelper::formatDateForView($value->assigned_date) }}"
                                               data-returned_date="{{ isset($value->returned_date) ? AppHelper::formatDateForView($value->returned_date) : '' }}"
                                               data-used_for="{{ $daysDifference }}"
                                               data-return_condition="{{ ucfirst($value->return_condition) }}">
                                                <i class="link-icon" data-feather="eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <p class="text-center"><b>{{  __('index.no_records_found') }}</b></p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- App maintenance Tab -->
                    <div class="tab-pane fade mb-4" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
                        <div class="table-responsive">
                            <table id="maintenanceTable" class="table">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">{{  __('index.asset') }}</th>
                                    <th class="text-center">{{  __('index.type') }}</th>
                                    <th class="text-center">{{  __('index.is_repaired') }}</th>
                                    <th class="text-center">{{  __('index.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($maintenanceLists as $key => $value)
                                    <tr>
                                        <td class="text-center">{{++$key}}</td>
                                        <td class="text-center">{{ucfirst($value->asset?->name)}}</td>
                                        <td class="text-center">{{ucfirst($value->asset?->type?->name)}}</td>
                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{route('admin.asset.toggle-repair-status',$value->id)}}"
                                                       type="checkbox" {{ $value->return_condition == \App\Enum\AssetReturnConditionEnum::repaired->value ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <td>
                                            @can('assign_repair_update')
                                            <a href="javascript:void(0)" title="{{  __('index.show_detail') }}"
                                               class="d-flex align-items-center justify-content-center show-repair-detail"
                                               data-asset="{{ $value->asset?->name }}"
                                               data-type="{{ $value->asset?->type?->name }}"
                                               data-notes="{!! $value->notes !!} "
                                               >
                                                <i class="link-icon" data-feather="eye"></i>
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <p class="text-center"><b>{{  __('index.no_records_found') }}</b></p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>
    @include('admin.assetManagement.assetDetail.common.assignment_detail')
    @include('admin.assetManagement.assetDetail.show')

    <div class="modal fade" id="repairDetail" tabindex="-1" aria-labelledby="repairDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header text-center">
                    <h5 class="modal-title repairTitle" id="repairDetailLabel"></h5>
                </div>
                <div class="modal-body p-4">
                    <table class="table table-borderless table-hover">
                        <tbody>
                            <tr>
                                <th scope="row" class="text-muted w-30">{{ __('index.asset') }}</th>
                                <td class="asset fw-medium"></td>
                            </tr>
                            <tr>
                                <th scope="row" class="text-muted w-30">{{ __('index.type') }}</th>
                                <td class="type fw-medium"></td>
                            </tr>
                            <tr>
                                <th scope="row" class="text-muted w-30">{{ __('index.notes') }}</th>
                                <td class="notes"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')


    <script>


        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            var status = $(this).prop('checked') === true ? 1 : 0;
            var href = $(this).attr('href');
            Swal.fire({
                title: '{{ __('index.confirm_change_status') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }else if (result.isDenied) {
                    (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                }
            })
        })

        $(document).on('click', '.show-assignment', function () {
            const $el = $(this);
            var days = $el.data('used_for');

            console.log($el.data('returned_date'));
            var rdate = $el.data('returned_date');
            if(days > 0){
                rdate += ' (used for : '+days + ' days)';
            }

            console.log(rdate);
            $('.assignmentTitle').text('Asset Assignment Detail');
            $('.assigned_date').text($el.data('assigned_date'));
            $('.status').text($el.data('status'));
            $('.returned_date').text(rdate);
            $('.return_condition').text($el.data('return_condition'));
            $('.employee').text($el.data('employee'));
            $('.asset').text($el.data('asset'));

            $('.notes').text($el.data('notes'));

            const modal = new bootstrap.Modal(document.getElementById('assignmentDetail'));
            modal.show();
        });
        $(document).on('click', '.show-repair-detail', function () {
            const $el = $(this);
            //'status', 'assigned_date', 'returned_date', 'return_condition', 'notes',
            $('.repairTitle').text('Asset Repair Detail');
            $('.type').text($el.data('type'));
            $('.asset').text($el.data('asset'));
            $('.notes').text($el.data('notes'));

            const modal = new bootstrap.Modal(document.getElementById('repairDetail'));
            modal.show();
        });

        function showAssetDetails(url) {
            $.get(url, function (response) {
                if (response && response.data) {
                    const data = response.data;

                    var daysUsed = data.used_for;
                    $('.assetTitle').html('Asset Detail');
                    $('.name').text(data.name);
                    $('.type').text(data.assetType);
                    $('.asset_code').text(data.asset_code);
                    $('.asset_serial_no').text(data.asset_serial_no);
                    $('.is_working').text(data.is_working);
                    $('.purchased_date').text(data.purchased_date);
                    $('.is_available').text(data.is_available);
                    $('.note').text(data.note);
                    if (daysUsed > 0) {
                        $('.used_for').text(daysUsed+ ' days');
                    } else {
                        $('.used_for').parent().hide();
                    }

                    if (data.image) {
                        $('.image').attr('src', data.image).show();
                    } else {
                        $('.image').parent().hide();
                    }

                    const modal = new bootstrap.Modal(document.getElementById('assetDetail'));
                    modal.show();
                }
            }).fail(function (xhr, status, error) {
                // Handle error
                alert('Error loading asset details. Please try again.');
                console.error('Error:', error);
            });
        }
    </script>

@endsection

