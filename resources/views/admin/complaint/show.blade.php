@extends('layouts.master')

@section('title',__('index.complaint'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.complaint.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.complaint.common.breadcrumb')

        <div class="card">
            <div class="card-body pb-0">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 mb-4">
                            <table class="table table-striped table-responsive">
                                <tbody>
                                <tr>
                                    <th class="w-30">{{ __('index.complaint_from') }}</th>
                                    <td>
                                        {{ $complaintDetail?->complainFrom?->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-30">{{ __('index.subject') }}</th>
                                    <td>
                                        {{ $complaintDetail->subject }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-30">{{ __('index.branch') }}</th>
                                    <td>{{ $complaintDetail->branch?->name }}</td>
                                </tr>
                                <tr>
                                    <th class="w-30">{{ __('index.department') }}</th>
                                    <td>
                                        <ul class="mb-0 ps-0 list-unstyled">
                                            @forelse($complaintDetail->complaintDepartment as $detail)
                                                <li>{{ $detail?->department?->dept_name }}</li>
                                            @empty
                                            @endforelse
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-30">{{ __('index.employee_name') }}</th>
                                    <td>
                                        <ul class="mb-0 ps-0 list-unstyled">
                                            @forelse($complaintDetail->complaintEmployee as $detail)
                                                <li>{{ $detail?->employee?->name }}</li>
                                            @empty
                                            @endforelse
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-30">{{ __('index.complaint_date') }}</th>
                                    <td>
                                        {{
                                            \App\Helpers\AppHelper::formatDateForView($complaintDetail->complaint_date) }}
                                    </td>
                                </tr>


                                <tr>
                                    <th class="w-30">{{ __('index.message') }}</th>
                                    <td>
                                        {!! $complaintDetail->message !!}
                                    </td>
                                </tr>
                                @if(isset($complaintDetail->image))
                                    <tr>
                                        <th class="w-30">{{ __('index.image') }}</th>
                                        <td>
                                            @php
                                                $fileExtension = pathinfo($complaintDetail->image, PATHINFO_EXTENSION);
                                            @endphp
                                            @if(in_array($fileExtension, ['jpeg', 'jpg', 'png', 'webp']))
                                                <img class="wd-200 ht-100" style="object-fit: cover;"
                                                    src="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}"
                                                    alt="Certificate" data-bs-toggle="modal" data-bs-target="#certificateModal-{{ $complaintDetail->id }}">

                                                <div class="modal fade" id="certificateModal-{{ $complaintDetail->id }}" tabindex="-1" aria-labelledby="imageModalLabel-{{ $complaintDetail->index }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="imageModalLabel-{{ $complaintDetail->id }}">View Image <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button></h5>

                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img class="img-fluid" src="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}" alt="IBAN">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($fileExtension === 'pdf')
                                                <embed src="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}"
                                                    type="application/pdf" width="150" height="100" />
                                                <a href="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}"
                                                target="_blank" class="mt-2">Preview PDF</a>
                                            @else
                                                <a href="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}"
                                                download class="mt-2">Download Document</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <th class="w-30">{{ __('index.created_by') }}</th>
                                    <td>{{ $complaintDetail->createdBy?->name }}</td>
                                </tr>
                                <tr>
                                    <th class="w-30">{{ __('index.updated_by') }}</th>
                                    <td>{{ $complaintDetail->updatedBy?->name }}</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        <div class="col-lg-4 col-md-4 mb-4">
                            <div class="cnt_response border p-4">
                                <h5 class="mb-3 pb-2 border-bottom mb-2"> {{ __('index.response_section') }}</h5>
                                @forelse($complaintDetail->complaintReply as $response)

                                    <p class="border-start ps-2 fw-bold mb-2" readonly>{!! $response->message !!}</p>
                                    <span class="fst-italic">Response By: {{ $response?->employee?->name }}</span>

                                @empty
                                @endforelse
                            </div>
                        </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.complaint.common.scripts')
@endsection

