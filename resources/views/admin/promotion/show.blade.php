@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title',__('index.promotion'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.promotion.index')}}">
            <button class="btn btn-sm btn-primary"><i class="link-icon"
                                                      data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.promotion.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <table class="table table-striped table-responsive">
                        <tbody>
                        <tr>
                            <th class="w-30">{{ __('index.branch') }}</th>
                            <td>{{ $promotionDetail->branch?->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.department') }}</th>
                            <td>
                                {{ $promotionDetail?->department?->dept_name }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.employee_name') }}</th>
                            <td>
                                {{ $promotionDetail?->employee?->name }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.old_post') }}</th>
                            <td>
                                {{ $promotionDetail?->oldPost?->post_name }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.new_post') }}</th>
                            <td>
                                {{ $promotionDetail?->post?->post_name }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.promotion_date') }}</th>
                            <td>
                                {{
                                    AppHelper::formatDateForView($promotionDetail->promotion_date) }}
                            </td>
                        </tr>

                        <tr>
                            <th class="w-30">{{ __('index.description') }}</th>
                            <td>
                                {!! $promotionDetail->description !!}
                            </td>
                        </tr>

{{--                        <tr>--}}
{{--                            <th class="w-30">{{ __('index.remark') }}</th>--}}
{{--                            <td>--}}
{{--                                {!! $promotionDetail->remark !!}--}}
{{--                            </td>--}}
{{--                        </tr>--}}


                        <tr>
                            <th class="w-30">{{ __('index.created_by') }}</th>
                            <td>{{ $promotionDetail->createdBy->name ?? 'Admin' }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.updated_by') }}</th>
                            <td>{{ $promotionDetail->updatedBy->name ?? 'Admin' }}</td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

