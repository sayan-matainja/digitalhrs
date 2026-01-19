@extends('layouts.master')

@section('title',__('index.warning'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.warning.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.warning.common.breadcrumb')

        <div class="card">
            <div class="card-body pb-0">
                <div class="row">
                    <div class="col-lg-8 col-md-8 mb-4">
                        <table class="table table-striped table-responsive">
                            <tbody>
                            <tr>
                                <th class="w-30">{{ __('index.subject') }}</th>
                                <td>
                                    {{ $warningDetail->subject }}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-30">{{ __('index.branch') }}</th>
                                <td>{{ $warningDetail->branch?->name }}</td>
                            </tr>
                            <tr>
                                <th class="w-30">{{ __('index.department') }}</th>
                                <td>
                                    <ul class="mb-0 ps-0 list-unstyled">
                                        @forelse($warningDetail->warningDepartment as $detail)
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
                                        @forelse($warningDetail->warningEmployee as $detail)
                                            <li>{{ $detail?->employee?->name }}</li>
                                        @empty
                                        @endforelse
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <th class="w-30">{{ __('index.warning_date') }}</th>
                                <td>
                                    {{
                                        \App\Helpers\AppHelper::formatDateForView($warningDetail->warning_date) }}
                                </td>
                            </tr>


                            <tr>
                                <th class="w-30">{{ __('index.message') }}</th>
                                <td>
                                    {!! $warningDetail->message !!}
                                </td>
                            </tr>


                            <tr>
                                <th class="w-30">{{ __('index.created_by') }}</th>
                                <td>{{ $warningDetail->createdBy->name ?? 'Admin' }}</td>
                            </tr>
                            <tr>
                                <th class="w-30">{{ __('index.updated_by') }}</th>
                                <td>{{ $warningDetail->updatedBy->name ?? 'Admin' }}</td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="col-lg-4 col-md-4 mb-4">
                        <div class="cnt_response border p-4">
                            <h5 class="mb-3 border-bottom pb-2"> {{ __('index.response_section') }}</h5>

                            @forelse($warningDetail->warningReply as $response)
                                <p class="border-start fw-bold ps-2 mb-2" readonly>{!! $response->message !!}</p>
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
    @include('admin.warning.common.scripts')
@endsection

