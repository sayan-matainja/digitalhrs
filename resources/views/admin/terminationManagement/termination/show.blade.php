@extends('layouts.master')

@section('title',__('index.termination'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.termination.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.terminationManagement.termination.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <table class="table table-striped table-responsive">
                        <tbody>
                        <tr>
                            <th class="w-30">{{ __('index.branch') }}</th>
                            <td>{{ $terminationDetail->branch?->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.department') }}</th>
                            <td>{{ $terminationDetail->department?->dept_name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.termination_type') }}</th>
                            <td>{{ $terminationDetail->terminationType?->title }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.employee_name') }}</th>
                            <td>{{ $terminationDetail->employee?->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.notice_date') }}</th>
                            <td>
                                {{ \App\Helpers\AppHelper::formatDateForView($terminationDetail->notice_date) }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.termination_date') }}</th>
                            <td>
                                {{ \App\Helpers\AppHelper::formatDateForView($terminationDetail->termination_date) }}
                            </td>
                        </tr>

                        <tr>
                            <th class="w-30">{{ __('index.reason') }}</th>
                            <td>
                                {!! $terminationDetail->reason !!}
                            </td>
                        </tr>

                        @if(isset($terminationDetail->document))
                            <tr>
                                <th class="w-30">{{ __('index.document') }}</th>
                                <td>
                                    @php
                                        $fileExtension = pathinfo($terminationDetail->document, PATHINFO_EXTENSION);
                                    @endphp
                                    @if(in_array($fileExtension, ['jpeg', 'jpg', 'png', 'webp']))
                                        <img class="wd-200 ht-100" style="object-fit: cover;"
                                             src="{{ asset(\App\Models\Termination::UPLOAD_PATH . $terminationDetail->document) }}"
                                             alt="Certificate" data-bs-toggle="modal" data-bs-target="#certificateModal-{{ $terminationDetail->id }}">

                                        <div class="modal fade" id="certificateModal-{{ $terminationDetail->id }}" tabindex="-1" aria-labelledby="imageModalLabel-{{ $terminationDetail->index }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="imageModalLabel-{{ $terminationDetail->id }}">View Image <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button></h5>

                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img class="img-fluid" src="{{ asset(\App\Models\Termination::UPLOAD_PATH . $terminationDetail->document) }}" alt="IBAN">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($fileExtension === 'pdf')
                                        <embed src="{{ asset(\App\Models\Termination::UPLOAD_PATH . $terminationDetail->document) }}"
                                               type="application/pdf" width="150" height="100" />
                                        <a href="{{ asset(\App\Models\Termination::UPLOAD_PATH . $terminationDetail->document) }}"
                                           target="_blank" class="mt-2">Preview PDF</a>
                                    @else
                                        <a href="{{ asset(\App\Models\Termination::UPLOAD_PATH . $terminationDetail->document) }}"
                                           download class="mt-2">Download Document</a>
                                    @endif
                                </td>
                            </tr>
                        @endif

                        <?php
                        $color = [
                            \App\Enum\TerminationStatusEnum::approved->value => 'success',
                            \App\Enum\TerminationStatusEnum::onReview->value => 'info',
                            \App\Enum\TerminationStatusEnum::pending->value => 'secondary',
                            \App\Enum\TerminationStatusEnum::cancelled->value => 'warning',
                        ];


                        ?>
                        <tr>
                            <th class="w-30">{{ __('index.status') }}</th>
                            <td> <span class="badge bg-{{ $color[$terminationDetail->status] }}" style="font-size: 1em">{{ ucfirst($terminationDetail->status) }}</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.terminationManagement.termination.common.scripts')
@endsection

