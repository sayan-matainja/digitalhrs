@extends('layouts.master')

@section('title',__('index.resignation'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.resignation.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.resignation.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <table class="table table-striped table-responsive">
                        <tbody>
                        <tr>
                            <th class="w-30">{{ __('index.branch') }}</th>
                            <td>{{ $resignationDetail->branch?->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.department') }}</th>
                            <td>{{ $resignationDetail->department?->dept_name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.employee_name') }}</th>
                            <td>{{ $resignationDetail->employee?->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.resignation_date') }}</th>
                            <td>
                                {{ \App\Helpers\AppHelper::formatDateForView($resignationDetail->resignation_date) }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.last_date') }}</th>
                            <td>
                                {{ \App\Helpers\AppHelper::formatDateForView($resignationDetail->last_working_day) }}
                            </td>
                        </tr>

                        <tr>
                            <th class="w-30">{{ __('index.reason') }}</th>
                            <td>
                                {!! $resignationDetail->reason !!}
                            </td>
                        </tr>
                        @if(isset($resignationDetail->document))
                            <tr>
                                <th class="w-30">{{ __('index.document') }}</th>
                                <td>
                                    @php
                                        $fileExtension = pathinfo($resignationDetail->document, PATHINFO_EXTENSION);
                                    @endphp
                                    @if(in_array($fileExtension, ['jpeg', 'jpg', 'png', 'webp']))
                                        <img class="wd-200 ht-100" style="object-fit: cover;"
                                             src="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}"
                                             alt="Certificate" data-bs-toggle="modal" data-bs-target="#certificateModal-{{ $resignationDetail->id }}">

                                        <div class="modal fade" id="certificateModal-{{ $resignationDetail->id }}" tabindex="-1" aria-labelledby="imageModalLabel-{{ $resignationDetail->index }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="imageModalLabel-{{ $resignationDetail->id }}">View Image <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button></h5>

                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img class="img-fluid" src="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}" alt="IBAN">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($fileExtension === 'pdf')
                                        <embed src="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}"
                                               type="application/pdf" width="150" height="100" />
                                        <a href="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}"
                                           target="_blank" class="mt-2">Preview PDF</a>
                                    @else
                                        <a href="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}"
                                           download class="mt-2">Download Document</a>
                                    @endif
                                </td>
                            </tr>
                        @endif

                        <?php
                        $color = [
                            \App\Enum\ResignationStatusEnum::approved->value => 'success',
                            \App\Enum\ResignationStatusEnum::onReview->value => 'info',
                            \App\Enum\ResignationStatusEnum::pending->value => 'secondary',
                            \App\Enum\ResignationStatusEnum::cancelled->value => 'danger',
                        ];


                        ?>
                        <tr>
                            <th class="w-30">{{ __('index.status') }}</th>
                            <td> <span class="badge bg-{{ $color[$resignationDetail->status] }}" style="font-size: 1em">{{ ucfirst($resignationDetail->status) }}</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.resignation.common.scripts')
@endsection

