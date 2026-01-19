@extends('layouts.master')

@section('title',__('index.asset_type'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-end">
        <a href="{{route('admin.asset-types.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.assetManagement.types.common.breadcrumb')

        <div class="card support-main">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('index.asset_name')}} </th>
                            <th class="text-center">{{__('index.purchased_date')}}</th>
                            <th class="text-center">{{__('index.is_working')}}</th>
                            <th class="text-center">{{__('index.is_available')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($assetTypeDetail->assets as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>
                                    <a href="javascript:void(0)" title="{{  __('index.asset_detail') }}"
                                       onclick="showAssetDetails('{{ route('admin.assets.show',$value->id) }}')">
                                        {{ucfirst($value->name)}}
                                    </a>

                                </td>
                                <td class="text-center">
                                    {{\App\Helpers\AppHelper::formatDateForView($value->purchased_date)}}
                                </td>
                                <td class="text-center">
                                    {{ucfirst($value->is_working)}}
                                </td>
                                <td class="text-center">
                                    {{ isset($value->is_available) && $value->is_available == 1  ? __('index.yes'):__('index.no')}}
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
    </section>
    @include('admin.assetManagement.assetDetail.show')
@endsection
@section('scripts')
    <script>
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

