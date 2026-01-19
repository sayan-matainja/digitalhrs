@php use App\Models\Asset; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.assignment'))

@section('action', __('index.lists'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.assets.index')}}">{{ __('index.assignment') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.history') }}</li>
            </ol>
        </nav>



        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.assignment_list_of') }} {{ $assetDetail->name }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{  __('index.employee') }}</th>
                            <th class="text-center">{{  __('index.assigned_date') }}</th>
                            <th class="text-center">{{  __('index.returned_date') }}</th>
                            <th class="text-center">{{  __('index.status') }}</th>
                            <th class="text-center">{{  __('index.action') }}</th>

                        </tr>
                        </thead>
                        <tbody>
                        @forelse($assignmentList as $key => $value)
                            <tr>
                                <td class="text-center">{{++$key}}</td>
                                <td>{{ucfirst($value->user->name)}}</td>
                                <td class="text-center">
                                    {{ AppHelper::formatDateForView($value->assigned_date) }}
                                </td>

                                @php
                                    // Get the difference in days between returned_date and assigned_date
                                    $assignedDate = new DateTime($value->assigned_date);
                                    $returnedDate = !empty($value->returned_date) ? new DateTime($value->returned_date) : new DateTime(); // Use today's date if returned_date is not present
                                    $daysDifference = $assignedDate->diff($returnedDate)->days +1;
                                @endphp

                                <td class="text-center">
                                    {{ isset($value->returned_date) ? AppHelper::formatDateForView($value->returned_date) : '' }}
                                    {{ ($daysDifference > 0) ? '(Used for:'. $daysDifference .' days)' : '' }}
                                </td>

                                <td class="text-center">{{ucfirst($value->status)}}</td>


                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">


                                            <li class="me-2">


                                                <a href="javascript:void(0)" title="{{  __('index.show_detail') }}"
                                                   class="d-flex align-items-center justify-content-center show-assignment"
                                                   data-asset="{{ $value->asset->name }}"
                                                   data-employee="{{ $value->user->name }}"
                                                   data-notes="{!! $value->notes !!} "
                                                   data-status="{{ ucfirst($value->status) }}"
                                                   data-assigned_date="{{  AppHelper::formatDateForView($value->assigned_date) }}"
                                                   data-returned_date="{{  isset($value->returned_date) ? AppHelper::formatDateForView($value->returned_date) : '' }}"
                                                   data-used_for="{{ $daysDifference }}"
                                                   data-return_condition="{{ ucfirst($value->return_condition) }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>
                                            </li>

                                        @if(is_null($value->returned_date) || ($value->return_condition == \App\Enum\AssetAssignmentStatusEnum::assigned->value))
                                            <li>
                                                <a class="btn btn-sm btn-info return-asset"
                                                   data-title="{{$value->asset->name}} Asset Detail"
                                                   data-href="{{ route('admin.asset.return', $value->id) }}"
                                                   title="{{ __('index.return') }}"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#assetReturnModal">
                                                    Return
                                                </a>
                                            </li>
                                        @endif

                                    </ul>
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

    </section>
    @include('admin.assetManagement.assetDetail.common.return')
    @include('admin.assetManagement.assetDetail.common.assignment_detail')
@endsection

@section('scripts')


   <script>
       $(document).on('click', '.show-assignment', function () {
           const $el = $(this);
           //'status', 'assigned_date', 'returned_date', 'return_condition', 'notes',
           var days = $el.data('used_for');

           var rdate = $el.data('returned_date');
           if(days > 0){
               rdate += ' (used for : '+days + ' days)';
           }

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

       document.addEventListener('DOMContentLoaded', function () {

           const form = document.getElementById('assetReturnForm');
           form.addEventListener('submit', function (event) {
               if (!form.checkValidity()) {
                   event.preventDefault();
                   event.stopPropagation();
               }
               form.classList.add('was-validated');
           }, false);


           document.querySelectorAll('.return-asset').forEach(button => {
               button.addEventListener('click', function () {
                   const form = document.getElementById('assetReturnForm');
                   const modalTitle = document.getElementById('assetReturnModalLabel');
                   form.action = this.getAttribute('data-href');
                   modalTitle.textContent = this.getAttribute('data-title') || 'Return Asset';
                   form.classList.remove('was-validated');
                   document.getElementById('notes').value = '';
               });
           });

       });
   </script>
@endsection

